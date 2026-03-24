<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\Category;
use App\Models\Product;
use App\Models\StoreProductPortion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    private $allStores;

    public function run(): void
    {
        // 1. Sadece "gorukle" şubesi
        $this->allStores = [
            'gorukle' => Store::firstOrCreate(['slug' => 'gorukle'], ['name' => 'Görükle', 'theme_color' => '#ffb000']),
        ];

        // 2. Menü Yapısı (Kullanıcı Tarafından Belirlenen Hiyerarşi)
        $menuTree = [
            'Yiyecek' => [
                'type' => 'food',
                'children' => [
                    'Aperitif' => [
                        'children' => [
                            'Patates' => [
                                'products' => [
                                    ['name' => 'Patates Kızartması', 'price' => 200],
                                    ['name' => 'Trüflü Tulumlu Patates', 'description' => 'Patates, İzmir Tulum, Trüf Yağı, Taze Biberiye', 'price' => 230],
                                    ['name' => 'Crash Cips', 'description' => 'Ev yapımı patates cipsi.', 'price' => 180],
                                ]
                            ],
                            'Paylaşımlı Tabaklar' => [
                                'products' => [
                                    ['name' => 'Bira Tabağı', 'description' => 'Tenders, Füme Sosis, Pancarlı Hellim, Çıtır Patates, Soğan Halkası, Çıtır Atom vb.', 'price' => 420],
                                    ['name' => 'XL Bira Tabağı', 'description' => 'Çıtır Tavuk But, Çıtır Atom, Jalapeno Poppers, Füme Sosis, Kıymalı Sigara Böreği vb.', 'price' => 530],
                                ]
                            ],
                        ]
                    ],
                    'Pizza' => [
                        'children' => [
                            'Pizza' => [
                                'products' => [
                                    ['name' => 'Margarita', 'description' => 'Fesleğen, İzmir Tulum, Suda Mozzarella, Pizza Sos', 'price' => 410],
                                    ['name' => 'Karışık', 'description' => 'Dana Sucuk, Çarliston, Zeytin, Kültür Mantarı, Mısır, Mozzarella, Pizza Sos', 'price' => 440],
                                    ['name' => 'Barbekü Tavuklu Pizza', 'description' => 'Barbekü Sos, Izgara Tavuk Bonfile, Mor Soğan, Mozzarella, Pizza Sos', 'price' => 430],
                                    ['name' => 'Mushrooming', 'description' => 'Trüf Krema, 3 çeşit mantar, Mozzarella, Pizza Sos', 'price' => 420],
                                    ['name' => 'Palatable', 'description' => 'Fesleğen, Kurutulmuş Domates, Cheddar, Palatable Sos', 'price' => 490],
                                    ['name' => 'Puerto', 'description' => 'Dana Eti, Kapya, Mor Soğan, Mozzarella, Pizza Sos', 'price' => 510],
                                    ['name' => 'Dört Peynirli Pizza', 'description' => 'Cheddar, İzmir Tulum, Ezine, Mozzarella, Pizza Sos', 'price' => 470],
                                ]
                            ],
                        ]
                    ],
                    'Burger' => [
                        'children' => [
                            'Et Burger' => [
                                'products' => [
                                    ['name' => 'With Cheese Burger', 'description' => 'Dana Köfte, Pane Kaşar/Cheddar, Jambon, Trüflü Ballı Mayonez', 'price' => 450],
                                    ['name' => 'Cherry Sauce Burger', 'description' => 'Dana Köfte, Ezine, Izgara Hellim, Vişne Sos', 'price' => 420],
                                    ['name' => 'Hamburger', 'description' => 'Dana Köfte, Dilim Cheddar, Karamelize Soğan, Marul, Domates, Turşu', 'price' => 390],
                                    ['name' => 'Trüflü Burger', 'description' => 'Dana Köfte, Dilim Kaşar, Slice Mantar, Domates, Trüf Sos', 'price' => 410],
                                ]
                            ],
                            'Tavuk Burger' => [
                                'products' => [
                                    ['name' => 'Cheddar Soslu Crash Chicken', 'description' => '3 Parça Panelenmiş Tavuk But, Mor Lahana Turşusu, Cheddar Sos', 'price' => 320],
                                    ['name' => 'Barbekü Soslu Crash Chicken', 'description' => '3 Parça Panelenmiş Tavuk But, Acı Turşu, Barbekü Sos', 'price' => 330],
                                    ['name' => 'Chilli Soslu Crash Chicken', 'description' => '3 Parça Panelenmiş Tavuk But, Acı Turşu, Chilli Sos', 'price' => 320],
                                ]
                            ],
                        ]
                    ],
                    'Salata' => [
                        'children' => [
                            'Salata' => [
                                'products' => [
                                    ['name' => 'Sezar Salata', 'description' => 'Izgara Tavuk But, İzmir Tulum, Sezar Sos, Marul, Kruton', 'price' => 340],
                                ]
                            ],
                        ]
                    ],
                    'Eşlikçi' => [
                        'children' => [
                            'Çerez' => [
                                'products' => [
                                    ['name' => 'Tuzlu Fıstık', 'price' => 100],
                                    ['name' => 'Karışık Çerez', 'description' => 'Kaju, Antep Fıstığı, Badem, Fındık', 'price' => 240],
                                ]
                            ],
                            'Yancı' => [
                                'products' => [
                                    ['name' => 'Taze Kaşar', 'price' => 180],
                                    ['name' => 'Söğüş', 'description' => 'Salatalık, Havuç, Limon Suyu', 'price' => 120],
                                    ['name' => 'Salatalık Turşusu', 'price' => 120],
                                ]
                            ],
                        ]
                    ],
                ]
            ],
            'İçecek' => [
                'type' => 'drink',
                'children' => [
                    'Kokteyl' => [
                        'children' => [
                            'Crash Kokteyl' => [
                                'products' => [
                                    ['name' => 'Kuzukulağı Tonik', 'description' => 'Beefeater, Kuzukulağı Cordial, Tonik', 'price' => 380],
                                    ['name' => 'Apple Pie', 'description' => 'Jameson, Kırmızı Elma, Tarçın', 'price' => 380],
                                    ['name' => 'Ebem Ekşisi', 'description' => 'Absolut Votka, Kuzukulağı, Yeşil Elma, Portakal', 'price' => 380],
                                    ['name' => 'Madagascar', 'description' => 'Havana Club 3, Malibu, Beyaz Şeftali, Vanilya, Portakal', 'price' => 380],
                                    ['name' => 'Aperolli', 'description' => 'Beefeater Cin, Aperol, Narenciye Karışımı', 'price' => 380],
                                ]
                            ],
                            'Klasik Kokteyl' => [
                                'products' => [
                                    ['name' => 'Cin Tonik', 'description' => 'Beefeater, Tonik, Limon Suyu', 'portions' => [['name' => 'Tek', 'price' => 350], ['name' => 'Duble', 'price' => 550]]],
                                    ['name' => 'Mojito', 'description' => 'Havana Club 3, Nane, Lime, Toz Şeker, Soda', 'price' => 440],
                                    ['name' => 'Long Island Iced Tea', 'description' => '5 beyaz içki, Triple Sec, Narenciye, Kola', 'price' => 480],
                                    ['name' => 'Margarita', 'description' => 'Olmeca Altos Plata, Triple Sec, Lime', 'price' => 440],
                                ]
                            ],
                        ]
                    ],
                    'Bira' => [
                        'children' => [
                            'Fıçı Bira' => [
                                'products' => [
                                    ['name' => 'Efes Fıçı', 'description' => '%4,8 Alkollü 40cl', 'price' => 150],
                                    ['name' => 'Beck\'s Fıçı', 'description' => '%5 Alkollü 40cl', 'price' => 165],
                                ]
                            ],
                            'Şişe Bira' => [
                                'products' => [
                                    ['name' => 'Efes Pilsen', 'description' => '50cl', 'price' => 215],
                                    ['name' => 'Efes Malt', 'description' => '50cl', 'price' => 215],
                                    ['name' => 'Efes Green', 'description' => '50cl', 'price' => 230],
                                    ['name' => 'Bomonti Filtresiz', 'description' => '50cl', 'price' => 240],
                                    ['name' => 'Beck\'s Şişe', 'description' => '50cl', 'price' => 240],
                                    ['name' => 'Bud', 'description' => '50cl', 'price' => 250],
                                    ['name' => 'Corona', 'description' => '35,5cl', 'price' => 280],
                                    ['name' => 'Amsterdam', 'description' => '50cl', 'price' => 320],
                                    ['name' => 'Hoegaarden', 'description' => '33cl', 'price' => 350],
                                ]
                            ],
                        ]
                    ],
                    'Kadeh Alkol' => [
                        'children' => [
                            'Viski' => [
                                'products' => [
                                    ['name' => 'Jameson', 'description' => 'İrlanda Viskisi', 'portions' => [['name' => 'Tek', 'price' => 290], ['name' => 'Duble', 'price' => 550]]],
                                    ['name' => 'Chivas Regal 12', 'description' => 'İskoç Viskisi', 'portions' => [['name' => 'Tek', 'price' => 400], ['name' => 'Duble', 'price' => 520]]],
                                    ['name' => 'Jack Daniel\'s', 'description' => 'Tennessee Viskisi', 'portions' => [['name' => 'Tek', 'price' => 400], ['name' => 'Duble', 'price' => 520]]],
                                    ['name' => 'Chivas Regal 18', 'description' => '18 Yıllık İskoç', 'portions' => [['name' => 'Tek', 'price' => 700], ['name' => 'Duble', 'price' => 900]]],
                                    ['name' => 'Glenlivet Founder\'s', 'description' => 'Single Malt', 'portions' => [['name' => 'Tek', 'price' => 550], ['name' => 'Duble', 'price' => 700]]],
                                ]
                            ],
                            'Tekila' => [
                                'products' => [
                                    ['name' => 'Olmeca Silver', 'price' => 125],
                                    ['name' => 'Olmeca Altos', 'price' => 145],
                                ]
                            ],
                            'Cin' => [
                                'products' => [
                                    ['name' => 'Beefeater Cin', 'price' => 125],
                                    ['name' => 'Malfy Gin', 'price' => 145],
                                ]
                            ],
                            'Votka' => [
                                'products' => [
                                    ['name' => 'Absolut Votka', 'price' => 125],
                                ]
                            ],
                            'Rom' => [
                                'products' => [
                                    ['name' => 'Havana Club 3', 'price' => 125],
                                ]
                            ],
                            'Likör' => [
                                'products' => [
                                    ['name' => 'Jägermeister', 'price' => 160],
                                    ['name' => 'Baileys', 'price' => 125],
                                    ['name' => 'Kahlua', 'price' => 125],
                                ]
                            ],
                        ]
                    ],
                    'Şarap' => [
                        'children' => [
                            'Kadeh Şarap' => [
                                'products' => [
                                    ['name' => 'Kırmızı Kadeh Şarap', 'price' => 250],
                                    ['name' => 'Beyaz Kadeh Şarap', 'price' => 250],
                                ]
                            ],
                            'Kırmızı Şarap' => [
                                'products' => [
                                    ['name' => 'Kırmızı Şişe Şarap', 'price' => 1200],
                                ]
                            ],
                            'Beyaz Şarap' => [
                                'products' => [
                                    ['name' => 'Beyaz Şişe Şarap', 'price' => 1200],
                                ]
                            ],
                        ]
                    ],
                    'Shot' => [
                        'children' => [
                            'Karışım' => [
                                'products' => [
                                    ['name' => 'Winx Shot', 'price' => 125],
                                    ['name' => 'Cumshot', 'price' => 125],
                                    ['name' => 'Jameson Archers Shot', 'price' => 125],
                                    ['name' => 'B-52', 'price' => 125],
                                ]
                            ],
                            'Sek Alkol' => [
                                'products' => [
                                    ['name' => 'Tekila Sek', 'price' => 125],
                                    ['name' => 'Viski Sek', 'price' => 180],
                                ]
                            ],
                        ]
                    ],
                    'Alkolsüz' => [
                        'children' => [
                            'Alkolsüz' => [
                                'products' => [
                                    ['name' => 'Kola', 'description' => '33cl Cam Şişe', 'price' => 75],
                                    ['name' => 'Sprite', 'description' => '33cl Cam Şişe', 'price' => 75],
                                    ['name' => 'Red Bull', 'description' => '25cl Kutu', 'price' => 100],
                                    ['name' => 'Churchill', 'description' => 'Taze Limon Suyu, Tuz, Soda', 'price' => 75],
                                    ['name' => 'Soda', 'description' => '20cl Şişe', 'price' => 50],
                                    ['name' => 'Su', 'description' => '33cl Cam Şişe', 'price' => 40],
                                    ['name' => 'Alkolsüz Bira', 'price' => 200],
                                ]
                            ],
                        ]
                    ],
                ]
            ]
        ];

        // 3. Ağacı İşle
        $this->processMenuTree($menuTree);
    }

    private function processMenuTree(array $tree, ?int $parentId = null, string $defaultType = 'food', string $slugPrefix = '')
    {
        foreach ($tree as $name => $data) {
            $categoryName = $data['name_override'] ?? $name;
            $type = $data['type'] ?? $defaultType;
            $slug = Str::slug($categoryName);
            
            // Eğer bir üst kategori varsa slug'ı benzersizleştir (Çakışmaları önlemek için: Pizza > Pizza gibi)
            $fullSlug = $slugPrefix ? $slugPrefix . '-' . $slug : $slug;

            // Kategori Oluştur/Güncelle (Eşleşme hem slug hem parent_id ile yapılır)
            $category = Category::updateOrCreate(
                ['slug' => $fullSlug],
                [
                    'name' => $categoryName,
                    'parent_id' => $parentId,
                    'type' => $type,
                    'sort_order' => $data['sort_order'] ?? 0,
                ]
            );

            // Ürünleri Oluştur/Güncelle
            if (isset($data['products'])) {
                foreach ($data['products'] as $productData) {
                    $product = Product::updateOrCreate(
                        ['name' => $productData['name'], 'category_id' => $category->id],
                        [
                            'description' => $productData['description'] ?? null,
                            'is_active' => true,
                            'sort_order' => 0
                        ]
                    );

                    // Şubeye Bağla (Sadece Görükle)
                    foreach ($this->allStores as $store) {
                        $product->stores()->syncWithoutDetaching([
                            $store->id => [
                                'is_active' => true,
                                'is_featured' => false,
                                'sort_order' => 0
                            ]
                        ]);

                        // Porsiyonları Oluştur/Güncelle
                        if (isset($productData['portions'])) {
                            foreach ($productData['portions'] as $index => $portion) {
                                StoreProductPortion::updateOrCreate(
                                    [
                                        'store_id' => $store->id, 
                                        'product_id' => $product->id, 
                                        'name' => $portion['name']
                                    ],
                                    [
                                        'price' => $portion['price'],
                                        'is_active' => true,
                                        'sort_order' => $index
                                    ]
                                );
                            }
                        } else {
                            StoreProductPortion::updateOrCreate(
                                [
                                    'store_id' => $store->id, 
                                    'product_id' => $product->id, 
                                    'name' => 'Standart'
                                ],
                                [
                                    'price' => $productData['price'] ?? 100,
                                    'is_active' => true,
                                    'sort_order' => 0
                                ]
                            );
                        }
                    }
                }
            }

            // Alt kategorileri işle (Recursive)
            if (isset($data['children'])) {
                $this->processMenuTree($data['children'], $category->id, $type, $fullSlug);
            }
        }
    }
}
