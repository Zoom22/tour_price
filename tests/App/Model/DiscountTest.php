<?php

namespace App\Tests\App\Model;

use App\Api\Dto\PriceQueryDto;
use App\Model\Discount;
use App\Model\DiscountCategory;
use App\Model\DiscountCondition\AgeLessThan;
use App\Model\DiscountCondition\DiscountConditionInterface;
use Carbon\CarbonImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DiscountTest extends KernelTestCase
{
    public function testConstructInvalidAmount(): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Amount value should be numeric');

        new Discount(
            'abc',
            [new AgeLessThan(18)],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            uniqid(),
            null
        );
    }

    public function testConstructInvalidMaxValue(): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Max value should be numeric');

        new Discount(
            (string)(1 / rand(1, 100)),
            [new AgeLessThan(18)],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            uniqid(),
            'abc'
        );
    }

    public function testConstructMaxValueFromCategoryDefault(): void
    {
        $defaultCategoryMaxValue = (string)rand(1, 100);
        $discount = new Discount(
            (string)(1 / rand(1, 100)),
            [new AgeLessThan(18)],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid(), $defaultCategoryMaxValue),
            uniqid(),
            null
        );

        self::assertEquals((float)$defaultCategoryMaxValue, (float)$discount->getMaxValue());
    }

    public function testIsAcceptable(): void
    {
        $discount = new Discount(
            (string)(1 / rand(1, 100)),
            [],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            uniqid()
        );

        $priceQueryDto = new PriceQueryDto(
            (string)rand(1, 100000),
            new CarbonImmutable(),
            new CarbonImmutable(),
            null
        );

        self::assertTrue($discount->isAcceptable($priceQueryDto));


        $discountCondition1Mock = $this->createMock(DiscountConditionInterface::class);
        $discountCondition1Mock->method('isValidForPriceQuery')->willReturn(true);

        $discountCondition2Mock = $this->createMock(DiscountConditionInterface::class);
        $discountCondition2Mock->method('isValidForPriceQuery')->willReturn(false);


        $discount = new Discount(
            (string)(1 / rand(1, 100)),
            [$discountCondition1Mock, $discountCondition2Mock],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            uniqid()
        );

        $priceQueryDto = new PriceQueryDto(
            (string)rand(1, 100000),
            new CarbonImmutable(),
            new CarbonImmutable(),
            null
        );

        self::assertFalse($discount->isAcceptable($priceQueryDto));


        $discountCondition1Mock = $this->createMock(DiscountConditionInterface::class);
        $discountCondition1Mock->method('isValidForPriceQuery')->willReturn(true);

        $discountCondition2Mock = $this->createMock(DiscountConditionInterface::class);
        $discountCondition2Mock->method('isValidForPriceQuery')->willReturn(true);


        $discount = new Discount(
            (string)(1 / rand(1, 100)),
            [$discountCondition1Mock, $discountCondition2Mock],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            uniqid()
        );

        $priceQueryDto = new PriceQueryDto(
            (string)rand(1, 100000),
            new CarbonImmutable(),
            new CarbonImmutable(),
            null
        );

        self::assertTrue($discount->isAcceptable($priceQueryDto));
    }

    public function testGetAmount(): void
    {
        $value = (string)(1 / rand(1, 100));
        $discount = new Discount(
            $value,
            [],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            uniqid()
        );

        self::assertEquals($value, $discount->getAmount());
    }

    public function testGetDescription(): void
    {
        $description = uniqid();
        $discount = new Discount(
            (string)(1 / rand(1, 100)),
            [],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            $description
        );

        self::assertEquals($description, $discount->getDescription());
    }

    public function testGetMaxValue(): void
    {
        $maxValue = (string)rand(1, 100000);
        $discount = new Discount(
            (string)(1 / rand(1, 100)),
            [],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            uniqid(),
            $maxValue
        );

        self::assertEquals($maxValue, $discount->getMaxValue());
    }
}
