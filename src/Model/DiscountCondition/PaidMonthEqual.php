<?php

namespace App\Model\DiscountCondition;

use App\Api\Dto\PriceQueryDto;

final readonly class PaidMonthEqual implements DiscountConditionInterface
{
    public function __construct(private string $monthString)
    {
    }


    public function isValidForPriceQuery(PriceQueryDto $priceQuery): bool
    {
        if (!$priceQuery->getPaidDate()) {
            return false;
        }

        $month = $priceQuery->getPaidDate()->firstOfMonth();
        $month = $month->change($this->monthString);

        return $priceQuery->getPaidDate()->year === $month->year && $priceQuery->getPaidDate()->month === $month->month;
    }
}
