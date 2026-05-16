<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollSchedule extends Model
{
    protected $fillable = ['poll_id', 'starts_at', 'ends_at', 'days_of_week'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'days_of_week' => 'array',
    ];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }
}
