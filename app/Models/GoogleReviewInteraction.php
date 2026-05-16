<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleReviewInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'store_id',
        'status',
        'google_redirected',
        'feedback_submitted',
        'guest_message_id',
        'showed_at',
        'responded_at',
    ];

    protected $casts = [
        'google_redirected' => 'boolean',
        'feedback_submitted' => 'boolean',
        'showed_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function guestMessage(): BelongsTo
    {
        return $this->belongsTo(GuestMessage::class, 'guest_message_id');
    }
}
