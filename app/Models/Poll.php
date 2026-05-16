<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = [
        'store_id', 'title', 'question', 'type', 'status', 'show_once'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function schedules()
    {
        return $this->hasMany(PollSchedule::class);
    }

    public function options()
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order');
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }

    public function impressions()
    {
        return $this->hasMany(PollImpression::class);
    }

    public function scopeActiveNow($query, $storeId = null)
    {
        return $query->where('status', 'active')
            ->where(function ($q) use ($storeId) {
                $q->whereNull('store_id')
                    ->when($storeId, fn($q) => $q->orWhere('store_id', $storeId));
            })
            ->whereHas('schedules', function ($q) {
                $now = now();
                $q->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now)
                    ->where(function ($sub) use ($now) {
                        $sub->whereNull('days_of_week')
                            ->orWhereJsonContains('days_of_week', $now->dayOfWeek);
                    });
            });
    }
}
