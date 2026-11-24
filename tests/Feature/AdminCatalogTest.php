<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSampleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.products.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_users_cannot_manage_catalog(): void
    {
        $customer = $this->user('customer');

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_admin_can_sign_in_to_dashboard_and_view_sidebar_sections(): void
    {
        $this->seed(CatalogSampleSeeder::class);
        $admin = $this->user('admin');

        $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'catalog-test-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.home'))
            ->assertRedirect(route('admin.dashboard'));

        $response = $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Overview')
            ->assertSee('Demo Oud 01');

        foreach ([
            'DASHBOARD',
            'ORDERS &amp; FULFILMENT',
            'PRODUCT CATALOGUE',
            'INVENTORY',
            'CUSTOMERS',
            'MARKETING',
            'STOREFRONT &amp; CONTENT',
            'PAYMENTS &amp; FINANCE',
            'REPORTS &amp; ANALYTICS',
            'SUPPORT',
            'SETTINGS &amp; ADMINISTRATION',
            'Suppliers &amp; Purchase Orders',
            'Phase 2',
            'Loyalty &amp; Referral Programme',
            'Later',
        ] as $expected) {
            $response->assertSee($expected, false);
        }

        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Demo Oud 01')
            ->assertSee('Sample');
    }

    public function test_all_admin_sidebar_routes_render_successfully(): void
    {
        $this->seed(CatalogSampleSeeder::class);
        $this->actingAs($this->user('admin'));

        foreach (config('admin_navigation') as $section) {
            foreach ($section['items'] as $item) {
                $url = route($item['route'], $item['route_params'] ?? []);
                $this->get($url)
                    ->assertOk()
                    ->assertSee($item['label']);
            }
        }
    }

    public function test_admin_can_create_a_bilingual_product_with_multiple_variants(): void
    {
        $this->seed(CatalogSampleSeeder::class);
        $this->actingAs($this->user('admin'));

        $response = $this->post(route('admin.products.store'), $this->productData());

        $product = Product::query()
            ->with(['translations', 'variants'])
            ->where('slug', 'rose-of-muscat')
            ->firstOrFail();
        $response->assertRedirect(route('admin.products.edit', $product));

        $this->assertSame('draft', $product->status);
        $this->assertFalse($product->is_sample);
        $this->assertSame('A floral study.', $product->translated('en')?->description);
        $this->assertSame('دراسة زهرية.', $product->translated('ar')?->description);
        $this->assertCount(2, $product->variants);
        $this->assertSame('22.500', $product->variants->firstWhere('sku', 'MF-ROSE-30')?->price);
    }

    public function test_admin_can_upload_product_images_with_bilingual_alt_text(): void
    {
        Storage::fake('public');
        $this->seed(CatalogSampleSeeder::class);
        $this->actingAs($this->user('admin'));
        $data = $this->productData();
        $data['images'] = [UploadedFile::fake()->createWithContent(
            'rose.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC')
        )];

        $this->post(route('admin.products.store'), $data)
            ->assertRedirect();

        $product = Product::query()->where('slug', 'rose-of-muscat')->firstOrFail();
        $image = $product->images()->firstOrFail();

        Storage::disk('public')->assertExists($image->path);
        $this->assertSame('Rose of Muscat', $image->alt_en);
        $this->assertSame('ورد مسقط', $image->alt_ar);

        $this->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee("remove-image-{$image->id}");

        $data['variants'] = $product->variants->map(fn (ProductVariant $variant) => [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'volume_ml' => $variant->volume_ml,
            'price' => $variant->price,
            'stock_on_hand' => $variant->stock_on_hand,
        ])->all();
        unset($data['images']);
        $data['remove_images'] = [$image->id];

        $this->put(route('admin.products.update', $product), $data)
            ->assertRedirect();

        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_sample_products_cannot_be_published_until_marked_as_real_catalog_data(): void
    {
        $this->seed(CatalogSampleSeeder::class);
        $this->actingAs($this->user('admin'));

        $product = Product::query()->with(['translations', 'variants'])->where('is_sample', true)->firstOrFail();
        $data = $this->productData();
        $data['slug'] = $product->slug;
        $data['brand_id'] = $product->brand_id;
        $data['category_id'] = $product->category_id;
        $data['status'] = 'published';
        $data['is_sample'] = '1';
        $data['translations'] = $product->translations->keyBy('locale')
            ->map(fn ($translation) => [
                'name' => $translation->name,
                'description' => $translation->description,
            ])->all();
        $data['variants'] = $product->variants->map(fn (ProductVariant $variant) => [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'volume_ml' => $variant->volume_ml,
            'price' => $variant->price,
            'stock_on_hand' => $variant->stock_on_hand,
        ])->all();

        $this->put(route('admin.products.update', $product), $data)
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'draft',
            'is_sample' => true,
        ]);
    }

    public function test_sample_seeder_is_repeatable_and_keeps_examples_unavailable(): void
    {
        $this->seed(CatalogSampleSeeder::class);
        $this->seed(CatalogSampleSeeder::class);

        $this->assertDatabaseCount('products', 3);
        $this->assertDatabaseCount('product_variants', 6);
        $this->assertDatabaseHas('products', ['slug' => 'demo-oud-01', 'status' => 'draft', 'is_sample' => true]);
        $this->assertDatabaseHas('product_variants', ['sku' => 'DEMO-OU-30', 'stock_on_hand' => 0, 'is_active' => false]);
    }

    private function user(string $role): User
    {
        return User::query()->create([
            'name' => 'Catalog test user',
            'email' => "{$role}@example.test",
            'password' => Hash::make('catalog-test-password'),
            'role' => $role,
        ]);
    }

    private function productData(): array
    {
        return [
            'slug' => 'rose-of-muscat',
            'brand_id' => Brand::query()->where('slug', 'muscat-fragrances-demo')->value('id'),
            'category_id' => Category::query()->where('slug', 'floral')->value('id'),
            'concentration' => 'EDP',
            'audience' => 'unisex',
            'country_of_origin' => 'Oman',
            'status' => 'draft',
            'translations' => [
                'en' => ['name' => 'Rose of Muscat', 'description' => 'A floral study.'],
                'ar' => ['name' => 'ورد مسقط', 'description' => 'دراسة زهرية.'],
            ],
            'variants' => [
                ['sku' => 'MF-ROSE-30', 'volume_ml' => 30, 'price' => '22.500', 'stock_on_hand' => 3],
                ['sku' => 'MF-ROSE-50', 'volume_ml' => 50, 'price' => '31.000', 'stock_on_hand' => 2],
            ],
        ];
    }
}
