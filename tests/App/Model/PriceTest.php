<?php

namespace App\Tests\App\Model;

use App\Api\Dto\PriceQueryDto;
use App\Model\Discount;
use App\Model\DiscountCategory;
use App\Model\DiscountCondition\AgeLessThan;
use App\Model\DiscountCondition\PaidMonthEqual;
use App\Model\Price;
use Carbon\CarbonImmutable;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PriceTest extends KernelTestCase
{
    public function testConstructInvalidPrice(): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Price should be numeric');

        new Price('abc');
    }

    public function testGetPrice(): void
    {
        $value = (string)rand(1, 100000);
        $price = new Price($value);

        self::assertEquals($value, $price->getPrice());
    }

    public function testSetPrice(): void
    {
        $initialValue = (string)rand(1, 100000);
        $price = new Price($initialValue);

        $newValue = bcsub($initialValue, '1');
        $price->setPrice($newValue);

        self::assertEquals($newValue, $price->getPrice());
    }

    public function testSetAndGetAppliedDiscounts(): void
    {
        $price = new Price((string)rand(1, 100000));
        $discount1 = new Discount(
            (string)(1 / rand(1, 100)),
            [new AgeLessThan(18)],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            uniqid(),
            null
        );
        $discount2 = new Discount(
            (string)(1 / rand(1, 100)),
            [new PaidMonthEqual(uniqid())],
            new DiscountCategory(2, uniqid(), rand(0, 100), uniqid()),
            uniqid(),
            (string)rand(1, 10000)
        );

        $price->addAppliedDiscount($discount1);
        $price->addAppliedDiscount($discount2);
        self::assertCount(2, $price->getAppliedDiscounts());
        self::assertContains($discount1, $price->getAppliedDiscounts());
        self::assertContains($discount2, $price->getAppliedDiscounts());
    }

    public function testFromPriceQueryDto(): void
    {
        $value = (string)rand(1, 100000);
        $priceQueryDto = new PriceQueryDto(
            $value,
            new CarbonImmutable(),
            new CarbonImmutable(),
            null
        );

        $price = Price::fromPriceQueryDto($priceQueryDto);
        self::assertEquals($value, $price->getPrice());
    }

    public function testIsDiscountCategoryApplied(): void
    {
        $price = new Price((string)rand(1, 100000));
        $discount1 = new Discount(
            (string)(1 / rand(1, 100)),
            [new AgeLessThan(18)],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            uniqid()
        );
        $discount2 = new Discount(
            (string)(1 / rand(1, 100)),
            [new PaidMonthEqual(uniqid())],
            new DiscountCategory(2, uniqid(), rand(0, 100), uniqid()),
            uniqid()
        );

        $price->addAppliedDiscount($discount1);

        self::assertFalse($price->isDiscountCategoryApplied($discount2->getCategory()));

        $price->addAppliedDiscount($discount2);

        self::assertTrue($price->isDiscountCategoryApplied($discount2->getCategory()));
    }
}
