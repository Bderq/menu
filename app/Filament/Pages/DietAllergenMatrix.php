<?php

namespace App\Filament\Pages;

use App\Models\Allergen;
use App\Models\Category;
use App\Models\DietType;
use App\Models\Product;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Illuminate\Support\Collection;

class DietAllergenMatrix extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?string $navigationLabel = 'Diyet & Alerjen Matrix';

    protected static ?string $title = 'Diyet & Alerjen Matrix';

    protected string $view = 'filament.pages.diet-allergen-matrix';

    public ?int $selectedCategoryId = null;

    public function mount(): void
    {
        // Default to the first leaf category
        $first = Category::whereNotNull('parent_id')->orderBy('name')->first();
        $this->selectedCategoryId = $first?->id;
    }

    public function getCategories(): Collection
    {
        return Category::whereNotNull('parent_id')
            ->with('parent')
            ->orderBy('name')
            ->get();
    }

    public function getDietTypes(): Collection
    {
        return DietType::orderBy('name')->get();
    }

    public function getAllergens(): Collection
    {
        return Allergen::orderBy('name')->get();
    }

    public function getProducts(): Collection
    {
        if (!$this->selectedCategoryId) {
            return collect();
        }

        // Recursive search for products in subcategories
        $allCategoryIds = $this->getAllChildCategoryIds($this->selectedCategoryId);

        return Product::whereIn('category_id', $allCategoryIds)
            ->with(['dietTypes', 'allergens'])
            ->orderBy('name')
            ->get();
    }

    private function getAllChildCategoryIds($parentId): array
    {
        $ids = [(int) $parentId];
        $children = Category::where('parent_id', $parentId)->pluck('id')->toArray();
        
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->getAllChildCategoryIds($childId));
        }
        
        return array_unique($ids);
    }

    /**
     * Returns white (#fff) for dark backgrounds, black (#000) for light — ensures readability.
     */
    public function getTextColor(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return '#000000';
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // sRGB linear luminance
        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        return $luminance < 0.4 ? '#ffffff' : '#000000';
    }

    public function toggleTag(int $productId, string $type, int $tagId): void
    {
        $product = Product::findOrFail($productId);

        if ($type === 'diet') {
            if ($product->dietTypes()->where('diet_type_id', $tagId)->exists()) {
                $product->dietTypes()->detach($tagId);
            } else {
                $product->dietTypes()->attach($tagId);
            }
        } elseif ($type === 'allergen') {
            if ($product->allergens()->where('allergen_id', $tagId)->exists()) {
                $product->allergens()->detach($tagId);
            } else {
                $product->allergens()->attach($tagId);
            }
        }

        // Refresh the current product's relations in session
        // Livewire will re-render via the updated selectedCategoryId trigger
    }

    public function getViewData(): array
    {
        $products   = $this->getProducts();
        $dietTypes  = $this->getDietTypes();
        $allergens  = $this->getAllergens();
        $categories = $this->getCategories();

        // Build lookup maps: product_id => [tag_id => true]
        $dietMap     = [];
        $allergenMap = [];

        foreach ($products as $product) {
            $dietMap[$product->id]     = $product->dietTypes->pluck('id')->flip()->toArray();
            $allergenMap[$product->id] = $product->allergens->pluck('id')->flip()->toArray();
        }

        return compact('products', 'dietTypes', 'allergens', 'categories', 'dietMap', 'allergenMap');
    }
}
