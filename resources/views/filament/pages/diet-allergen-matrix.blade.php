<x-filament-panels::page>
    <div class="flex flex-col gap-y-6">
        {{-- Category Filter Bar --}}
        <div class="flex items-center justify-between !bg-gray-50 dark:!bg-black p-4 rounded-xl shadow-sm border border-gray-200 dark:border-white/10">
            <div class="flex items-center gap-x-6 w-full">
                <div class="flex items-center gap-x-3 max-w-md w-full">
                    <label for="category_select" class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest shrink-0">BÖLÜM:</label>
                    <select 
                        id="category_select"
                        wire:model.live="selectedCategoryId"
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 rounded-lg shadow-sm text-sm font-bold transition-all"
                    >
                        <option value="">Seçiniz...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->parent ? $category->parent->name . ' > ' : '' }}{{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-x-4 ml-auto">
                    <div wire:loading class="flex items-center gap-x-2 text-[10px] text-primary-600 font-black uppercase tracking-tighter">
                        <x-filament::loading-indicator class="w-3 h-3" />
                        <span>Senkronize ediliyor</span>
                    </div>
                    <div class="px-3 py-1 rounded-full bg-gray-200 dark:bg-white/10 text-[10px] text-gray-600 dark:text-gray-300 font-black uppercase tracking-widest border border-gray-300 dark:border-white/5">
                        {{ $products->count() }} ÜRÜN
                    </div>
                </div>
            </div>
        </div>

        {{-- Matrix Table --}}
        @if($selectedCategoryId)
        <div class="relative overflow-x-auto !bg-gray-50 dark:!bg-black rounded-xl border border-gray-200 dark:border-white/10 shadow-sm">
            <table class="w-full text-left border-collapse table-fixed min-w-[1200px]">
                <thead>
                    <tr class="divide-x divide-gray-200 dark:divide-white/10">
                        <th class="sticky left-0 z-20 !bg-gray-100 dark:!bg-black w-[250px] p-4 text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider border-b border-gray-200 dark:border-white/10 shadow-[2px_0_0_0_rgba(0,0,0,0.1)]">
                            Ürün İsmi
                        </th>
                        
                        {{-- Diet Type Columns --}}
                        @foreach($dietTypes as $dt)
                        <th 
                            style="background-color: {{ $dt->color }}; color: {{ $this->getTextColor($dt->color) }};"
                            class="w-[120px] p-2 text-[10px] text-center font-black uppercase border-b border-white/20 leading-tight whitespace-normal"
                        >
                            {{ $dt->name }}
                        </th>
                        @endforeach

                        {{-- Divider --}}
                        <th class="w-[12px] bg-gray-900 dark:bg-black border-b border-white/20"></th>

                        {{-- Allergen Columns --}}
                        @foreach($allergens as $al)
                        <th 
                            style="background-color: {{ $al->color }}; color: {{ $this->getTextColor($al->color) }};"
                            class="w-[120px] p-2 text-[10px] text-center font-black uppercase border-b border-white/20 leading-tight whitespace-normal"
                        >
                            {{ $al->name }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-white/10 dark:divide-white/20">
                    @forelse($products as $product)
                    <tr class="group hover:bg-primary-50 dark:hover:bg-white/5 transition-colors divide-x divide-gray-100 dark:divide-white/5">
                        <td class="sticky left-0 z-10 !bg-gray-50 dark:!bg-black group-hover:bg-gray-100 dark:group-hover:bg-gray-900 p-4 transition-colors border-r border-gray-200 dark:border-white/10 shadow-[4px_0_0_0_rgba(0,0,0,0.1)]">
                            <span class="text-xs font-black text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 uppercase tracking-tight leading-relaxed">
                                {{ $product->name }}
                            </span>
                        </td>

                        {{-- Diet Type Cells --}}
                        @foreach($dietTypes as $dt)
                        <td 
                            wire:click="toggleTag({{ $product->id }}, 'diet', {{ $dt->id }})"
                            class="p-0 cursor-pointer relative"
                        >
                            @php $isActive = isset($dietMap[$product->id][$dt->id]); @endphp
                            <div 
                                class="w-full h-[50px] flex items-center justify-center transition-all duration-150
                                {{ $isActive ? '' : 'hover:bg-gray-50 dark:hover:bg-white/5' }}"
                                style="{{ $isActive ? 'background-color: ' . $dt->color . ';' : '' }}"
                            >
                                @if($isActive)
                                    <x-heroicon-o-check class="w-5 h-5 flex-shrink-0" style="width: 20px !important; height: 20px !important; color: {{ $this->getTextColor($dt->color) }}" stroke-width="3" />
                                @endif
                            </div>
                        </td>
                        @endforeach

                        {{-- Divider --}}
                        <td class="bg-gray-100 dark:bg-gray-800/50"></td>

                        {{-- Allergen Cells --}}
                        @foreach($allergens as $al)
                        <td 
                            wire:click="toggleTag({{ $product->id }}, 'allergen', {{ $al->id }})"
                            class="p-0 cursor-pointer relative"
                        >
                            @php $isActive = isset($allergenMap[$product->id][$al->id]); @endphp
                            <div 
                                class="w-full h-[50px] flex items-center justify-center transition-all duration-150
                                {{ $isActive ? '' : 'hover:bg-gray-100 dark:hover:bg-white/5' }}"
                                style="{{ $isActive ? 'background-color: ' . $al->color . ';' : '' }}"
                            >
                                @if($isActive)
                                    <x-heroicon-o-check class="w-5 h-5 flex-shrink-0" style="width: 20px !important; height: 20px !important; color: {{ $this->getTextColor($al->color) }}" stroke-width="3" />
                                @endif
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($dietTypes) + count($allergens) + 2 }}" class="p-12 text-center text-gray-400 italic">
                            Bu kategoride henüz ürün bulunmuyor.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center justify-center p-20 bg-white dark:bg-gray-900 rounded-xl border border-dashed border-gray-300 dark:border-white/20">
            <x-heroicon-o-magnifying-glass class="w-12 h-12 text-gray-300 mb-4" />
            <p class="text-gray-500 dark:text-gray-400">Yönetmeye başlamak için lütfen üstteki menüden bir kategori seçin.</p>
        </div>
        @endif
    </div>

    <style>
        /* Custom scrollbar to make it look premium */
        .overflow-x-auto::-webkit-scrollbar { height: 8px; }
        .overflow-x-auto::-webkit-scrollbar-track { background: transparent; }
        .overflow-x-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .overflow-x-auto::-webkit-scrollbar-thumb { background: #334155; }
        
        /* Fixed header and sticky column helpers */
        table { border-spacing: 0; }
        th.sticky, td.sticky { position: sticky !important; left: 0 !important; z-index: 10; transition: background-color 0.2s; }
        
        /* Row and Column separators for better scanning */
        tbody tr { border-bottom: 2px solid rgba(255, 255, 255, 0.1); }
        .dark tbody tr { border-bottom: 2px solid rgba(255, 255, 255, 0.15); }
        
        /* Vertical separators */
        tbody td, thead th { border-right: 2px solid rgba(255, 255, 255, 0.05); }
        .dark tbody td, .dark thead th { border-right: 2px solid rgba(255, 255, 255, 0.1); }

        /* DARK MODE FORCE - Ultimate fallback for CSS conflicts */
        .dark .dark\:bg-black, 
        .dark .dark\:bg-gray-900,
        .dark .dark\:bg-zinc-950 {
            background-color: #000 !important;
        }

        .dark th.sticky, 
        .dark td.sticky {
            background-color: #000 !important;
            color: #fff !important;
            border-color: rgba(255,255,255,0.1) !important;
        }

        .dark select,
        .dark .bg-white,
        .dark .bg-gray-50 {
            background-color: #09090b !important;
            color: #fff !important;
            border-color: rgba(255,255,255,0.1) !important;
        }

        /* Category dropdown fix */
        body.dark select#category_select {
            background-color: #111 !important;
            color: #fff !important;
        }
    </style>
</x-filament-panels::page>
