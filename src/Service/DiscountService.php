<?php

namespace App\Service;

use App\Api\Dto\PriceQueryDto;
use App\Model\Discount;
use App\Model\Price;

class DiscountService
{
    /**
     * @psalm-param Discount[] $discounts
     */
    public function applyDiscounts(PriceQueryDto $priceQueryDto, array $discounts): Price
    {
        $price = Price::fromPriceQueryDto($priceQueryDto);

        foreach ($discounts as $discount) {
            /** @var Discount $discount */
            if (
                !$price->isDiscountCategoryApplied($discount->getCategory())
                && $discount->isAcceptable($priceQueryDto)
            ) {
                $this->applyDiscount($price, $discount);
            }
        }

        return $price;
    }

    private function applyDiscount(Price $price, Discount $discount): void
    {
        $discountAmount = bcmul($price->getPrice(), $discount->getAmount());
        $discountMaxValue = $discount->getMaxValue();
        if ($discountMaxValue && bccomp($discountAmount, $discountMaxValue) > 0) {
            $discountAmount = $discountMaxValue;
        }

        $price->setPrice(bcsub($price->getPrice(), $discountAmount));
        $price->addAppliedDiscount($discount);
    }
}
