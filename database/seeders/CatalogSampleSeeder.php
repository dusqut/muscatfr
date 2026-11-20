<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSampleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $brand = Brand::firstOrCreate(
                ['slug' => 'muscat-fragrances-demo'],
                ['name' => 'Muscat Fragrances — Demo']
            );

            $categories = collect([
                ['slug' => 'oud', 'name_en' => 'Oud', 'name_ar' => 'العود', 'sort_order' => 1],
                ['slug' => 'floral', 'name_en' => 'Floral', 'name_ar' => 'الأزهار', 'sort_order' => 2],
                ['slug' => 'amber', 'name_en' => 'Amber', 'name_ar' => 'العنبر', 'sort_order' => 3],
            ])->mapWithKeys(function (array $category): array {
                $model = Category::firstOrCreate(
                    ['slug' => $category['slug']],
                    $category
                );

                return [$category['slug'] => $model];
            });

            $samples = [
                [
                    'slug' => 'demo-oud-01',
                    'category' => 'oud',
                    'sku_prefix' => 'DEMO-OU',
                    'name_en' => 'Demo Oud 01',
                    'name_ar' => 'عود تجريبي ٠١',
                    'prices' => ['30' => '12.000', '50' => '18.500'],
                ],
                [
                    'slug' => 'demo-rose-01',
                    'category' => 'floral',
                    'sku_prefix' => 'DEMO-RO',
                    'name_en' => 'Demo Rose 01',
                    'name_ar' => 'ورد تجريبي ٠١',
                    'prices' => ['30' => '10.000', '50' => '16.000'],
                ],
                [
                    'slug' => 'demo-amber-01',
                    'category' => 'amber',
                    'sku_prefix' => 'DEMO-AM',
                    'name_en' => 'Demo Amber 01',
                    'name_ar' => 'عنبر تجريبي ٠١',
                    'prices' => ['30' => '11.000', '50' => '17.500'],
                ],
            ];

            foreach ($samples as $sample) {
                $product = Product::firstOrCreate(
                    ['slug' => $sample['slug']],
                    [
                        'brand_id' => $brand->id,
                        'category_id' => $categories->get($sample['category'])->id,
                        'concentration' => 'EDP',
                        'audience' => 'unisex',
                        'country_of_origin' => 'Oman',
                        'status' => 'draft',
                        'is_sample' => true,
                    ]
                );

                foreach (['en', 'ar'] as $locale) {
                    $product->translations()->firstOrCreate(
                        ['locale' => $locale],
                        [
                            'name' => $sample["name_{$locale}"],
                            'description' => $locale === 'ar'
                                ? 'بيانات تجريبية فقط — يُرجى استبدالها بالمعلومات المعتمدة قبل النشر.'
                                : 'Sample catalogue content only. Replace with approved product information before publishing.',
                        ]
                    );
                }

                foreach ($sample['prices'] as $volume => $price) {
                    $product->variants()->firstOrCreate(
                        ['sku' => "{$sample['sku_prefix']}-{$volume}"],
                        [
                            'volume_ml' => (int) $volume,
                            'price' => $price,
                            'stock_on_hand' => 0,
                            'is_active' => false,
                        ]
                    );
                }
            }
        });
    }
}
