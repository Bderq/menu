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
            // Option A: Skip the 'campaign' gallery tab from global price overrides
            // This ensures products in specific campaign views show their own prices
            if ($mainCategoryType === 'campaign') continue;

            foreach ($groups as &$group) {
                if (!isset($group['subcategories'])) continue;
                
                foreach ($group['subcategories'] as &$sub) {
                    if (!isset($sub['items']) || !is_array($sub['items'])) continue;
                    
                    foreach ($sub['items'] as &$product) {
                        $this->applyRulesToProduct($product, $activeCampaigns);
                    }
                }
            }
        }

        return $menuData;
    }

    public function getActiveCampaignsForStore(int $storeId)
    {
        return $this->getActiveCampaigns($storeId);
    }

    /**
     * Get all campaigns that could potentially run in this store (ignoring current time schedule).
     */
    public function getAvailableCampaignsForStore(int $storeId)
    {
        $now = now();

        return \App\Models\Campaign::query()
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->whereHas('stores', function ($q) use ($storeId) {
                $q->where('stores.id', $storeId)
                  ->where('campaign_store.is_active', true);
            })
            ->with(['items.product.dietTypes', 'items.product.allergens', 'schedules'])
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Check if a specific campaign is active right now.
     */
    public function isCampaignActiveNow(\App\Models\Campaign $campaign): bool
    {
        if (!$campaign->is_active) return false;

        $now = now();
        
        // Date Check
        if ($campaign->start_date && $now->lt($campaign->start_date)) return false;
        if ($campaign->end_date && $now->gt($campaign->end_date)) return false;

        // Schedule Check
        if ($campaign->schedules->isEmpty()) return true;

        $dayOfWeek = strtolower($now->format('l'));
        $yesterday = strtolower($now->copy()->subDay()->format('l'));
        $time = $now->format('H:i:s');

        foreach ($campaign->schedules as $schedule) {
            // Today match
            if (in_array($dayOfWeek, $schedule->days)) {
                if ($schedule->start_time <= $schedule->end_time) {
                    if ($time >= $schedule->start_time && $time <= $schedule->end_time) return true;
                } else {
                    // Overnight
                    if ($time >= $schedule->start_time) return true;
                }
            }
            // Yesterday spill match
            if (in_array($yesterday, $schedule->days) && $schedule->start_time > $schedule->end_time) {
                if ($time <= $schedule->end_time) return true;
            }
        }

        return false;
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
            ->with(['items.product.dietTypes', 'items.product.allergens'])
            ->orderBy('priority', 'desc')
            ->get();
    }

    protected function applyRulesToProduct(&$product, $campaigns)
    {
        // Product structure: ['id', 'name', 'price', 'options' => [...]]
        
        foreach ($campaigns as $campaign) {
            // Check if campaign targets this product
            // logic: campaign->items contains product_id?
            
            $targetItem = $campaign->items->firstWhere('product_id', (int)$product['id']);
            
            if (!$targetItem) {
                continue;
            }

            // FOUND A MATCH!
            $product['campaign_name'] = $campaign->display_title;
            $product['campaign_type'] = $campaign->type->value;

            // Case A: Specific Portion Target (e.g. ID matches in options)
            if ($targetItem->store_product_portion_id && !empty($product['options'])) {
                foreach ($product['options'] as &$option) {
                    if ($option['id'] === $targetItem->store_product_portion_id) {
                         $this->applyDiscountToOption($option, $campaign, $targetItem);
                    }
                }
            }
            // Case B: Product has only ONE portion and campaign targets that portion specifically
            elseif ($targetItem->store_product_portion_id && ($product['store_product_portion_id'] ?? null) === $targetItem->store_product_portion_id) {
                 $this->applyDiscountToProduct($product, $campaign, $targetItem);
            }
            // Case C: Whole Product Target (No specific portion ID specified in campaign)
            elseif (!$targetItem->store_product_portion_id) {
                 $this->applyDiscountToProduct($product, $campaign, $targetItem);
            }

            // High priority campaign applied, stop here to prevent lower priority campaigns from overwriting
            break;
        }
    }


    protected function applyDiscountToOption(&$option, $campaign, $item)
    {
        $originalPrice = $option['price'];
        $newPrice = $originalPrice;

        if ($campaign->type->value === \App\Enums\CampaignType::FIXED_PRICE->value) {
             $newPrice = $item->price_override ?? $campaign->value;
        } elseif ($campaign->type->value === \App\Enums\CampaignType::PERCENTAGE->value) {
             $discount = ($originalPrice * $campaign->value) / 100;
             $newPrice = $originalPrice - $discount;
        } elseif ($campaign->type->value === \App\Enums\CampaignType::BUNDLE->value) {
             $newPrice = $campaign->value;
        } elseif ($campaign->type->value === \App\Enums\CampaignType::COLLECTIVE->value) {
             $option['collective_tiers'] = $campaign->tiers;
             if (!empty($campaign->tiers) && isset($campaign->tiers[0]['price'])) {
                 $newPrice = $campaign->tiers[0]['price']; // Fallback starting price
             }
        }

        // Set name and type for all matches
        $option['campaign_name'] = $campaign->display_title;
        $option['campaign_type'] = $campaign->type->value;

        // Apply price only if it's not 'x_get_y' and is cheaper/valid
        if ($campaign->type->value !== \App\Enums\CampaignType::X_GET_Y->value && ($newPrice < $originalPrice || in_array($campaign->type->value, [\App\Enums\CampaignType::BUNDLE->value, \App\Enums\CampaignType::COLLECTIVE->value]))) {
            $option['campaign_price'] = $newPrice;
        }
    }

    protected function applyDiscountToProduct(&$product, $campaign, $item)
    {
        // If product has no options, it's a simple price
        if (empty($product['options'])) {
            $originalPrice = $product['price'];
            $newPrice = $originalPrice;

             if ($campaign->type->value === \App\Enums\CampaignType::FIXED_PRICE->value) {
                 $newPrice = $item->price_override ?? $campaign->value;
            } elseif ($campaign->type->value === \App\Enums\CampaignType::PERCENTAGE->value) {
                 $discount = ($originalPrice * $campaign->value) / 100;
                 $newPrice = $originalPrice - $discount;
            } elseif ($campaign->type->value === \App\Enums\CampaignType::BUNDLE->value) {
                 $newPrice = $campaign->value;
            } elseif ($campaign->type->value === \App\Enums\CampaignType::COLLECTIVE->value) {
                 $product['collective_tiers'] = $campaign->tiers;
                 if (!empty($campaign->tiers) && isset($campaign->tiers[0]['price'])) {
                     $newPrice = $campaign->tiers[0]['price'];
                 }
            }

            $product['campaign_name'] = $campaign->display_title;
            $product['campaign_type'] = $campaign->type->value;

            if ($campaign->type->value !== \App\Enums\CampaignType::X_GET_Y->value && ($newPrice < $originalPrice || in_array($campaign->type->value, [\App\Enums\CampaignType::BUNDLE->value, \App\Enums\CampaignType::COLLECTIVE->value]))) {
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
