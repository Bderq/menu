<x-filament-widgets::widget>
    <x-filament::section>
        @php $results = $this->getResults(); @endphp

        @if($results)
            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-bold">{{ $results['poll_title'] }}</h3>
                    <p class="text-sm text-gray-500">{{ $results['poll_question'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Toplam Oy: {{ $results['total_votes'] }}</p>
                </div>

                <div class="space-y-3">
                    @foreach($results['options'] as $option)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>{{ $option['emoji'] }} {{ $option['text'] }}</span>
                                <span class="font-medium">{{ $option['percentage'] }}% ({{ $option['count'] }})</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div class="bg-primary-600 h-2.5 rounded-full" style="width: {{ $option['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-10 text-gray-500">
                <p>Lütfen sonuçlarını görmek istediğiniz anketi seçin.</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
