<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }
}
