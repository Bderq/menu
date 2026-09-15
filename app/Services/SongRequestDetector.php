<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SongRequestDetector
{
    private const PROMPT = <<<'TXT'
Sen bir kafe/restoran QR menüsündeki "Ses Verin" misafir mesajlarını inceliyorsun.
Görevin: mesajın bir ŞARKI İSTEĞİ olup olmadığını belirlemek ve eğer öyleyse sanatçı ile şarkı adını çıkarmak.

Kurallar:
- Misafir açıkça bir şarkının/parçanın çalınmasını istiyorsa is_song_request = true.
- Sadece "müzik çok yüksek", "müzik güzel", "başka tür çalın" gibi genel yorumlar şarkı isteği DEĞİLDİR.
- Sanatçı veya şarkı adı mesajda geçmiyorsa ilgili alanı null bırak, uydurma.
- Yazım hatalarını düzelt (örn. "tarkan şımarık" -> artist: "Tarkan", title: "Şımarık").
- Şarkı isteği değilse artist ve title null olmalı.

Misafir mesajı:
"""
%s
"""
TXT;

    /**
     * @return array{is_song_request: bool, artist: ?string, title: ?string}|null
     */
    public function detect(string $message): ?array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model');

        if (!$apiKey) {
            Log::warning('Gemini API key is not configured; skipping song request detection.');
            return null;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [[
                        'parts' => [['text' => sprintf(self::PROMPT, $message)]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0,
                        'responseMimeType' => 'application/json',
                        'responseSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'is_song_request' => ['type' => 'boolean'],
                                'artist' => ['type' => 'string', 'nullable' => true],
                                'title' => ['type' => 'string', 'nullable' => true],
                            ],
                            'required' => ['is_song_request', 'artist', 'title'],
                        ],
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Gemini request failed: ' . $e->getMessage());
            return null;
        }

        if (!$response->successful()) {
            Log::warning('Gemini returned an error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        $parsed = is_string($text) ? json_decode($text, true) : null;

        if (!is_array($parsed) || !array_key_exists('is_song_request', $parsed)) {
            Log::warning('Gemini returned unparseable output', ['text' => $text]);
            return null;
        }

        return [
            'is_song_request' => (bool) $parsed['is_song_request'],
            'artist' => $this->cleanString($parsed['artist'] ?? null),
            'title' => $this->cleanString($parsed['title'] ?? null),
        ];
    }

    private function cleanString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
