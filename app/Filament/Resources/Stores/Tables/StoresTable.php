<?php

namespace App\Filament\Resources\Stores\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use App\Models\Store;
use App\Models\StoreProductPortion;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('logo_path')
                    ->searchable(),
                TextColumn::make('theme_color')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('cloneFrom')
                    ->label('Başka Şubeden Kopyala')
                    ->modalHeading('Şube Verilerini Kopyala/Klonla')
                    ->modalDescription('Seçeceğiniz şubedeki tüm ürünleri, fiyatları, porsiyonları ve kampanyaları bu şubeye aktarır.')
                    ->modalSubmitActionLabel('Aktarımı Başlat')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->form([
                        Select::make('source_store_id')
                            ->label('Kaynak Şube')
                            ->placeholder('Verilerin kopyalanacağı şubeyi seçin')
                            ->options(fn (Store $record) => Store::where('id', '!=', $record->id)->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Checkbox::make('clear_existing')
                            ->label('Hedef şubedeki mevcut ürünleri ve porsiyonları temizle')
                            ->helperText('Bu şubedeki eski ayarları silip kaynak şubenin birebir kopyasını oluşturmak için bunu seçili bırakın.')
                            ->default(true),
                    ])
                    ->action(function (Store $record, array $data): void {
                        $sourceStore = Store::with(['products', 'portions', 'campaigns.items', 'campaigns.schedules'])->find($data['source_store_id']);
                        
                        if (!$sourceStore) {
                            Notification::make()
                                ->title('Hata')
                                ->body('Kaynak şube bulunamadı.')
                                ->danger()
                                ->send();
                            return;
                        }

                        \Illuminate\Support\Facades\DB::transaction(function () use ($record, $sourceStore, $data) {
                            if ($data['clear_existing']) {
                                // Mevcut ilişkileri ve porsiyonları temizle
                                $record->products()->detach();
                                
                                // Şubeye özel oluşturulmuş eski kampanyaları ve öğelerini GÜVENLİ temizle
                                $oldCampaigns = $record->campaigns;
                                foreach ($oldCampaigns as $oldCamp) {
                                    $record->campaigns()->detach($oldCamp->id);
                                    
                                    // Eğer bu kampanya başka hiçbir şubeye bağlı değilse (yetim kalmışsa) sil
                                    if ($oldCamp->stores()->count() === 0) {
                                        $oldCamp->items()->delete();
                                        $oldCamp->schedules()->delete();
                                        $oldCamp->delete();
                                    }
                                }
                                $record->portions()->delete();
                            }

                            // 1. Ürünleri Klonla (pivot verileriyle birlikte)
                            foreach ($sourceStore->products as $product) {
                                $record->products()->attach($product->id, [
                                    'custom_name' => $product->pivot->custom_name,
                                    'custom_description' => $product->pivot->custom_description,
                                    'custom_image_path' => $product->pivot->custom_image_path,
                                    'is_active' => $product->pivot->is_active,
                                    'is_featured' => $product->pivot->is_featured,
                                    'sort_order' => $product->pivot->sort_order ?? 0,
                                ]);
                            }

                            // 2. Porsiyonları Klonla ve ID Haritası Çıkar
                            $portionMapping = []; // [old_id => new_id]
                            foreach ($sourceStore->portions as $portion) {
                                $newPortion = \App\Models\StoreProductPortion::create([
                                    'store_id' => $record->id,
                                    'product_id' => $portion->product_id,
                                    'name' => $portion->name,
                                    'price' => $portion->price,
                                    'is_active' => $portion->is_active,
                                    'sort_order' => $portion->sort_order,
                                ]);
                                
                                $portionMapping[$portion->id] = $newPortion->id;
                            }

                            // 3. Kampanyaları Kapsamlı Şekilde Klonla
                            foreach ($sourceStore->campaigns as $campaign) {
                                // Kampanyayı çoğalt
                                $newCampaign = $campaign->replicate();
                                $newCampaign->name = $campaign->name; // İsim aynı kalabilir veya istersek sonuna '(Kopya)' diyebiliriz
                                $newCampaign->save();

                                // Kampanya Öğelerini (Items) Klonla
                                foreach ($campaign->items as $item) {
                                    $newPortionId = null;
                                    if ($item->store_product_portion_id) {
                                        $newPortionId = $portionMapping[$item->store_product_portion_id] ?? null;
                                    }

                                    $newCampaign->items()->create([
                                        'product_id' => $item->product_id,
                                        'store_product_portion_id' => $newPortionId,
                                        'price_override' => $item->price_override,
                                        'is_optional' => $item->is_optional,
                                    ]);
                                }

                                // Kampanya Zamanlamalarını (Schedules) Klonla
                                foreach ($campaign->schedules as $schedule) {
                                    $newCampaign->schedules()->create([
                                        'days' => $schedule->days,
                                        'start_time' => $schedule->start_time,
                                        'end_time' => $schedule->end_time,
                                    ]);
                                }

                                // Yeni kampanyayı bu şubeye bağla
                                $record->campaigns()->attach($newCampaign->id, [
                                    'is_active' => $campaign->pivot->is_active,
                                ]);
                            }
                        });

                        Notification::make()
                            ->title('Kopyalama Tamamlandı')
                            ->body("{$sourceStore->name} şubesindeki veriler başarıyla bu şubeye aktarıldı.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
