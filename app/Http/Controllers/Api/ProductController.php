<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreProductRequest;
use App\Http\Requests\Catalog\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StoreProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Liste des produits avec filtres et pagination (F-PROD-01).
     */
    public function index(Request $request): JsonResponse
    {
        $storeId = $request->input('store_id');
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $status = $request->input('status', 'active');

        $query = Product::with(['category', 'units', 'storeProducts'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');

        $products = $request->has('all')
            ? $query->get()
            : $query->paginate($request->integer('per_page', 25));

        // Formatage pour inclure le prix effectif de la boutique demandée (F-PROD-05)
        $items = $request->has('all') ? $products : $products->items();
        foreach ($items as $product) {
            $product->effective_price = $storeId ? $product->getPriceForStore((int) $storeId) : $product->sale_price;
            $product->effective_alert_threshold = $storeId ? $product->getAlertThresholdForStore((int) $storeId) : $product->alert_threshold;
        }

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Recherche instantanée optimisée pour l'écran de caisse (F-PROD-04).
     * Réponse rapide (< 200ms) par nom, référence ou code-barres.
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim($request->input('q', ''));
        $barcode = trim($request->input('barcode', ''));
        $storeId = $request->integer('store_id');

        if (empty($term) && empty($barcode)) {
            return response()->json(['results' => []]);
        }

        $query = Product::with(['units', 'storeProducts'])
            ->where('status', 'active');

        if (! empty($barcode)) {
            // Recherche exacte par code-barres du produit ou d'une de ses unités
            $query->where(function ($q) use ($barcode) {
                $q->where('barcode', $barcode)
                    ->orWhereHas('units', fn ($u) => $u->where('barcode', $barcode));
            });
        } elseif (! empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%");
            });
        }

        $results = $query->limit(20)->get()->map(function ($product) use ($storeId) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'base_unit' => $product->base_unit,
                'purchase_price' => $product->purchase_price,
                'sale_price' => $storeId ? $product->getPriceForStore($storeId) : $product->sale_price,
                'units' => $product->units->map(function ($unit) {
                    return [
                        'id' => $unit->id,
                        'unit_name' => $unit->unit_name,
                        'conversion_factor' => $unit->conversion_factor,
                        'sale_price' => $unit->sale_price,
                        'barcode' => $unit->barcode,
                    ];
                }),
            ];
        });

        return response()->json([
            'results' => $results,
        ]);
    }

    /**
     * Enregistrer un nouveau produit avec ses unités et ses prix boutique (F-PROD-01).
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = DB::transaction(function () use ($validated) {
            $product = Product::create([
                'category_id' => $validated['category_id'] ?? null,
                'name' => $validated['name'],
                'sku' => $validated['sku'] ?? null,
                'barcode' => $validated['barcode'] ?? null,
                'base_unit' => $validated['base_unit'] ?? 'pièce',
                'purchase_price' => $validated['purchase_price'],
                'sale_price' => $validated['sale_price'],
                'alert_threshold' => $validated['alert_threshold'] ?? 5,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);

            // Unités de vente et conversions (F-PROD-03)
            if (! empty($validated['units'])) {
                foreach ($validated['units'] as $unit) {
                    $product->units()->create([
                        'unit_name' => $unit['unit_name'],
                        'conversion_factor' => $unit['conversion_factor'],
                        'sale_price' => $unit['sale_price'],
                        'barcode' => $unit['barcode'] ?? null,
                    ]);
                }
            }

            // Prix par boutique (F-PROD-05)
            if (! empty($validated['store_prices'])) {
                foreach ($validated['store_prices'] as $storePrice) {
                    StoreProduct::create([
                        'store_id' => $storePrice['store_id'],
                        'product_id' => $product->id,
                        'sale_price' => $storePrice['sale_price'] ?? null,
                        'alert_threshold' => $storePrice['alert_threshold'] ?? null,
                        'is_active' => $storePrice['is_active'] ?? true,
                    ]);
                }
            }

            return $product;
        });

        return response()->json([
            'message' => 'Produit créé avec succès.',
            'product' => $product->load(['category', 'units', 'storeProducts']),
        ], 201);
    }

    /**
     * Afficher les détails d'un produit.
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'product' => $product->load(['category', 'units', 'storeProducts.store']),
        ]);
    }

    /**
     * Mettre à jour un produit.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $product) {
            $product->update([
                'category_id' => $validated['category_id'] ?? $product->category_id,
                'name' => $validated['name'] ?? $product->name,
                'sku' => array_key_exists('sku', $validated) ? $validated['sku'] : $product->sku,
                'barcode' => array_key_exists('barcode', $validated) ? $validated['barcode'] : $product->barcode,
                'base_unit' => $validated['base_unit'] ?? $product->base_unit,
                'purchase_price' => $validated['purchase_price'] ?? $product->purchase_price,
                'sale_price' => $validated['sale_price'] ?? $product->sale_price,
                'alert_threshold' => $validated['alert_threshold'] ?? $product->alert_threshold,
                'description' => array_key_exists('description', $validated) ? $validated['description'] : $product->description,
                'status' => $validated['status'] ?? $product->status,
            ]);

            // Gestion des unités
            if (isset($validated['units'])) {
                $existingIds = [];
                foreach ($validated['units'] as $unitData) {
                    if (! empty($unitData['id'])) {
                        $unit = ProductUnit::where('product_id', $product->id)->find($unitData['id']);
                        if ($unit) {
                            $unit->update($unitData);
                            $existingIds[] = $unit->id;
                        }
                    } else {
                        $newUnit = $product->units()->create($unitData);
                        $existingIds[] = $newUnit->id;
                    }
                }
                // Supprimer les unités qui ne sont plus dans la liste
                $product->units()->whereNotIn('id', $existingIds)->delete();
            }

            // Gestion des prix boutique (F-PROD-05)
            if (isset($validated['store_prices'])) {
                foreach ($validated['store_prices'] as $sp) {
                    StoreProduct::updateOrCreate(
                        ['store_id' => $sp['store_id'], 'product_id' => $product->id],
                        [
                            'sale_price' => $sp['sale_price'] ?? null,
                            'alert_threshold' => $sp['alert_threshold'] ?? null,
                            'is_active' => $sp['is_active'] ?? true,
                        ]
                    );
                }
            }
        });

        return response()->json([
            'message' => 'Produit mis à jour avec succès.',
            'product' => $product->fresh(['category', 'units', 'storeProducts.store']),
        ]);
    }

    /**
     * Archiver un produit (F-PROD-01).
     */
    public function archive(Product $product): JsonResponse
    {
        $product->update(['status' => 'archived']);

        return response()->json([
            'message' => 'Produit archivé avec succès.',
            'product' => $product,
        ]);
    }

    /**
     * Restaurer un produit archivé.
     */
    public function restore(Product $product): JsonResponse
    {
        $product->update(['status' => 'active']);

        return response()->json([
            'message' => 'Produit restauré avec succès.',
            'product' => $product,
        ]);
    }
}
