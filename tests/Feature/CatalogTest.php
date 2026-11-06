<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Store $store1;

    protected Store $store2;

    protected User $owner;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->tenant = Tenant::create([
            'name' => 'Quincaillerie Centrale',
            'slug' => 'quincaillerie-centrale',
            'business_type' => 'quincaillerie',
            'currency' => 'XOF',
            'status' => 'active',
        ]);

        $this->store1 = Store::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Boutique Akpakpa',
            'code' => 'ST01',
            'currency' => 'XOF',
            'is_active' => true,
        ]);

        $this->store2 = Store::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Boutique Calavi',
            'code' => 'ST02',
            'currency' => 'XOF',
            'is_active' => true,
        ]);

        $this->owner = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Patron Test',
            'email' => 'patron@centrale.bj',
            'phone' => '+22997000001',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);
        $this->owner->assignRole('owner');
        $this->owner->stores()->attach($this->store1->id, ['is_default' => true]);

        $this->token = $this->owner->createToken('test-token')->plainTextToken;
    }

    public function test_can_create_category_and_sub_category(): void
    {
        // Catégorie parent
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/categories', [
                'name' => 'Peintures & Enduits',
                'description' => 'Peintures pour bâtiment',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('category.name', 'Peintures & Enduits')
            ->assertJsonPath('category.slug', 'peintures-enduits');

        $parentId = $response->json('category.id');

        // Sous-catégorie
        $subResponse = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/categories', [
                'name' => 'Peinture à l\'eau',
                'parent_id' => $parentId,
            ]);

        $subResponse->assertStatus(201)
            ->assertJsonPath('category.parent_id', $parentId);

        // Liste des catégories
        $listResponse = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/categories');

        $listResponse->assertStatus(200)
            ->assertJsonCount(1, 'categories')
            ->assertJsonPath('categories.0.children.0.name', 'Peinture à l\'eau');
    }

    public function test_can_create_product_with_units_and_store_prices(): void
    {
        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Ciments & Béton',
            'slug' => 'ciments-beton',
        ]);

        $payload = [
            'name' => 'Ciment Dangote 42.5',
            'category_id' => $category->id,
            'sku' => 'CIM-DAN-50KG',
            'barcode' => '6151234567890',
            'base_unit' => 'sac',
            'purchase_price' => 4500, // 4 500 XOF
            'sale_price' => 5200,     // 5 200 XOF
            'alert_threshold' => 20,
            'description' => 'Sac de ciment de 50kg haute résistance',
            'units' => [
                [
                    'unit_name' => 'Tonne (20 sacs)',
                    'conversion_factor' => 20,
                    'sale_price' => 102000, // 102 000 XOF au lieu de 104 000 XOF
                    'barcode' => '6151234567891',
                ],
            ],
            'store_prices' => [
                [
                    'store_id' => $this->store2->id,
                    'sale_price' => 5500, // Prix plus élevé à Calavi pour transport
                    'alert_threshold' => 15,
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/products', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('product.name', 'Ciment Dangote 42.5')
            ->assertJsonPath('product.sale_price', 5200)
            ->assertJsonCount(1, 'product.units')
            ->assertJsonPath('product.units.0.conversion_factor', 20)
            ->assertJsonCount(1, 'product.store_products');

        $productId = $response->json('product.id');
        $product = Product::find($productId);

        // Vérification des prix effectifs par boutique (F-PROD-05)
        $this->assertEquals(5200, $product->getPriceForStore($this->store1)); // Prix standard pour boutique 1
        $this->assertEquals(5500, $product->getPriceForStore($this->store2)); // Prix surchargé pour boutique 2
    }

    public function test_can_search_products_instantaneously_by_barcode_or_name(): void
    {
        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Clou 70mm',
            'sku' => 'CLOU-70',
            'barcode' => '20000000001',
            'base_unit' => 'kg',
            'purchase_price' => 700,
            'sale_price' => 1000,
            'alert_threshold' => 10,
        ]);

        Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Clou 100mm',
            'sku' => 'CLOU-100',
            'barcode' => '20000000002',
            'base_unit' => 'kg',
            'purchase_price' => 800,
            'sale_price' => 1200,
            'alert_threshold' => 10,
        ]);

        // Recherche par nom
        $searchResponse = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/products/search?q=Clou');

        $searchResponse->assertStatus(200)
            ->assertJsonCount(2, 'results');

        // Recherche par code-barres exact (scan caméra / lecteur POS)
        $barcodeResponse = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/products/search?barcode=20000000001');

        $barcodeResponse->assertStatus(200)
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.sku', 'CLOU-70');
    }

    public function test_can_archive_and_restore_product(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Produit Ancien',
            'purchase_price' => 1000,
            'sale_price' => 1500,
            'status' => 'active',
        ]);

        $archiveResponse = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/products/{$product->id}/archive");

        $archiveResponse->assertStatus(200)
            ->assertJsonPath('product.status', 'archived');

        $this->assertEquals('archived', $product->fresh()->status);

        $restoreResponse = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/products/{$product->id}/restore");

        $restoreResponse->assertStatus(200)
            ->assertJsonPath('product.status', 'active');

        $this->assertEquals('active', $product->fresh()->status);
    }

    public function test_tenant_cannot_see_products_of_another_tenant(): void
    {
        // Autre entreprise
        $otherTenant = Tenant::create([
            'name' => 'Autre Commerce',
            'slug' => 'autre-commerce',
            'currency' => 'XOF',
        ]);

        Product::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Produit Secret Autre Entreprise',
            'purchase_price' => 500,
            'sale_price' => 1000,
            'status' => 'active',
        ]);

        // Notre utilisateur interroge la liste
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/products');

        $response->assertStatus(200);
        $this->assertStringNotContainsString('Produit Secret Autre Entreprise', json_encode($response->json()));
    }
}
