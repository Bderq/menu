<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <x-filament::section>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">Anket Seçin</label>
                        <select wire:model.live="pollId" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700">
                            <option value="">Seçiniz...</option>
                            @foreach(\App\Models\Poll::all() as $poll)
                                <option value="{{ $poll->id }}">{{ $poll->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Şube Filtresi</label>
                        <select wire:model.live="storeId" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700">
                            <option value="">Tüm Şubeler</option>
                            @foreach(\App\Models\Store::all() as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
