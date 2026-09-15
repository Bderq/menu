<?php

namespace App\Models;

use App\Services\TelegramService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SongRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_DISMISSED = 'dismissed';
    public const STATUS_NOT_FOUND = 'not_found';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'candidates' => 'array',
            'queued_at' => 'datetime',
        ];
    }

    public function guestMessage(): BelongsTo
    {
        return $this->belongsTo(GuestMessage::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function telegramText(): string
    {
        $e = fn (?string $s) => TelegramService::escape((string) $s);
        $wanted = trim(implode(' – ', array_filter([$this->artist, $this->title])));

        return "🎵 <b>{$e($this->store->name)}</b> - Şarkı İsteği\n\n"
            . "{$e($this->guestMessage->content)}\n\n"
            . $this->guestMessage->created_at->format('d.m.Y H:i')
            . ($wanted !== '' ? "\n\nAranan: <i>{$e($wanted)}</i>" : '');
    }

    public function telegramKeyboard(): array
    {
        if ($this->isConfidentMatch()) {
            $top = $this->candidates[0];
            $rows = [[[
                'text' => Str::limit("▶ Sıraya ekle: {$top['artist']} – {$top['name']}", 60),
                'callback_data' => "sq:{$this->id}:0",
            ]]];
        } else {
            $rows = collect($this->candidates)->map(fn (array $c, int $i) => [[
                'text' => Str::limit("▶ {$c['artist']} – {$c['name']}", 60),
                'callback_data' => "sq:{$this->id}:{$i}",
            ]])->all();
        }

        $rows[] = [['text' => '✖ Yok say', 'callback_data' => "sq:{$this->id}:x"]];

        return $rows;
    }

    public function isConfidentMatch(): bool
    {
        $top = $this->candidates[0] ?? null;

        if (!$top || !$this->artist || !$this->title) {
            return false;
        }

        $norm = fn (string $s) => trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($s, 'tr'))));

        return str_contains($norm($top['artist']), $norm($this->artist))
            && str_contains($norm($top['name']), $norm($this->title));
    }
}
