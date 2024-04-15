<?php

namespace App\Model\DiscountCondition;

use App\Api\Dto\PriceQueryDto;

interface DiscountConditionInterface
{
    public function isValidForPriceQuery(PriceQueryDto $priceQuery): bool;
}
