<?php

namespace App\Filament\Resources\Polls\Pages;

use App\Filament\Resources\Polls\PollResource;
use Filament\Resources\Pages\Page;

class PollDashboard extends Page
{
    protected static string $resource = PollResource::class;

    protected string $view = 'filament.resources.polls.pages.poll-dashboard';

    public $pollId = null;
    public $storeId = null;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PollResultsWidget::class,
        ];
    }

    public function updated($property)
    {
        if (in_array($property, ['pollId', 'storeId'])) {
            $this->dispatch('filterUpdated', [
                'pollId' => $this->pollId,
                'storeId' => $this->storeId,
            ]);
        }
    }
}
