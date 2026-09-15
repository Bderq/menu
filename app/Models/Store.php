<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'spotify_client_secret' => 'encrypted',
            'spotify_refresh_token' => 'encrypted',
        ];
    }

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

    public function guestMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GuestMessage::class);
    }

    public function tables(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StoreTable::class);
    }

    public function hasSpotify(): bool
    {
        return $this->spotify_client_id && $this->spotify_client_secret && $this->spotify_refresh_token;
    }
}
