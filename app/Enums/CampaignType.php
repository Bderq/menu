<?php

namespace App\Enums;

enum CampaignType: string
{
    case BUNDLE = 'bundle';
    case PERCENTAGE = 'percentage';
    case FIXED_PRICE = 'fixed_price';
    case X_GET_Y = 'x_get_y';
}
