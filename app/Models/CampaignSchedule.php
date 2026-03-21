<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignSchedule extends Model
{
    protected $fillable = [
        'campaign_id',
        'days',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'days' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
