<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = ['visitor_id', 'product_id'];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
