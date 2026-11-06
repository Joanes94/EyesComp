<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_owner_can_register_and_creates_tenant_and_store(): void
    {
        $response = $this->postJson('/api/auth/register-owner', [
            'name' => 'Koffi Mensah',
            'phone' => '+22997001122',
            'email' => 'koffi@mensah-store.bj',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'business_name' => 'Quincaillerie Mensah & Fils',
            'business_type' => 'quincaillerie',
            'store_address' => 'Akpakpa, Cotonou',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'phone', 'email', 'tenant_id'],
                'current_store' => ['id', 'name', 'code', 'currency'],
            ]);

        // Vérification de la création de l'entreprise
        $this->assertDatabaseHas('tenants', [
            'name' => 'Quincaillerie Mensah & Fils',
            'business_type' => 'quincaillerie',
            'status' => 'trial',
            'currency' => 'XOF',
        ]);

        // Vérification de la création de la boutique
        $this->assertDatabaseHas('stores', [
            'name' => 'Quincaillerie Mensah & Fils',
            'code' => 'BOUTIQUE-01',
        ]);

        // Vérification du rôle propriétaire
        $user = User::where('phone', '+22997001122')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('owner'));
    }

    public function test_user_can_login_with_email(): void
    {
        $tenant = Tenant::create([
            'name' => 'Alimentation Le Bonheur',
            'slug' => 'alimentation-le-bonheur',
            'business_type' => 'alimentation',
            'currency' => 'XOF',
            'status' => 'active',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Amina Zinsou',
            'email' => 'amina@bonheur.com',
            'phone' => '+22995123456',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'amina@bonheur.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user',
            ]);
    }

    public function test_user_can_login_with_phone_number(): void
    {
        $tenant = Tenant::create([
            'name' => 'Boutique Carrefour',
            'slug' => 'boutique-carrefour',
            'currency' => 'XOF',
            'status' => 'active',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Sékou Traoré',
            'phone' => '+22996554433',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => '+22996554433',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user',
            ]);
    }

    public function test_cashier_can_login_with_pin_code_for_store(): void
    {
        $tenant = Tenant::create([
            'name' => 'Dépôt de Boissons Glo-Djigbé',
            'slug' => 'depot-glo-djigbe',
            'currency' => 'XOF',
            'status' => 'active',
        ]);

        $store = Store::create([
            'tenant_id' => $tenant->id,
            'name' => 'Point de vente 1',
            'code' => 'PV01',
            'is_active' => true,
        ]);

        $cashier = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Mireille Caissière',
            'phone' => '+22990112233',
            'password' => Hash::make('unusedpassword'),
            'pin_code' => Hash::make('1234'),
            'status' => 'active',
        ]);
        $cashier->assignRole('cashier');
        $cashier->stores()->attach($store->id, ['is_default' => true]);

        $response = $this->postJson('/api/auth/pin-login', [
            'store_id' => $store->id,
            'pin_code' => '1234',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.name', 'Mireille Caissière')
            ->assertJsonPath('current_store.id', $store->id)
            ->assertJsonStructure(['token', 'user', 'current_store']);
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $tenant = Tenant::create([
            'name' => 'Ma Boutique',
            'slug' => 'ma-boutique',
            'currency' => 'XOF',
            'status' => 'active',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Proprio Test',
            'email' => 'proprio@test.com',
            'phone' => '+22960000000',
            'password' => Hash::make('secret'),
            'status' => 'active',
        ]);
        $user->assignRole('owner');

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('user.name', 'Proprio Test')
            ->assertJsonPath('user.tenant.id', $tenant->id);
    }
}
