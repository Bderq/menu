<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Schemas\ProductInfolist;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Products\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // Handled via Repeater in ProductForm
        ];
    }

    public static function getNavigationItems(): array
    {
        $stores = \App\Models\Store::all();
        
        $items = [
            \Filament\Navigation\NavigationItem::make('Master Kütüphane')
                ->group('Ürün Yönetimi')
                ->icon('heroicon-o-archive-box')
                ->activeIcon('heroicon-s-archive-box')
                ->url(static::getUrl('index'))
                ->isActiveWhen(fn () => request()->routeIs(static::getRouteBaseName() . '.index')),
        ];

        foreach ($stores as $store) {
            $items[] = \Filament\Navigation\NavigationItem::make($store->name)
                ->group('Ürün Yönetimi')
                ->icon('heroicon-o-building-storefront')
                ->url(static::getUrl('store', ['store' => $store->id]))
                ->isActiveWhen(fn () => (request()->route('store') instanceof \App\Models\Store ? request()->route('store')->id : request()->route('store')) == $store->id);
        }

        return $items;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'store' => Pages\StoreProducts::route('/store/{store}'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
