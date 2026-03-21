<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    private $allStores;

    public function run(): void
    {
        // 1. Stores (Idempotent)
        $this->allStores = [
            'gorukle' => Store::firstOrCreate(['slug' => 'gorukle'], ['name' => 'Görükle', 'theme_color' => '#ffb000']),
            'ozluce' => Store::firstOrCreate(['slug' => 'ozluce'], ['name' => 'Özlüce', 'theme_color' => '#ffb000']),
            'fsm' => Store::firstOrCreate(['slug' => 'fsm'], ['name' => 'FSM', 'theme_color' => '#ffb000']),
            'floyd' => Store::firstOrCreate(['slug' => 'floyd'], ['name' => 'Floyd', 'theme_color' => '#ffb000']),
        ];

        // 2. Define the Menu Structure
        $menuTree = [
            'Yiyecek' => [
                'type' => 'food',
                'children' => [
                    'Aperitif' => [
                        'children' => [
                            'Patatesler' => [
                                'products' => [
                                    ['name' => 'Patates Kızartması', 'description' => 'Sade servis edilir.', 'price' => 120],
                                    ['name' => 'Trüflü Tulumlu Patates', 'description' => 'Patates, İzmir Tulum, Trüf Yağı, Taze Biberiye.', 'price' => 180],
                                ]
                            ],
                            'Paylaşım Tabakları' => [
                                'products' => [
                                    ['name' => 'Bira Tabağı', 'description' => 'Tenders, Füme Sosis, Pancarlı Hellim, Çıtır Patates, Soğan Halkası, Çıtır Atom, Brokoli Topu, Patates ve Soğuk Şişler.', 'price' => 450],
                                    ['name' => 'XL Bira Tabağı', 'description' => 'Çıtır Tavuk But, Çıtır Atom, Çıtır Jalapeno Poppers, Stick Hellim, Füme Sosis, Çıtır Soğan, Kıymalı Sigara Böreği, Samosa, Patates, Pancar Cipsi, Izgara Zeytin, Turşu.', 'price' => 650],
                                ]
                            ],
                            'Atıştırmalık Tavuklar' => [
                                'products' => [
                                    ['name' => 'Çıtır Tavuk (İstersen Acılı)', 'description' => 'Pane Tavuk But, Patates.', 'price' => 240],
                                    ['name' => 'Cheddar Soslu Çıtır Tavuk', 'description' => 'Çıtır Tavuk But, Cheddar Sos, Kurutulmuş Çeri.', 'price' => 260],
                                    ['name' => 'Barbekü Soslu Çıtır Tavuk', 'description' => 'Çıtır Tavuk But, Barbekü Sos, Çıtır Soğan.', 'price' => 260],
                                    ['name' => 'Chilli Soslu Çıtır Tavuk', 'description' => 'Çıtır Tavuk But, Chilli Sos, Chop Maydanoz.', 'price' => 260],
                                ]
                            ]
                        ]
                    ],
                    'Burger' => [
                        'children' => [
                            'Et Burgerler' => [
                                'products' => [
                                    ['name' => 'Hamburger', 'description' => 'Dana Köfte, Dilim Cheddar, Karamelize Soğan, Marul, Domates, Turşu, Burger Sos, Patates ile servis edilir.', 'price' => 320],
                                    ['name' => 'With Cheese', 'description' => 'Dana Köfte, Pane Kaşar, Pane Cheddar, Trüflü Ballı Mayonez, Kıtır Jambon, Patates ile servis edilir.', 'price' => 380],
                                    ['name' => 'Cherry Sauce', 'description' => 'Dana Köfte, Ezine, Izgara Hellim, Vişne Sos, Patates ile servis edilir.', 'price' => 420],
                                    ['name' => 'Trüflü', 'description' => 'Dana Köfte, Dilim Kaşar, Slice Mantar, Domates, Trüf Sos, Patates ile servis edilir.', 'price' => 400],
                                ]
                            ],
                            'Tavuk Burgerler' => [
                                'products' => [
                                    ['name' => 'Cheddar Soslu Crash Chicken', 'description' => '3 Parça Panelenmiş Tavuk But, El Yapımı Mor Lahana Turşusu, Burger Sos, El Yapımı Cheddar Sos, Patates ile servis edilir.', 'price' => 280],
                                    ['name' => 'Barbekü Soslu Crash Chicken', 'description' => '3 Parça Panelenmiş Tavuk But, El Yapımı Acı Turşu, Barbekü Sos, Burger Sos, Patates ile servis edilir.', 'price' => 280],
                                    ['name' => 'Chilli Soslu Crash Chicken', 'description' => '3 Parça Panelenmiş Tavuk But, El Yapımı Acı Turşu, Burger Sos, Chilli Sos, Patates ile servis edilir.', 'price' => 280],
                                ]
                            ]
                        ]
                    ],
                    'Pizza' => [
                        'children' => [
                            'Klasik Pizzalar' => [
                                'products' => [
                                    ['name' => 'Margarita', 'description' => 'Fesleğen, İzmir Tulum, Kurutulmuş Cherry, Suda Mozzarella, Mozzarella, Pizza Sos.', 'price' => 280],
                                    ['name' => 'Karışık', 'description' => 'Dana Sucuk, Çarliston, Zeytin, Kültür Mantarı, Mısır, Mozzarella, Pizza Sos.', 'price' => 340],
                                ]
                            ],
                            'Gurme Pizzalar' => [
                                'products' => [
                                    ['name' => 'Mushrooming', 'description' => 'Trüf Krema, Kestane Mantarı, İstiridye Mantarı, Kültür Mantarı, Mozzarella, Pizza Sos.', 'price' => 360],
                                    ['name' => 'Palatable', 'description' => 'Fesleğen, Kurutulmuş Domates, Cheddar, Suda Mozzarella, Mozzarella, Palatable Sos.', 'price' => 380],
                                    ['name' => 'Puerto', 'description' => 'Dana Eti, Kapya, Mor Soğan, Mozzarella, Pizza Sos.', 'price' => 420],
                                    ['name' => 'Barbekü Tavuklu', 'description' => 'Barbekü Sos, Izgara Tavuk Bonfile, Mor Soğan, Mozzarella, Pizza Sos.', 'price' => 340],
                                    ['name' => 'Dört Peynirli', 'description' => 'Cheddar, İzmir Tulum, Ezine, Mozzarella, Pizza Sos.', 'price' => 360],
                                ]
                            ]
                        ]
                    ],
                    'Soslu Tavuk' => [
                        'children' => [
                            'Soslu Tavuk' => [
                                'products' => [
                                    ['name' => 'Garlic Soslu', 'description' => 'Tavuk Bonfile, Garlic Sos, Taze Soğan, Turşu, Patates Kızartması.', 'price' => 280],
                                    ['name' => 'Barbekü Soslu', 'description' => 'Tavuk Bonfile, Yer Fıstık Parçacıklı Barbekü Sos, Çıtır Soğan Parçaları, Patates Kızartması.', 'price' => 280],
                                    ['name' => 'Cheddar Soslu', 'description' => 'Tavuk Bonfile, Cheddar Sos, Izgara Zeytin, Patates Kızartması.', 'price' => 280],
                                    ['name' => 'Sriracha Soslu', 'description' => 'Tavuk Bonfile, Sriracha Sos, Lime Dilimi, Patates Kızartması.', 'price' => 280],
                                    ['name' => 'Mix Soslu', 'description' => 'Tavuk Bonfile, Garlic, Barbekü, Sriracha, Cheddar Sos, Patates Kızartması.', 'price' => 320],
                                ]
                            ]
                        ]
                    ],
                    'Ana Yemek' => [
                        'children' => [
                            'Izgara ve Schnitzel' => [
                                'products' => [
                                    ['name' => 'Douma', 'description' => 'Izgara Tavuk Bonfile, Humus. Salata ile servis edilir.', 'price' => 320],
                                    ['name' => 'Tavuk Schnitzel', 'description' => 'Pane Tavuk Bonfile, Alman Patates Salatası.', 'price' => 300],
                                    ['name' => 'Mantar Soslu Tavuk Schnitzel', 'description' => 'Pane Tavuk Bonfile, Alman Patates Salatası, Mantar Sos.', 'price' => 340],
                                ]
                            ]
                        ]
                    ],
                    'Salata ve Bowl' => [
                        'children' => [
                            'Salata' => [
                                'products' => [
                                    ['name' => 'Sezar', 'description' => 'Izgara Tavuk But, Çeri Domates, İzmir Tulum, Sezar Sos, Marul, Lolorosso, Kruton.', 'price' => 260],
                                ]
                            ],
                            'Bowl' => [
                                'products' => [
                                    ['name' => 'Vegan Bowl', 'description' => 'Vegan İçli Köfte, 3 Mantarlı Basmati, Sweet Chili Patates, Lolorosso, Mor Lahana Turşusu, Kibrit Salatalık.', 'price' => 280],
                                ]
                            ]
                        ]
                    ],
                    'Eşlikçi' => [
                        'children' => [
                            'Eşlikçi' => [
                                'products' => [
                                    ['name' => 'Taze Kaşar', 'price' => 40],
                                    ['name' => 'Söğüş', 'description' => 'Salatalık, Havuç, Limon Suyu.', 'price' => 50],
                                    ['name' => 'Salatalık Turşusu', 'price' => 30],
                                    ['name' => 'Tuzlu Fıstık', 'price' => 60],
                                    ['name' => 'Karışık Çerez', 'description' => 'Kaju, Antep Fıstığı, Badem, Fındık.', 'price' => 120],
                                ]
                            ]
                        ]
                    ],
                ]
            ],
            'İçecek' => [
                'type' => 'drink',
                'children' => [
                    'Bira' => [
                        'children' => [
                            'Fıçı Bira' => [
                                'products' => [
                                    ['name' => 'Efes Fıçı', 'description' => '%4,8 Alkollü 40cl.', 'price' => 120],
                                    ['name' => "Beck's Fıçı", 'description' => '%5 Alkollü 40cl.', 'price' => 130],
                                ]
                            ],
                            'Şişe Bira' => [
                                'products' => [
                                    ['name' => ['Efes Pilsen', 'Efes Malt', 'Efes Green'], 'description' => '50cl seçenekleri.', 'price' => 140],
                                    ['name' => 'Bomonti Filtresiz', 'description' => '50cl.', 'price' => 160],
                                    ['name' => ["Beck's", 'Bud'], 'description' => '50cl.', 'price' => 150],
                                    ['name' => ['Miller', 'Corona', 'Heineken'], 'description' => '33cl-35,5cl seçenekleri.', 'price' => 180],
                                    ['name' => ['Amsterdam', 'Duvel'], 'description' => 'Yüksek alkollü seçenekler.', 'price' => 220],
                                    ['name' => 'Efes Glutensiz', 'description' => 'Karabuğday ve arpa maltlı.', 'price' => 170],
                                    ['name' => ['Hoegaarden', 'Erdinger'], 'description' => 'Buğday biraları.', 'price' => 200],
                                    ['name' => ['Belfast', 'Stella Artois'], 'description' => 'Premium Lager seçenekleri.', 'price' => 190],
                                ]
                            ]
                        ]
                    ],
                    'Kokteyl' => [
                        'children' => [
                            'Crash Kokteyl' => [
                                'products' => [
                                    ['name' => 'Kuzu Tonik', 'description' => 'Beefeater, kuzukulağı cordial, tonik, limon suyu.', 'price' => 320],
                                    ['name' => 'Apple Pie', 'description' => 'Jameson, kırmızı elma, tarçın.', 'price' => 320],
                                    ['name' => 'Ebem Ekşisi', 'description' => 'Absolut Votka, kuzukulağı, yeşil elma, portakal.', 'price' => 320],
                                    ['name' => 'Arnavut', 'description' => 'Olmeca Silver, triple sec, çarkıfelek meyvesi, portakal, Arnavut biberi.', 'price' => 320],
                                    ['name' => 'Madagascar', 'description' => 'Havana Club 3, Malibu, beyaz şeftali, vanilya, portakal.', 'price' => 320],
                                    ['name' => 'Aperolli', 'description' => 'Beefeater Cin, Aperol, narenciye karışımı.', 'price' => 320],
                                    ['name' => 'Crash Lemonade', 'description' => 'Absolut Votka, damla sakızı likörü, melisa, yeşil elma, portakal.', 'price' => 320],
                                    ['name' => 'Bergamot', 'description' => 'Beefeater Cin, Rosso Vermut, bergamot, yaban mersini, portakal.', 'price' => 320],
                                ]
                            ],
                            'Klasik Kokteyl' => [
                                'products' => [
                                    ['name' => ['Cin Tonik', 'Mojito', 'Margarita', 'Cosmopolitan'], 'price' => 300],
                                    ['name' => ['Long Island Iced Tea', 'Cuba Libre', 'Negroni', 'Caipirinha'], 'price' => 340],
                                    ['name' => ['Jagerita', 'Cin Fizz', 'Espresso Martini', 'Lynchburg Lemonade'], 'price' => 320],
                                    ['name' => ['Old Fashioned', 'Whiskey Sour', 'Carrie'], 'price' => 360],
                                ]
                            ]
                        ]
                    ],
                    'Shot' => [
                        'children' => [
                            'Karışım' => [
                                'products' => [
                                    ['name' => ['Winx', 'Dark Rom Archers', 'Cumshot', 'Jameson Archers', 'B-52', 'Baby Stout'], 'price' => 120],
                                    ['name' => ['Kiraz Votka', 'Cin Elma'], 'description' => '(Shaker ile servis edilenler).', 'price' => 150],
                                ]
                            ],
                            'Tekila' => [
                                'products' => [
                                    ['name' => ['Olmeca Silver', 'Olmeca Gold', 'Olmeca Altos', 'Avion Silver'], 'price' => 140],
                                ]
                            ],
                            'Viski' => [
                                'products' => [
                                    ['name' => ['Jameson', "Jack Daniel's", 'Chivas Regal'], 'description' => '(Shot servis)', 'price' => 160],
                                ]
                            ],
                            'Votka' => [
                                'products' => [
                                    ['name' => ['Absolut Blue', 'Absolut Aromalı'], 'price' => 130],
                                ]
                            ],
                            'Cin' => [
                                'products' => [
                                    ['name' => ['Beefeater', 'Beefeater Pink', 'Malfy serisi'], 'price' => 130],
                                ]
                            ],
                            'Rom' => [
                                'products' => [
                                    ['name' => ['Havana Club', 'Bumbu'], 'price' => 160],
                                ]
                            ],
                            'Likör' => [
                                'products' => [
                                    ['name' => ['Jägermeister', 'Kahlua', 'Malibu', 'Baileys'], 'price' => 120],
                                ]
                            ]
                        ]
                    ],
                    'Kadeh' => [
                        'name_override' => 'Kadeh (Sek veya Buzlu Servis)',
                        'children' => [
                            'Tekila' => [
                                'products' => [
                                    ['name' => ['Olmeca Silver', 'Olmeca Gold', 'Olmeca Dark Chocolate', 'Olmeca Altos', 'Ojo De Tigre (Mezcal)'], 'price' => 240],
                                ]
                            ],
                            'Viski' => [
                                'products' => [
                                    ['name' => ['Jameson', 'Jameson Black Barrel', 'Jameson Caskmates'], 'price' => 260],
                                    ['name' => ['Chivas 12', 'Chivas 13', 'Chivas 15', 'Chivas 18'], 'price' => 320],
                                    ['name' => ["Jack Daniel's Apple", "Jack Daniel's Fire", "Jack Daniel's Honey"], 'price' => 280],
                                    ['name' => ['Glenlivet', 'Ballantines', 'Lot 40', 'The Deacon'], 'price' => 300],
                                ]
                            ],
                            'Votka' => [
                                'products' => [
                                    ['name' => 'Absolut (Tüm Çeşitler)', 'price' => 220],
                                ]
                            ],
                            'Cin' => [
                                'products' => [
                                    ['name' => ['Beefeater', 'Beefeater Pink'], 'price' => 220],
                                    ['name' => ['Malfy Original', 'Malfy Lemon', 'Malfy Gin Rosa', 'Malfy Arancia'], 'price' => 260],
                                ]
                            ],
                            'Rom' => [
                                'products' => [
                                    ['name' => ['Havana Club 3', 'Havana Club 7', 'Bumbu'], 'price' => 280],
                                ]
                            ],
                            'Likör' => [
                                'products' => [
                                    ['name' => ['Jägermeister', 'Kahlua', 'Malibu', 'Baileys', 'Chambord'], 'price' => 240],
                                ]
                            ],
                            'Şarap' => [
                                'products' => [
                                    ['name' => 'Suvla Bigalı', 'description' => 'Rose, Kırmızı, Beyaz kadeh seçenekleri.', 'price' => 180],
                                ]
                            ]
                        ]
                    ],
                    'Alkolsüz' => [
                        'children' => [
                            'Meşrubat' => [
                                'products' => [
                                    ['name' => ['Kola', 'Sprite', 'Soda', 'Su'], 'price' => 45],
                                    ['name' => 'Red Bull', 'price' => 110],
                                    ['name' => 'Churchill', 'description' => 'Taze limon suyu, tuz, soda.', 'price' => 60],
                                    ['name' => 'Alkolsüz Meyve Kokteyli', 'description' => 'Tatlı, ekşi veya dengeli.', 'price' => 150],
                                    ['name' => 'Kuzukulağı Gazozu', 'description' => 'Kuzukulağı cordial, soda.', 'price' => 120],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // 3. Process the Tree
        $this->processMenuTree($menuTree);
    }

    private function processMenuTree(array $tree, ?int $parentId = null, string $defaultType = 'food')
    {
        foreach ($tree as $name => $data) {
            $categoryName = $data['name_override'] ?? $name;
            $type = $data['type'] ?? $defaultType;
            $slug = \Illuminate\Support\Str::slug($categoryName);

            // Check for existing image for category (thumbnails)
            $catImagePath = null;
            if (Storage::disk('public')->exists("categories/{$slug}.webp")) {
                $catImagePath = "categories/{$slug}.webp";
            }

            // Create Category
            $category = \App\Models\Category::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $categoryName,
                    'parent_id' => $parentId,
                    'type' => $type,
                    'sort_order' => 0,
                ]
            );

            // Create Products
            if (isset($data['products'])) {
                foreach ($data['products'] as $productData) {
                    $names = is_array($productData['name']) ? $productData['name'] : [$productData['name']];
                    
                    foreach ($names as $productName) {
                        $productSlug = Str::slug($productName);
                        $imagePath = null;
                        if (Storage::disk('public')->exists("products/thumbnails/{$productSlug}.webp")) {
                            $imagePath = "products/thumbnails/{$productSlug}.webp";
                        }

                        $gallery = [];
                        // Check for gallery images (slug-gallery-1.webp, etc.)
                        for ($i = 1; $i <= 5; $i++) {
                             // This is a simple pattern, we might need a better one for exact timestamps, 
                             // but for seeding, a simple slug match is easier to maintain manually
                             $files = Storage::disk('public')->files("products/gallery");
                             foreach($files as $file) {
                                 if (Str::startsWith(basename($file), $productSlug . '-gallery-')) {
                                     $gallery[] = $file;
                                 }
                             }
                             break; // Exit after finding pattern files
                        }

                        $product = \App\Models\Product::firstOrCreate(
                            ['name' => $productName, 'category_id' => $category->id],
                            [
                                'description' => $productData['description'] ?? null,
                                'image_path' => $imagePath,
                                'gallery' => !empty($gallery) ? array_unique($gallery) : null,
                                'is_active' => true,
                                'sort_order' => 0
                            ]
                        );

                        // Attach to all stores if not already attached
                        foreach ($this->allStores as $store) {
                            $product->stores()->syncWithoutDetaching([
                                $store->id => [
                                    'is_active' => true,
                                    'is_featured' => false,
                                    'sort_order' => 0
                                ]
                            ]);

                            // Create default portion for each store
                            \App\Models\StoreProductPortion::firstOrCreate(
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

            // Process Children Recursive
            if (isset($data['children'])) {
                $this->processMenuTree($data['children'], $category->id, $type);
            }
        }
    }
}
