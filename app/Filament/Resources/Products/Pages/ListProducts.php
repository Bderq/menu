<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $categories = \App\Models\Category::withCount('products')->get();
        $tabs = ['all' => \Filament\Schemas\Components\Tabs\Tab::make('All Products')
            ->badge(\App\Models\Product::count())];

        foreach ($categories as $category) {
            $tabs[$category->slug] = \Filament\Schemas\Components\Tabs\Tab::make($category->name)
                ->badge($category->products_count)
                ->query(fn ($query) => $query->where('category_id', $category->id));
        }

        return $tabs;
    }
}
