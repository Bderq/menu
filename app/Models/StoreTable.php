<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StoreTable extends Model
{
    protected $table = 'store_tables';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (StoreTable $table) {
            $table->qr_token ??= (string) Str::uuid();
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class, 'table_id');
    }

    public function menuUrl(): string
    {
        return route('menu.index', ['store_slug' => $this->store->slug, 'masa' => $this->qr_token]);
    }
}
