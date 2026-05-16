<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollImpression extends Model
{
    protected $fillable = ['poll_id', 'visitor_id', 'shown_at'];

    protected $casts = [
        'shown_at' => 'datetime',
    ];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }
}
