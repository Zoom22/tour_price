<?php

namespace App\Model\DiscountCondition;

use App\Api\Dto\PriceQueryDto;

class StartDateGreaterThanOrEqual implements DiscountConditionInterface
{
    public function __construct(private string $dateString)
    {
    }


    public function isValidForPriceQuery(PriceQueryDto $priceQuery): bool
    {
        if (!$priceQuery->getPaidDate()) {
            return false;
        }

        $startDate = $priceQuery->getStartDate()->setTime(0, 0, 0);
        $borderDate = $priceQuery->getPaidDate()->change($this->dateString)->setTime(0, 0, 0);

        return $startDate >= $borderDate;
    }
}
