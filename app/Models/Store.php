<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends Model
{
    protected $guarded = [];

    // The products available in this store (via pivot)
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'store_products')
                    ->withPivot(['custom_name', 'custom_description', 'custom_image_path', 'is_active', 'is_featured'])
                    ->withTimestamps();
    }

    public function portions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StoreProductPortion::class);
    }

    public function campaigns(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_store')
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
