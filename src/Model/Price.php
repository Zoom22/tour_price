<?php

namespace App\Model;

use App\Api\Dto\PriceQueryDto;

class Price
{
    private array $appliedDiscounts = [];

    /**
     * @psalm-param numeric-string $price
     */
    public function __construct(private string $price)
    {
        if (!is_numeric($price)) {
            throw new \InvalidArgumentException('Price should be numeric');
        }
    }

    /**
     * @psalm-return numeric-string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @psalm-param numeric-string $price
     */
    public function setPrice(string $price): void
    {
        $this->price = $price;
    }

    public function getAppliedDiscounts(): array
    {
        return $this->appliedDiscounts;
    }

    public function addAppliedDiscount(Discount $discount): void
    {
        if (!in_array($discount, $this->appliedDiscounts)) {
            $this->appliedDiscounts[] = $discount;
        }
    }

    public static function fromPriceQueryDto(PriceQueryDto $priceQuery): self
    {
        return new self($priceQuery->getBasePrice());
    }

    public function isDiscountCategoryApplied(DiscountCategory $discountCategory): bool
    {
        foreach ($this->appliedDiscounts as $discount) {
            /** @var Discount $discount */
            if ($discount->getCategory()->getId() === $discountCategory->getId()) {
                return true;
            }
        }

        return false;
    }
}
