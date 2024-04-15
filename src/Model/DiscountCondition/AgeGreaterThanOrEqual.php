<?php

namespace App\Model\DiscountCondition;

use App\Api\Dto\PriceQueryDto;

final readonly class AgeGreaterThanOrEqual implements DiscountConditionInterface
{
    public function __construct(private int $value)
    {
    }

    public function isValidForPriceQuery(PriceQueryDto $priceQuery): bool
    {
        return $priceQuery->getStartDate()->diff($priceQuery->getBirthday())->y >= $this->value;
    }
}
