<?php

namespace Database\Seeders;

use App\Models\Allergen;
use App\Models\DietType;
use Illuminate\Database\Seeder;

class DietAllergenSeeder extends Seeder
{
    public function run(): void
    {
        // Diet Types
        $dietTypes = [
            [
                'name' => 'Vegan',
                'icon' => 'heroicon-o-leaf',
                'color' => '#10B981',
            ],
            [
                'name' => 'Vejetaryen',
                'icon' => 'heroicon-o-leaf',
                'color' => '#34D399',
            ],
            [
                'name' => 'Glutensiz',
                'icon' => 'heroicon-o-variable',
                'color' => '#60A5FA',
            ],
        ];

        foreach ($dietTypes as $type) {
            DietType::updateOrCreate(['name' => $type['name']], $type);
        }

        // Allergens
        $allergens = [
            ['name' => 'Gluten', 'icon' => 'heroicon-o-variable', 'color' => '#EF4444'],
            ['name' => 'Yumurta', 'icon' => 'heroicon-o-circle-stack', 'color' => '#F59E0B'],
            ['name' => 'Deniz Ürünleri', 'icon' => 'heroicon-o-lifebuoy', 'color' => '#3B82F6'],
            ['name' => 'Yer Fıstığı', 'icon' => 'heroicon-o-ellipsis-horizontal-circle', 'color' => '#8B4513'],
            ['name' => 'Soya', 'icon' => 'heroicon-o-adjustments-horizontal', 'color' => '#10B981'],
            ['name' => 'Süt', 'icon' => 'heroicon-o-beaker', 'color' => '#60A5FA'],
            ['name' => 'Kuruyemiş', 'icon' => 'heroicon-o-circle-stack', 'color' => '#D97706'],
            ['name' => 'Kereviz', 'icon' => 'heroicon-o-sparkles', 'color' => '#10B981'],
            ['name' => 'Hardal', 'icon' => 'heroicon-o-fire', 'color' => '#FBBF24'],
            ['name' => 'Susam', 'icon' => 'heroicon-o-swatch', 'color' => '#FCD34D'],
            ['name' => 'Sülfat', 'icon' => 'heroicon-o-flask', 'color' => '#A855F7'],
            ['name' => 'Lupin', 'icon' => 'heroicon-o-sun', 'color' => '#EC4899'],
        ];

        foreach ($allergens as $allergen) {
            Allergen::updateOrCreate(['name' => $allergen['name']], $allergen);
        }
    }
}
