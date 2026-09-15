<?php

namespace App\Services;

use App\Models\SongRequest;

class TelegramCallbackHandler
{
    public function __construct(
        private readonly SpotifyService $spotify,
        private readonly TelegramService $telegram,
    ) {
    }

    public function handle(array $callback): void
    {
        $callbackId = (string) $callback['id'];
        $user = trim(($callback['from']['first_name'] ?? '') . ' ' . ($callback['from']['last_name'] ?? '')) ?: 'Biri';

        if (!preg_match('/^sq:(\d+):(\d+|x)$/', (string) ($callback['data'] ?? ''), $m)) {
            $this->telegram->answerCallback($callbackId, '');
            return;
        }

        $songRequest = SongRequest::with(['store', 'guestMessage'])->find($m[1]);

        if (!$songRequest || !$songRequest->telegram_message_id) {
            $this->telegram->answerCallback($callbackId, 'İstek bulunamadı.');
            return;
        }

        // Atomic claim: only one concurrent press can move pending -> processing.
        $claimed = SongRequest::whereKey($songRequest->id)
            ->where('status', SongRequest::STATUS_PENDING)
            ->update(['status' => SongRequest::STATUS_PROCESSING]);

        if (!$claimed) {
            $this->telegram->answerCallback($callbackId, match ($songRequest->fresh()->status) {
                SongRequest::STATUS_QUEUED => 'Bu istek zaten sıraya eklendi.',
                SongRequest::STATUS_PROCESSING => 'Bu istek şu an başkası tarafından işleniyor.',
                default => 'Bu istek kapatılmış.',
            });
            return;
        }

        try {
            $this->process($songRequest, $m[2], $callbackId, $user);
        } finally {
            SongRequest::whereKey($songRequest->id)
                ->where('status', SongRequest::STATUS_PROCESSING)
                ->update(['status' => SongRequest::STATUS_PENDING]);
        }
    }

    private function process(SongRequest $songRequest, string $choice, string $callbackId, string $user): void
    {
        if ($choice === 'x') {
            $songRequest->update(['status' => SongRequest::STATUS_DISMISSED]);
            $this->telegram->editMessage(
                $songRequest->telegram_message_id,
                $songRequest->telegramText() . "\n\n✖ Yok sayıldı (" . TelegramService::escape($user) . ')'
            );
            $this->telegram->answerCallback($callbackId, 'Yok sayıldı.');
            return;
        }

        $candidate = $songRequest->candidates[(int) $choice] ?? null;

        if (!$candidate) {
            $this->telegram->answerCallback($callbackId, 'Geçersiz seçim.');
            return;
        }

        $result = $this->spotify->addToQueue($songRequest->store, $candidate['uri']);

        if (!$result['ok']) {
            $this->telegram->answerCallback($callbackId, match ($result['error']) {
                'no_device' => "Aktif Spotify cihazı yok — {$songRequest->store->name}'da müzik çalıyor mu?",
                'premium_required' => 'Bu Spotify hesabı Premium değil, sıraya ekleme yapılamıyor.',
                'unauthorized' => 'Spotify yetkisi eksik — mağazanın Spotify bağlantısını yenilemek gerekiyor.',
                default => 'Spotify hata verdi, tekrar dene.',
            }, alert: true);
            return;
        }

        $trackName = "{$candidate['artist']} – {$candidate['name']}";

        $songRequest->update([
            'status' => SongRequest::STATUS_QUEUED,
            'chosen_track_uri' => $candidate['uri'],
            'chosen_track_name' => $trackName,
            'queued_by' => $user,
            'queued_at' => now(),
        ]);

        $this->telegram->editMessage(
            $songRequest->telegram_message_id,
            $songRequest->telegramText()
                . "\n\n✅ Sıraya eklendi: <b>" . TelegramService::escape($trackName) . '</b>'
                . "\n" . TelegramService::escape($user) . ', ' . now()->format('H:i')
        );
        $this->telegram->answerCallback($callbackId, 'Sıraya eklendi ✅');
    }
}
