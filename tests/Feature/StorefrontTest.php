<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\StorefrontSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['launch.enabled' => false]);
        File::delete(storage_path('app/storefront-settings.json'));
    }

    protected function tearDown(): void
    {
        File::delete(storage_path('app/storefront-settings.json'));
        parent::tearDown();
    }

    public function test_homepage_redirects_to_the_english_storefront(): void
    {
        $this->get('/')
            ->assertRedirect(route('store.home', ['locale' => 'en']));
    }

    public function test_english_storefront_renders_with_search_metadata(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('<html lang="en" dir="ltr">', false)
            ->assertSee('Find the scent that feels like', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('hreflang="ar"', false);
    }

    public function test_arabic_storefront_renders_right_to_left_with_language_switch(): void
    {
        $this->get('/ar')
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('اكتشفوا عطراً يشبه', false)
            ->assertSee('href="http://localhost/en"', false);
    }

    public function test_unsupported_locales_are_not_storefront_routes(): void
    {
        $this->get('/fr')->assertNotFound();
    }

    public function test_desktop_and_mobile_navigation_render_all_required_items_in_both_locales(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('OMR 40')
            ->assertSee('images/logo.webp')
            ->assertSee('Search perfumes, brands, products...')
            ->assertSee('New In')
            ->assertSee('Shop')
            ->assertSee('Brands')
            ->assertSee('Oud &amp; Arabian', false)
            ->assertSee('Best Sellers')
            ->assertSee('Gifts')
            ->assertSee('Offers')
            ->assertSee('Find Our Store')
            ->assertSee('Track My Order')
            ->assertSee('Customer Support')
            ->assertSee('Currency: OMR')
            ->assertSee('Shop All');

        $this->get('/ar')
            ->assertOk()
            ->assertSee('وصل حديثاً')
            ->assertSee('تسوّق')
            ->assertSee('العلامات التجارية')
            ->assertSee('العود والعطور العربية')
            ->assertSee('الأكثر مبيعاً')
            ->assertSee('الهدايا')
            ->assertSee('العروض')
            ->assertSee('العملة: OMR');
    }

    public function test_navigation_reflects_real_published_catalog_and_omits_unsupported_categories(): void
    {
        $brand = Brand::query()->create(['name' => 'Amouage', 'slug' => 'amouage']);
        $oud = Category::query()->create(['slug' => 'oud', 'name_en' => 'Oud', 'name_ar' => 'العود', 'sort_order' => 1, 'is_active' => true]);

        $product = Product::query()->create([
            'brand_id' => $brand->id,
            'category_id' => $oud->id,
            'slug' => 'royal-muscat-oud',
            'concentration' => 'EDP',
            'audience' => 'unisex',
            'status' => 'published',
            'is_sample' => false,
            'published_at' => now(),
        ]);

        $product->translations()->createMany([
            ['locale' => 'en', 'name' => 'Royal Muscat Oud', 'description' => 'Warm Omani frankincense and oud.', 'top_notes' => 'Frankincense'],
            ['locale' => 'ar', 'name' => 'عود مسقط الملكي', 'description' => 'لبان عُماني وعود فاخر.', 'top_notes' => 'لبان'],
        ]);

        $product->variants()->create([
            'sku' => 'MF-RMO-50',
            'volume_ml' => 50,
            'price' => '42.000',
            'stock_on_hand' => 10,
            'is_active' => true,
        ]);

        $this->get('/en')
            ->assertOk()
            ->assertSee('Amouage')
            ->assertSee('OMR 25 – 50')
            ->assertDontSee('Under OMR 10')
            ->assertDontSee('Bakhoor Accessories');

        $this->get('/en/shop?q=Frankincense')
            ->assertOk()
            ->assertSee('Royal Muscat Oud')
            ->assertSee('42.000');

        $this->getJson('/en/search/suggestions?q=Amouage')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Royal Muscat Oud'])
            ->assertJsonFragment(['name' => 'Amouage']);

        $this->get('/en/brands/amouage')
            ->assertOk()
            ->assertSee('Royal Muscat Oud');
    }

    public function test_all_storefront_navigation_destinations_resolve_cleanly(): void
    {
        foreach (['en', 'ar'] as $locale) {
            foreach ([
                'store.home',
                'store.shop',
                'store.new-in',
                'store.brands',
                'store.oud-arabian',
                'store.best-sellers',
                'store.gifts',
                'store.offers',
                'store.stores',
                'store.track-order',
                'store.support',
                'store.account',
                'store.wishlist',
                'store.bag',
            ] as $routeName) {
                $this->get(route($routeName, ['locale' => $locale]))->assertOk();
            }

            foreach ([
                'about-us',
                'authenticity',
                'shipping-delivery',
                'returns-refunds',
                'faq',
                'privacy-policy',
                'terms-of-use',
                'cookie-policy',
                'disclaimer',
            ] as $pageSlug) {
                $this->get(route('store.page', ['locale' => $locale, 'slug' => $pageSlug]))->assertOk();
            }
        }
    }

    public function test_footer_displays_social_handles_and_ecommerce_sections(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('https://www.facebook.com/MuscatFragrances', false)
            ->assertSee('https://www.instagram.com/MuscatFragrances', false)
            ->assertSee('https://www.tiktok.com/@MuscatFragrances', false)
            ->assertSee('https://x.com/MuscatFragrances', false)
            ->assertSee('Follow @MuscatFragrances')
            ->assertSee('Privacy Policy')
            ->assertSee('Terms of Use')
            ->assertSee('Shipping &amp; Delivery', false)
            ->assertSee('Returns &amp; Refunds', false);

        $this->get('/ar')
            ->assertOk()
            ->assertSee('https://www.facebook.com/MuscatFragrances', false)
            ->assertSee('سياسة الخصوصية')
            ->assertSee('شروط الاستخدام');

        $this->get('/en/support')
            ->assertOk()
            ->assertSee('+96871826515')
            ->assertSee('https://wa.me/96871826515')
            ->assertDontSee('96800000000');
    }

    public function test_admin_can_update_storefront_announcement_without_modifying_source(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'navadmin@example.test',
            'password' => Hash::make('catalog-test-password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.storefront.navigation.update'), [
                'announcement_enabled' => '1',
                'announcement_en' => 'Ramadan Evening Delivery Across Muscat',
                'announcement_ar' => 'توصيل مسائي خاص في مسقط خلال رمضان',
                'announcement_url_path' => '/gifts',
                'free_delivery_threshold_omr' => '25',
                'whatsapp_number' => '+96899999999',
                'gifts_ramadan' => '1',
            ])
            ->assertRedirect(route('admin.storefront.navigation'));

        $this->get('/en')
            ->assertOk()
            ->assertSee('Ramadan Evening Delivery Across Muscat')
            ->assertSee('Ramadan Gifts');

        $this->get('/ar')
            ->assertOk()
            ->assertSee('توصيل مسائي خاص في مسقط خلال رمضان')
            ->assertSee('هدايا رمضان');
    }
}
