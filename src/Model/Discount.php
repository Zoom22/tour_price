<?php

namespace App\Model;

use App\Api\Dto\PriceQueryDto;
use App\Model\DiscountCondition\DiscountConditionInterface;

class Discount
{
    /**
     * @psalm-param numeric-string $amount
     * @psalm-param DiscountConditionInterface[] $conditions
     * @psalm-param ?numeric-string $maxValue
     */
    public function __construct(
        private string $amount,
        private array $conditions,
        private DiscountCategory $category,
        private string $description,
        private ?string $maxValue = null
    ) {
        if (!is_numeric($this->amount)) {
            throw new \InvalidArgumentException('Amount value should be numeric');
        }

        if ($maxValue && !is_numeric($maxValue)) {
            throw new \InvalidArgumentException('Max value should be numeric');
        }

        if ($maxValue === null && $this->category->getDefaultMaxDiscountValue() !== null) {
            $this->maxValue = $this->category->getDefaultMaxDiscountValue();
        }
    }

    public function isAcceptable(PriceQueryDto $priceQuery): bool
    {
        /** @var DiscountConditionInterface $condition */
        foreach ($this->conditions as $condition) {
            if (!$condition->isValidForPriceQuery($priceQuery)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @psalm-return numeric-string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCategory(): DiscountCategory
    {
        return $this->category;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return ?numeric-string
     */
    public function getMaxValue(): ?string
    {
        return $this->maxValue;
    }
}
