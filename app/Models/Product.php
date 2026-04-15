<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'gallery' => 'array',
        'badges' => 'array',
        'tags' => 'array',
    ];

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class);
    }

    public function dietTypes(): BelongsToMany
    {
        return $this->belongsToMany(DietType::class, 'diet_type_product');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_products')
                    ->withPivot(['custom_name', 'custom_description', 'custom_image_path', 'is_active', 'is_featured', 'sort_order'])
                    ->withTimestamps();
    }

    public function storeProducts(): HasMany
    {
        return $this->hasMany(StoreProduct::class);
    }

    public function portions(): HasMany
    {
        return $this->hasMany(StoreProductPortion::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
