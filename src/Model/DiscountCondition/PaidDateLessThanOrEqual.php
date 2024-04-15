<?php

namespace App\Model\DiscountCondition;

use App\Api\Dto\PriceQueryDto;

final readonly class PaidDateLessThanOrEqual implements DiscountConditionInterface
{
    public function __construct(private string $dateString)
    {
    }


    public function isValidForPriceQuery(PriceQueryDto $priceQuery): bool
    {
        if (!$priceQuery->getPaidDate()) {
            return false;
        }

        $paidDate = $priceQuery->getPaidDate()->setTime(0, 0, 0);
        $borderDate = $priceQuery->getPaidDate()
            ->modify($this->dateString)
            ->setTime(0, 0, 0)
            ->addDay()
            ->subMicrosecond();

        return $paidDate <= $borderDate;
    }
}
