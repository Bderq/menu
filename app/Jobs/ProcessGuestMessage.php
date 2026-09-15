<?php

namespace App\Jobs;

use App\Models\GuestMessage;
use App\Models\SongRequest;
use App\Services\SongRequestDetector;
use App\Services\SpotifyService;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessGuestMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(public GuestMessage $guestMessage)
    {
    }

    public function handle(SongRequestDetector $detector, SpotifyService $spotify, TelegramService $telegram): void
    {
        $message = $this->guestMessage;
        $store = $message->store;

        $request = $message->songRequest;

        if ($request?->telegram_message_id) {
            return;
        }

        if (!$request) {
            $detection = $detector->detect($message->content);

            if (!$detection || !$detection['is_song_request'] || !$store->hasSpotify()) {
                $telegram->sendMessage($this->plainText($message, $detection['is_song_request'] ?? false));
                return;
            }

            $query = trim(($detection['title'] ?? '') . ' ' . ($detection['artist'] ?? ''));

            $request = SongRequest::create([
                'guest_message_id' => $message->id,
                'store_id' => $store->id,
                'artist' => $detection['artist'],
                'title' => $detection['title'],
                'candidates' => $spotify->searchTracks($store, $query !== '' ? $query : $message->content),
                'status' => SongRequest::STATUS_PENDING,
            ]);
        }

        $request->setRelation('store', $store)->setRelation('guestMessage', $message);

        if (empty($request->candidates)) {
            $messageId = $telegram->sendMessage($request->telegramText() . "\n\n⚠️ Spotify'da eşleşme bulunamadı.");
            $request->update(['status' => SongRequest::STATUS_NOT_FOUND, 'telegram_message_id' => $messageId]);
            return;
        }

        $messageId = $telegram->sendMessage(
            $request->telegramText() . ($request->isConfidentMatch() ? '' : "\n\nSıraya eklemek için seç:"),
            $request->telegramKeyboard()
        );

        $request->update(['telegram_message_id' => $messageId]);
    }

    private function plainText(GuestMessage $message, bool $songRequestWithoutSpotify): string
    {
        $e = fn (string $s) => TelegramService::escape($s);

        return "📣 <b>{$e($message->store->name)}</b> - Yeni Sesverin Mesajı\n\n"
            . "{$e($message->content)}\n\n"
            . $message->created_at->format('d.m.Y H:i')
            . ($songRequestWithoutSpotify ? "\n\n🎵 Şarkı isteği gibi görünüyor ama bu mağazada Spotify bağlı değil." : '');
    }
}
