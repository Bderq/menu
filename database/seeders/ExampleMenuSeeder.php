<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExampleMenuSeeder extends Seeder
{
    private $allStores;

    public function run(): void
    {
        // 1. Sadece "gorukle" şubesi
        $this->allStores = [
            'gorukle' => Store::firstOrCreate(['slug' => 'gorukle'], ['name' => 'Görükle', 'theme_color' => '#ffb000']),
        ];

        // 2. Menü Yapısı (Fiyatlar ve Kategoriler Sadeleştirildi)
        $menuTree = [
            'Yiyecek' => [
                'type' => 'food',
                'children' => [
                    'Aperitif' => [
                        'products' => [
                            // Örnek 1: Klasik tek fiyatlı ürün
                            [
                                'name' => 'Patates Kızartması', 
                                'description' => 'Sade servis edilir.', 
                                'price' => 200 // Yalnızca price verilirse "Standart" porsiyon açar
                            ],
                        ]
                    ]
                ]
            ],
            'İçecek' => [
                'type' => 'drink',
                'children' => [
                    'Viski' => [
                        'products' => [
                            // Örnek 2: Çoklu porsiyona (portions) sahip ürün
                            [
                                'name' => 'Jameson', 
                                'description' => 'Üçlü damıtma yöntemiyle üretilen yumuşak bir İrlanda viskisi.', 
                                'portions' => [
                                    ['name' => 'Shot', 'price' => 125],
                                    ['name' => 'Tek', 'price' => 290],
                                    ['name' => 'Duble', 'price' => 550],
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        ];

        // 3. Ağacı İşle
        $this->processMenuTree($menuTree);
    }

    private function processMenuTree(array $tree, ?int $parentId = null, string $defaultType = 'food')
    {
        foreach ($tree as $name => $data) {
            $categoryName = $data['name_override'] ?? $name;
            $type = $data['type'] ?? $defaultType;
            $slug = Str::slug($categoryName);

            // Kategori Oluştur
            $category = \App\Models\Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $categoryName,
                    'parent_id' => $parentId,
                    'type' => $type,
                    'sort_order' => 0,
                ]
            );

            // Ürünleri Oluştur
            if (isset($data['products'])) {
                foreach ($data['products'] as $productData) {
                    $names = is_array($productData['name']) ? $productData['name'] : [$productData['name']];
                    
                    foreach ($names as $productName) {
                        
                        $product = \App\Models\Product::updateOrCreate(
                            ['name' => $productName, 'category_id' => $category->id],
                            [
                                'description' => $productData['description'] ?? null,
                                'is_active' => true,
                                'sort_order' => 0
                            ]
                        );

                        // Şube ile İlişkilendir (Sadece Görükle)
                        foreach ($this->allStores as $store) {
                            $product->stores()->syncWithoutDetaching([
                                $store->id => [
                                    'is_active' => true,
                                    'is_featured' => false,
                                    'sort_order' => 0
                                ]
                            ]);

                            // YENİ ÇOKLU PORSİYON ALTYAPISI 
                            // 1. Portions dizisi varsa:
                            if (isset($productData['portions']) && is_array($productData['portions'])) {
                                foreach ($productData['portions'] as $index => $portion) {
                                    \App\Models\StoreProductPortion::updateOrCreate(
                                        [
                                            'store_id' => $store->id, 
                                            'product_id' => $product->id, 
                                            'name' => $portion['name']
                                        ],
                                        [
                                            'price' => $portion['price'],
                                            'is_active' => true,
                                            'sort_order' => $index // Listeleme sırası
                                        ]
                                    );
                                }
                            } 
                            // 2. Sadece 'price' varsa (Standart Porsiyon)
                            else {
                                \App\Models\StoreProductPortion::updateOrCreate(
                                    [
                                        'store_id' => $store->id, 
                                        'product_id' => $product->id, 
                                        'name' => 'Standart'
                                    ],
                                    [
                                        'price' => $productData['price'] ?? 100, // Fiyat gelmezse varsayılan 100
                                        'is_active' => true,
                                        'sort_order' => 0
                                    ]
                                );
                            }
                        }
                    }
                }
            }

            // Çocuk kategorileri öz yinelemeli gönder
            if (isset($data['children'])) {
                $this->processMenuTree($data['children'], $category->id, $type);
            }
        }
    }
}
