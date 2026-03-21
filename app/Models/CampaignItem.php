<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignItem extends Model
{
    protected $fillable = [
        'campaign_id',
        'product_id',
        'store_product_portion_id',
        'price_override',
        'is_optional'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function storeProductPortion()
    {
        return $this->belongsTo(StoreProductPortion::class);
    }
}

