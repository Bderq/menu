<div
    x-data="{ isDropping: false, isUploading: false }"
    @dragover.prevent="isDropping = true"
    @dragleave.prevent="isDropping = false"
    @drop.prevent="
        isDropping = false;
        if ($event.dataTransfer.files.length > 0) {
            isUploading = true;
            $wire.upload('inline_thumbnail', $event.dataTransfer.files[0], (uploadedFilename) => {
                $wire.processThumbnailUpload('{{ $getRecord()->id }}', uploadedFilename).then(() => {
                    isUploading = false;
                });
            }, (error) => {
                isUploading = false;
            })
        }
    "
    :class="{'border-primary-500 bg-primary-500/10 scale-105 shadow-xl': isDropping, 'opacity-50': isUploading}"
    class="relative flex items-center justify-center border border-dashed border-gray-600 rounded-lg hover:border-primary-400 transition-all duration-300 cursor-pointer overflow-hidden group bg-gray-900/40"
    style="width: 80px; height: 80px; flex-shrink: 0;"
>
    <!-- Background Gradient Overlay (Hover) -->
    <div class="absolute inset-0 bg-gradient-to-tr from-primary-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

    <!-- Loading Spinner -->
    <div x-show="isUploading" class="absolute inset-0 flex items-center justify-center bg-gray-950/80 z-20 pointer-events-none" style="display: none;">
        <svg class="animate-spin h-6 w-6 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    @if ($getState())
        <img src="{{ Storage::url($getState()) }}" class="object-cover transition-transform duration-500 group-hover:scale-110 pointer-events-none" style="width: 100%; height: 100%; display: block;" />
        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm pointer-events-none">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        </div>
    @else
        <div class="flex flex-col items-center justify-center p-2 pointer-events-none opacity-50 group-hover:opacity-100 transition-opacity">
            <svg class="w-6 h-6 text-gray-400 group-hover:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        </div>
    @endif
</div>
