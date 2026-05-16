<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollDisplayLog extends Model
{
    protected $table = 'poll_display_log';

    protected $fillable = ['poll_id', 'store_id', 'shown_date'];

    protected $casts = [
        'shown_date' => 'date',
    ];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
