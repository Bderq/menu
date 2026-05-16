<?php

namespace App\Filament\Widgets;

use App\Models\GoogleReviewInteraction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GoogleReviewStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';
    protected ?string $heading = 'Google Review Performans Analizi';
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalShows = GoogleReviewInteraction::count();
        
        $accepted = GoogleReviewInteraction::where('status', 'accepted')->count();
        $rejected = GoogleReviewInteraction::where('status', 'rejected')->count();
        $dismissed = GoogleReviewInteraction::where('status', 'dismissed')->count();
        
        $googleRedirected = GoogleReviewInteraction::where('google_redirected', true)->count();
        $feedbackSubmitted = GoogleReviewInteraction::where('feedback_submitted', true)->count();

        $acceptedRate = $totalShows > 0 ? round(($accepted / $totalShows) * 100, 1) : 0;
        $rejectedRate = $totalShows > 0 ? round(($rejected / $totalShows) * 100, 1) : 0;
        $dismissedRate = $totalShows > 0 ? round(($dismissed / $totalShows) * 100, 1) : 0;
        
        $googleRedirectRate = $accepted > 0 ? round(($googleRedirected / $accepted) * 100, 1) : 0;
        $feedbackRate = $rejected > 0 ? round(($feedbackSubmitted / $rejected) * 100, 1) : 0;

        return [
            Stat::make('Toplam Gösterim', $totalShows)
                ->description('Popup kaç kez açıldı')
                ->icon('heroicon-o-eye')
                ->color('gray'),

            Stat::make('Evet (Olumlu)', "{$accepted}")
                ->description("Seçenek: Evet (%{$acceptedRate})")
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Google\'a Yönlenme', $googleRedirected)
                ->description("Gerçek dönüşüm (%{$googleRedirectRate})")
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('success'),

            Stat::make('Hayır (Olumsuz)', "{$rejected}")
                ->description("Seçenek: Hayır (%{$rejectedRate})")
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Ses Ver\'e Yazılan', $feedbackSubmitted)
                ->description("Olumsuz dönüşüm (%{$feedbackRate})")
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('primary'),

            Stat::make('Kapatıldı / Pasif', "{$dismissed}")
                ->description("Etkileşimsiz (%{$dismissedRate})")
                ->icon('heroicon-o-minus-circle')
                ->color('warning'),
        ];
    }
}
