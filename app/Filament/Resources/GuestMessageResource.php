<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestMessageResource\Pages;
use App\Models\GuestMessage;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use BackedEnum;

class GuestMessageResource extends Resource
{
    protected static ?string $model = GuestMessage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Müşteri Sesleri';
    protected static ?string $modelLabel = 'Mesaj';
    protected static ?string $pluralModelLabel = 'Mesajlar';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store.name')
                    ->label('Şube')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('content')
                    ->label('Mesaj')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('ip_address')
                    ->label('IP Adresi')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_read')
                    ->label('Okundu')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('store_id')
                    ->label('Şube')
                    ->relationship('store', 'name'),
                
                TernaryFilter::make('is_read')
                    ->label('Okuma Durumu')
                    ->placeholder('Tümü')
                    ->trueLabel('Okunmuş')
                    ->falseLabel('Okunmamış'),
            ])
            ->actions([
                Action::make('markAsRead')
                    ->label('Okundu İşaretle')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->hidden(fn (GuestMessage $record): bool => $record->is_read)
                    ->action(function (GuestMessage $record) {
                        $record->update([
                            'is_read' => true,
                            'read_at' => Carbon::now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuestMessages::route('/'),
        ];
    }
}
