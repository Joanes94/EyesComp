<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PinLoginRequest;
use App\Http\Requests\Auth\RegisterOwnerRequest;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Inscription du propriétaire (F-AUTH-01).
     * Crée l'entreprise (tenant), sa première boutique et le compte propriétaire.
     */
    public function registerOwner(RegisterOwnerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = DB::transaction(function () use ($validated) {
            // 1. Création de l'entreprise (Tenant) avec 14 jours d'essai gratuit
            $slug = Str::slug($validated['business_name']);
            if (Tenant::where('slug', $slug)->exists()) {
                $slug .= '-'.Str::lower(Str::random(5));
            }

            $tenant = Tenant::create([
                'name' => $validated['business_name'],
                'slug' => $slug,
                'business_type' => $validated['business_type'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'currency' => $validated['currency'] ?? 'XOF',
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
                'settings' => [
                    'onboarding_completed' => false,
                ],
            ]);

            // 2. Création de la première boutique
            $store = Store::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['business_name'],
                'code' => 'BOUTIQUE-01',
                'phone' => $validated['phone'],
                'address' => $validated['store_address'] ?? null,
                'currency' => $tenant->currency,
                'receipt_header' => $validated['business_name'],
                'receipt_footer' => 'Merci pour votre confiance et à bientôt !',
                'allow_negative_stock' => false,
                'tax_enabled' => false,
                'is_active' => true,
            ]);

            // 3. Création de l'utilisateur Propriétaire
            $owner = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'password' => Hash::make($validated['password']),
                'status' => 'active',
                'last_active_at' => now(),
            ]);

            // 4. Attribution du rôle Propriétaire et association à la boutique
            $owner->assignRole('owner');
            $owner->stores()->attach($store->id, ['is_default' => true]);

            // 5. Génération du token Sanctum
            $token = $owner->createToken('auth-token')->plainTextToken;

            return [
                'token' => $token,
                'user' => $owner->load(['roles', 'tenant', 'stores']),
                'store' => $store,
            ];
        });

        return response()->json([
            'message' => 'Compte EyesComp créé avec succès. Bienvenue !',
            'token' => $result['token'],
            'user' => $result['user'],
            'current_store' => $result['store'],
        ], 201);
    }

    /**
     * Connexion par email ou téléphone + mot de passe (F-AUTH-02).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $login = $request->input('login');
        $password = $request->input('password');

        // Recherche par email ou par téléphone
        $user = User::withoutGlobalScopes()
            ->where(function ($query) use ($login) {
                $query->where('email', $login)
                    ->orWhere('phone', $login);
            })
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Votre compte est inactif ou désactivé. Veuillez contacter votre gérant.',
            ], 403);
        }

        if ($user->tenant && $user->tenant->status === 'suspended') {
            return response()->json([
                'message' => 'L\'accès à votre entreprise est temporairement suspendu.',
            ], 403);
        }

        $user->update(['last_active_at' => now()]);

        $deviceName = $request->input('device_name', 'web-browser');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'token' => $token,
            'user' => $user->load(['roles', 'permissions', 'tenant', 'stores']),
            'current_store' => $user->defaultStore(),
        ]);
    }

    /**
     * Connexion rapide par code PIN sur l'appareil de la boutique (F-AUTH-04).
     */
    public function pinLogin(PinLoginRequest $request): JsonResponse
    {
        $storeId = $request->input('store_id');
        $pinCode = $request->input('pin_code');

        $store = Store::withoutGlobalScopes()->findOrFail($storeId);

        // Récupérer les utilisateurs actifs rattachés à cette boutique
        $users = $store->users()
            ->withoutGlobalScopes()
            ->where('status', 'active')
            ->get();

        $authenticatedUser = null;

        foreach ($users as $user) {
            if ($user->verifyPin($pinCode)) {
                $authenticatedUser = $user;
                break;
            }
        }

        if (! $authenticatedUser) {
            throw ValidationException::withMessages([
                'pin_code' => ['Code PIN invalide pour cette boutique.'],
            ]);
        }

        $authenticatedUser->update(['last_active_at' => now()]);

        $deviceName = $request->input('device_name', 'pos-terminal');
        $token = $authenticatedUser->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Connexion caissier réussie.',
            'token' => $token,
            'user' => $authenticatedUser->load(['roles', 'permissions', 'tenant']),
            'current_store' => $store,
        ]);
    }

    /**
     * Obtenir les informations de l'utilisateur connecté (F-ORG-03).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'permissions', 'tenant', 'stores']);

        return response()->json([
            'user' => $user,
            'current_store' => $user->defaultStore(),
        ]);
    }

    /**
     * Déconnexion (révocation du token actuel).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion effectuée avec succès.',
        ]);
    }
}
