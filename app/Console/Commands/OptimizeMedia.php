<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:optimize';

    protected $description = 'Resizes all uploaded images to max 800px width and converts them to WebP';

    public function handle()
    {
        $this->info('Starting media optimization... (Target: 800px width, WebP format)');

        // 1. Optimize Product Images
        $products = \App\Models\Product::all();
        $this->info("Found {$products->count()} products.");
        
        foreach ($products as $product) {
            $updated = false;

            // Thumbnail
            if ($product->image_path && $newPath = $this->optimizeFile($product->image_path)) {
                $product->image_path = $newPath;
                $updated = true;
            }

            // Gallery
            if (!empty($product->gallery) && is_array($product->gallery)) {
                $newGallery = [];
                $gUpdated = false;
                foreach ($product->gallery as $gPath) {
                    if ($newGPath = $this->optimizeFile($gPath)) {
                        $newGallery[] = $newGPath;
                        $gUpdated = true;
                    } else {
                        $newGallery[] = $gPath;
                    }
                }
                if ($gUpdated) {
                    $product->gallery = $newGallery;
                    $updated = true;
                }
            }

            if ($updated) {
                $product->save();
            }
        }

        // 2. Optimize Store Product Pivots (Custom images)
        $storeProducts = \Illuminate\Support\Facades\DB::table('store_products')->whereNotNull('custom_image_path')->get();
        $this->info("Found {$storeProducts->count()} custom store products images.");
        
        foreach ($storeProducts as $sp) {
            if ($newPath = $this->optimizeFile($sp->custom_image_path)) {
                \Illuminate\Support\Facades\DB::table('store_products')->where('id', $sp->id)->update(['custom_image_path' => $newPath]);
            }
        }

        // 3. Optimize Campaigns
        $campaigns = \App\Models\Campaign::all();
        $this->info("Found {$campaigns->count()} campaigns.");
        
        foreach ($campaigns as $campaign) {
            if ($campaign->image_path && $newPath = $this->optimizeFile($campaign->image_path)) {
                $campaign->image_path = $newPath;
                $campaign->save();
            }
        }

        $this->info('Media optimization completed successfully.');
    }

    private function optimizeFile($path)
    {
        if (empty($path) || str_starts_with($path, 'http')) {
            return null; // Ignore external or empty
        }

        $fullPath = storage_path('app/public/' . $path);
        
        if (!file_exists($fullPath)) {
            return null;
        }

        $info = @getimagesize($fullPath);
        if (!$info) return null;

        [$width, $height, $type] = $info;

        $isAlreadyOptimized = (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'webp' && $width <= 800);
        if ($isAlreadyOptimized) {
            return null; // Already webp and scaled
        }

        $image = null;
        switch ($type) {
            case IMAGETYPE_JPEG: 
                $image = @imagecreatefromjpeg($fullPath); 
                break;
            case IMAGETYPE_PNG:  
                $image = @imagecreatefrompng($fullPath); 
                // Preserve transparency
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case IMAGETYPE_WEBP: 
                $image = @imagecreatefromwebp($fullPath); 
                break;
        }

        if (!$image) return null;

        // Resize if wider than 800px
        if ($width > 800) {
            $newImage = imagescale($image, 800);
            if ($newImage) {
                imagedestroy($image);
                $image = $newImage;
            }
        }

        // Save as WebP
        $newPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $path);
        // If it was already webp but we resized it, path is same.
        if ($newPath === $path && $width <= 800) {
            imagedestroy($image);
            return null; 
        }

        $newFullPath = storage_path('app/public/' . $newPath);
        
        // Output as WebP with 80% quality
        imagewebp($image, $newFullPath, 80);
        imagedestroy($image);

        // Remove old file if format changed
        if ($newFullPath !== $fullPath && file_exists($fullPath)) {
            @unlink($fullPath);
        }

        return $newPath;
    }
}
