<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    /** @use HasFactory<\Database\Factories\CampaignFactory> */
    protected $fillable = [
        'name',
        'display_title',
        'description',
        'image_path',
        'type',
        'value',
        'buy_qty',
        'get_qty',
        'priority',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'type' => \App\Enums\CampaignType::class,
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CampaignItem::class);
    }

    public function schedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CampaignSchedule::class);
    }

    public function stores(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'campaign_store')
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
