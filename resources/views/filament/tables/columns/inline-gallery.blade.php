<div
    x-data="{ isDropping: false, isUploading: false }"
    @dragover.prevent="isDropping = true"
    @dragleave.prevent="isDropping = false"
    @drop.prevent="
        isDropping = false;
        if ($event.dataTransfer.files.length > 0) {
            isUploading = true;
            let files = Array.from($event.dataTransfer.files);
            $wire.uploadMultiple('inline_gallery_files', files, (uploadedFilenames) => {
                $wire.processGalleryUpload('{{ $getRecord()->id }}', uploadedFilenames).then(() => {
                    isUploading = false;
                });
            }, (error) => {
                isUploading = false;
            })
        }
    "
    :class="{'border-primary-500 bg-primary-500/10 scale-105 shadow-xl': isDropping, 'opacity-50': isUploading}"
    class="relative flex items-center justify-center border border-dashed border-gray-600 rounded-lg hover:border-primary-400 transition-all duration-300 cursor-pointer overflow-hidden p-2 gap-1.5 group bg-gray-900/40"
    style="width: 120px; height: 80px; flex-shrink: 0;"
>
    <!-- Background Gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-primary-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

    <!-- Loading Spinner -->
    <div x-show="isUploading" class="absolute inset-0 flex items-center justify-center bg-gray-950/80 z-20 pointer-events-none" style="display: none;">
        <svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    @php
        $gallery = $getState() ?? [];
        $displayImages = array_slice($gallery, 0, 3);
        $remaining = count($gallery) - count($displayImages);
    @endphp

    @if (count($displayImages) > 0)
        <div class="flex -space-x-2 group-hover:-space-x-1 transition-all duration-300 pointer-events-none">
            @foreach ($displayImages as $image)
                <img src="{{ Storage::url($image) }}" class="rounded-md object-cover border border-gray-900 shadow-md ring-1 ring-white/10" style="width: 40px; height: 40px; display: block;" />
            @endforeach
            @if ($remaining > 0)
                <div class="rounded-md bg-gray-900 flex items-center justify-center text-[10px] font-bold border border-gray-800 shadow-md text-primary-400" style="width: 40px; height: 40px;">
                    +{{ $remaining }}
                </div>
            @endif
        </div>
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm pointer-events-none">
             <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
        </div>
    @else
        <div class="flex items-center justify-center p-1 pointer-events-none opacity-50 group-hover:opacity-100 transition-opacity">
            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.587-1.587a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
    @endif
</div>
