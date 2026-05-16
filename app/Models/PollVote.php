<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollVote extends Model
{
    protected $fillable = ['poll_id', 'poll_option_id', 'visitor_id', 'store_id'];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function option()
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
