<?php

namespace App\Services;

class MenuService
{
    protected $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    public function getFormattedMenuData(\App\Models\Store $store)
    {
        $mainCategories = \App\Models\Category::whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
        
        $menuData = [];

        foreach ($mainCategories as $mainCat) {
            $groups = $mainCat->children()
                ->with(['children.products' => function($query) use ($store) {
                    $query->whereHas('stores', function($q) use ($store) {
                        $q->where('store_id', $store->id)->where('is_active', true);
                    })->with(['portions' => function($q) use ($store) {
                        $q->where('store_id', $store->id)->where('is_active', true)->orderBy('sort_order');
                    }])->orderBy('sort_order');
                }])
                ->get();
            
            $formattedGroups = [];
            
            if ($mainCat->type === \App\Enums\CategoryType::CAMPAIGN) {
                // CAMPAIGN GALLERY LOGIC
                $activeCampaigns = $this->campaignService->getActiveCampaignsForStore($store->id);
                
                foreach ($activeCampaigns as $campaign) {
                    $campaignItems = [];
                    foreach ($campaign->items as $targetItem) {
                        $p = $targetItem->product;
                        if (!$p) continue;

                        $sp = $p->stores()->where('store_id', $store->id)->first();
                        if (!$sp) continue;

                        $formatted = $this->formatProduct($p, $sp->pivot, $store->id);
                        $this->applySpecificCampaignToProduct($formatted, $campaign, $targetItem);
                        $campaignItems[] = $formatted;
                    }

                    $formattedGroups[] = [
                        'id' => 'camp-' . $campaign->id,
                        'name' => $campaign->display_title,
                        'description' => $campaign->description,
                        'image' => $campaign->image_path ? (str_starts_with($campaign->image_path, 'http') ? $campaign->image_path : '/storage/' . $campaign->image_path) : null,
                        'type' => $campaign->type,
                        'value' => $campaign->value,
                        'subcategories' => [
                            [
                                'id' => 'cp-items-' . $campaign->id,
                                'name' => 'Dahil Olan Ürünler',
                                'items' => $campaignItems
                            ]
                        ]
                    ];
                }
            } else {
                foreach ($groups as $group) {
                    $subcategories = [];
                    foreach ($group->children as $sub) {
                        $items = [];
                        foreach ($sub->products as $product) {
                            $sp = $product->stores->find($store->id);
                            if ($sp) {
                                $items[] = $this->formatProduct($product, $sp->pivot, $store->id);
                            }
                        }

                        if (count($items) > 0) {
                            $subcategories[] = [
                                'id' => $sub->id,
                                'name' => $sub->name,
                                'items' => $items
                            ];
                        }
                    }

                    if (count($subcategories) > 0) {
                        $formattedGroups[] = [
                            'id' => $group->slug,
                            'name' => $group->name,
                            'subcategories' => $subcategories
                        ];
                    }
                }
            }

            // BEST SELLERS LOGIC
            if ($mainCat->type !== \App\Enums\CategoryType::CAMPAIGN) {
                $groupIds = $mainCat->children()->pluck('id'); 
                $subCategoryIds = \App\Models\Category::whereIn('parent_id', $groupIds)->pluck('id');

                $featuredProducts = \App\Models\Product::whereIn('category_id', $subCategoryIds)
                    ->whereHas('stores', function($q) use ($store) {
                        $q->where('store_id', $store->id)
                          ->where('is_featured', true)
                          ->where('is_active', true);
                    })
                    ->with(['stores' => function($q) use ($store) {
                         $q->where('store_id', $store->id)->withPivot(['custom_name', 'custom_description', 'custom_image_path', 'is_active', 'is_featured', 'sort_order']);
                    }, 'portions' => function($q) use ($store) {
                         $q->where('store_id', $store->id)->where('is_active', true)->orderBy('sort_order');
                    }])
                    ->get()
                    ->sortBy(function($product) use ($store) {
                        return $product->stores->find($store->id)->pivot->sort_order;
                    });

                $featuredItems = [];
                foreach ($featuredProducts as $product) {
                    $pivot = $product->stores->find($store->id)->pivot;
                    $featuredItems[] = $this->formatProduct($product, $pivot, $store->id);
                }

                if (count($featuredItems) > 0) {
                    array_unshift($formattedGroups, [
                        'id' => 'best-sellers-' . $mainCat->slug,
                        'name' => '⭐ En Çok Satanlar',
                        'subcategories' => [
                            [
                                'id' => 'best-sellers-sub-' . $mainCat->slug,
                                'name' => 'Favoriler',
                                'items' => $featuredItems
                            ]
                        ]
                    ]);
                }
            }

            $key = strtolower($mainCat->type->value);
            $menuData[$key] = $formattedGroups;
        }

        // APPLY ACTIVE CAMPAIGNS
        return $this->campaignService->applyCampaigns($menuData, $store->id);
    }

    private function formatProduct($product, $pivot, $storeId)
    {
        $name = $pivot->custom_name ?? $product->name;
        $description = $pivot->custom_description ?? $product->description;
        $image = $pivot->custom_image_path ?? $product->image_path;

        $portions = $product->relationLoaded('portions') 
            ? $product->portions 
            : \App\Models\StoreProductPortion::where('product_id', $product->id)
                ->where('store_id', $storeId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

        $options = null;
        $price = 0;

        if ($portions->count() > 1) {
            $options = $portions->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float)$p->price
            ])->toArray();
            $price = (float)$portions->first()->price;
        } elseif ($portions->count() === 1) {
            $price = (float)$portions->first()->price;
        }

        $tags = $product->tags ?? [];
        
        $badge = null;
        if (!empty($product->badges) && is_array($product->badges)) {
            $badge = $product->badges[0]['label'] ?? null;
        }

        $detailImage = null;
        if (!empty($product->gallery) && is_array($product->gallery) && count($product->gallery) > 0) {
            $rawDetail = $product->gallery[0];
            $detailImage = !str_starts_with($rawDetail, 'http') ? '/storage/' . $rawDetail : $rawDetail;
        }

        return [
            'id' => $product->id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'image' => $image && !str_starts_with($image, 'http') ? '/storage/' . $image : $image,
            'detail_image' => $detailImage,
            'options' => $options,
            'tags' => $tags,
            'badge' => $badge
        ];
    }

    private function applySpecificCampaignToProduct(&$product, $campaign, $targetItem)
    {
        $originalPrice = $product['price'];
        $newPrice = $originalPrice;

        if ($campaign->type === \App\Enums\CampaignType::FIXED_PRICE) {
            $newPrice = $targetItem->price_override ?? $campaign->value;
        } elseif ($campaign->type === \App\Enums\CampaignType::PERCENTAGE) {
            $discount = ($originalPrice * $campaign->value) / 100;
            $newPrice = $originalPrice - $discount;
        } elseif ($campaign->type === \App\Enums\CampaignType::BUNDLE) {
            $newPrice = $campaign->value;
        }

        $product['campaign_name'] = $campaign->display_title;
        $product['campaign_type'] = $campaign->type;

        if ($campaign->type !== \App\Enums\CampaignType::X_GET_Y && $campaign->type === \App\Enums\CampaignType::BUNDLE) {
             $product['campaign_price'] = $newPrice;
        } elseif ($campaign->type !== \App\Enums\CampaignType::X_GET_Y && $newPrice < $originalPrice) {
             $product['campaign_price'] = $newPrice;
        }

        if (!empty($product['options'])) {
            foreach ($product['options'] as &$option) {
                $optPrice = $option['price'];
                $optNew = $optPrice;

                if ($targetItem->store_product_portion_id && $option['id'] !== $targetItem->store_product_portion_id) {
                    continue;
                }

                if ($campaign->type === \App\Enums\CampaignType::FIXED_PRICE) {
                    $optNew = $targetItem->price_override ?? $campaign->value;
                } elseif ($campaign->type === \App\Enums\CampaignType::PERCENTAGE) {
                    $optNew = $optPrice - (($optPrice * $campaign->value) / 100);
                } elseif ($campaign->type === \App\Enums\CampaignType::BUNDLE) {
                    $optNew = $campaign->value;
                }

                $option['campaign_name'] = $campaign->display_title;
                if ($campaign->type !== \App\Enums\CampaignType::X_GET_Y && ($optNew < $optPrice || $campaign->type === \App\Enums\CampaignType::BUNDLE)) {
                    $option['campaign_price'] = $optNew;
                    $product['campaign_price'] = $optNew; 
                }
            }
        }
    }
}
