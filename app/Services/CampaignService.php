<?php

namespace App\Services;

class CampaignService
{
    /**
     * Apply active campaigns to the menu structure.
     *
     * @param array $menuData flatten array or structured menu
     * @param int $storeId
     * @return array
     */
    public function applyCampaigns(array $menuData, int $storeId): array
    {
        // 1. Get Active Campaigns for this Store & Time
        $activeCampaigns = $this->getActiveCampaigns($storeId);

        if ($activeCampaigns->isEmpty()) {
            return $menuData;
        }

        // 2. Iterate and Apply
        // Note: menuData structure is typically ['category' => [groups...]]
        // We need to traverse deep.
        
        foreach ($menuData as $mainCategoryType => &$groups) {
            foreach ($groups as &$group) {
                $subcategories = $group['subcategories'] ?? [];
                foreach ($subcategories as &$sub) {
                    $items = &$sub['items'];
                    if (is_array($items)) {
                        foreach ($items as &$product) {
                            $this->applyRulesToProduct($product, $activeCampaigns);
                        }
                    }
                }
                // Update the modified subcategories back to group
                $group['subcategories'] = $subcategories;
            }
        }

        return $menuData;
    }

    public function getActiveCampaignsForStore(int $storeId)
    {
        return $this->getActiveCampaigns($storeId);
    }

    protected function getActiveCampaigns(int $storeId)
    {
        $now = now();
        $dayOfWeek = strtolower($now->format('l')); // monday, tuesday...
        $yesterday = strtolower($now->copy()->subDay()->format('l'));
        $time = $now->format('H:i:s');

        return \App\Models\Campaign::query()
            ->where('is_active', true)
            // 1. Date Range Check (DateTime)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            // 2. Store Check
            ->whereHas('stores', function ($q) use ($storeId) {
                $q->where('stores.id', $storeId)
                  ->where('campaign_store.is_active', true);
            })
            // 3. Schedule Check
            ->where(function ($q) use ($dayOfWeek, $yesterday, $time) {
                // Active 24/7 if no schedules
                $q->whereDoesntHave('schedules')
                  ->orWhereHas('schedules', function ($sq) use ($dayOfWeek, $yesterday, $time) {
                      $sq->where(function ($todayCheck) use ($dayOfWeek, $time) {
                          // Started TODAY (one of the days must match current day)
                          $todayCheck->whereJsonContains('days', $dayOfWeek)
                                     ->where(function ($timeCheck) use ($time) {
                                         $timeCheck->where(function ($normal) use ($time) {
                                             // Normal: 08:00 - 20:00 (now must be between)
                                             $normal->whereColumn('start_time', '<=', 'end_time')
                                                    ->where('start_time', '<=', $time)
                                                    ->where('end_time', '>=', $time);
                                         })->orWhere(function ($overnightStart) use ($time) {
                                             // Overnight: 22:00 - 04:00 (Started today at 22, now is 23)
                                             $overnightStart->whereColumn('start_time', '>', 'end_time')
                                                            ->where('start_time', '<=', $time);
                                         });
                                     });
                      })->orWhere(function ($yesterdayCheck) use ($yesterday, $time) {
                          // Started YESTERDAY and spills into today
                          $yesterdayCheck->whereJsonContains('days', $yesterday)
                                         ->whereColumn('start_time', '>', 'end_time')
                                         ->where('end_time', '>=', $time);
                      });
                  });
            })
            ->with(['items.product'])
            ->orderBy('priority', 'desc')
            ->get();
    }

    protected function applyRulesToProduct(&$product, $campaigns)
    {
        // Product structure: ['id', 'name', 'price', 'options' => [...]]
        
        foreach ($campaigns as $campaign) {
            // Check if campaign targets this product
            // logic: campaign->items contains product_id?
            
            $targetItem = $campaign->items->firstWhere('product_id', $product['id']);
            
            if (!$targetItem) {
                continue;
            }

            // FOUND A MATCH!
            $product['campaign_name'] = $campaign->display_title;
            $product['campaign_type'] = $campaign->type;

            // Case A: Specific Portion Target (e.g. ID matches)
            if ($targetItem->store_product_portion_id && !empty($product['options'])) {
                foreach ($product['options'] as &$option) {
                    if ($option['id'] === $targetItem->store_product_portion_id) {
                         $this->applyDiscountToOption($option, $campaign, $targetItem);
                    }
                }
            }
            // Case B: Whole Product Target (No specific portion ID specified)
            elseif (!$targetItem->store_product_portion_id) {
                 $this->applyDiscountToProduct($product, $campaign, $targetItem);
            }
        }
    }

    protected function applyDiscountToOption(&$option, $campaign, $item)
    {
        $originalPrice = $option['price'];
        $newPrice = $originalPrice;

        if ($campaign->type === \App\Enums\CampaignType::FIXED_PRICE) {
             $newPrice = $item->price_override ?? $campaign->value;
        } elseif ($campaign->type === \App\Enums\CampaignType::PERCENTAGE) {
             $discount = ($originalPrice * $campaign->value) / 100;
             $newPrice = $originalPrice - $discount;
        } elseif ($campaign->type === \App\Enums\CampaignType::BUNDLE) {
             $newPrice = $campaign->value;
        }

        // Set name and type for all matches
        $option['campaign_name'] = $campaign->display_title;
        $option['campaign_type'] = $campaign->type;

        // Apply price only if it's not 'x_get_y' and is cheaper/valid
        if ($campaign->type !== \App\Enums\CampaignType::X_GET_Y && ($newPrice < $originalPrice || $campaign->type === \App\Enums\CampaignType::BUNDLE)) {
            $option['campaign_price'] = $newPrice;
        }
    }

    protected function applyDiscountToProduct(&$product, $campaign, $item)
    {
        // If product has no options, it's a simple price
        if (empty($product['options'])) {
            $originalPrice = $product['price'];
            $newPrice = $originalPrice;

             if ($campaign->type === \App\Enums\CampaignType::FIXED_PRICE) {
                 $newPrice = $item->price_override ?? $campaign->value;
            } elseif ($campaign->type === \App\Enums\CampaignType::PERCENTAGE) {
                 $discount = ($originalPrice * $campaign->value) / 100;
                 $newPrice = $originalPrice - $discount;
            } elseif ($campaign->type === \App\Enums\CampaignType::BUNDLE) {
                 $newPrice = $campaign->value;
            }

            $product['campaign_name'] = $campaign->display_title;
            $product['campaign_type'] = $campaign->type;

            if ($campaign->type !== \App\Enums\CampaignType::X_GET_Y && ($newPrice < $originalPrice || $campaign->type === \App\Enums\CampaignType::BUNDLE)) {
                $product['campaign_price'] = $newPrice;
            }
        } else {
            // If product has options but campaign targets "ALL", apply to all options
             foreach ($product['options'] as &$option) {
                 $this->applyDiscountToOption($option, $campaign, $item);
             }
        }
    }
}
