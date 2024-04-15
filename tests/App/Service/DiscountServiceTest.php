<?php

namespace App\Tests\App\Service;

use App\Api\Dto\PriceQueryDto;
use App\Model\Discount;
use App\Model\DiscountCategory;
use App\Model\DiscountCondition\AgeLessThan;
use App\Model\DiscountCondition\PaidMonthEqual;
use App\Model\Price;
use App\Service\DiscountService;
use Carbon\CarbonImmutable;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DiscountServiceTest extends KernelTestCase
{
    private ?DiscountService $discountService;

    public function testApplyDiscountsEmptyDiscounts(): void
    {
        self::assertNotNull($this->discountService);

        $priceQueryDto = new PriceQueryDto(
            (string)rand(1, 100000),
            new CarbonImmutable(),
            new CarbonImmutable(),
            null
        );

        $price = $this->discountService->applyDiscounts($priceQueryDto, []);

        self::assertEquals($priceQueryDto->getBasePrice(), $price->getPrice());
        self::assertCount(0, $price->getAppliedDiscounts());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testApplyDiscountsSameCategory(): void
    {
        self::assertNotNull($this->discountService);

        $discountCategory = new DiscountCategory(
            1,
            uniqid(),
            rand(1, 100),
            uniqid()
        );

        $discount1Mock = $this->createMock(Discount::class);
        $discount1Mock
            ->method('getCategory')
            ->willReturn($discountCategory);
        $discount1Mock
            ->expects(self::once())
            ->method('isAcceptable')
            ->willReturn(true);
        $discount1Mock
            ->method('getDescription')
            ->willReturn(uniqid());

        $discount2Mock = $this->createMock(Discount::class);
        $discount2Mock
            ->method('getCategory')
            ->willReturn($discountCategory);
        $discount2Mock
            ->expects(self::never())
            ->method('isAcceptable');
        $discount2Mock
            ->method('getDescription')
            ->willReturn(uniqid());

        $priceQueryDto = new PriceQueryDto(
            (string)rand(1, 100000),
            new CarbonImmutable(),
            new CarbonImmutable(),
            null
        );

        $price = $this->discountService->applyDiscounts($priceQueryDto, [$discount1Mock, $discount2Mock]);


        $appliedDiscountsDescriptions = array_map(
            fn (Discount $discount) => $discount->getDescription(),
            $price->getAppliedDiscounts()
        );
        self::assertCount(1, $price->getAppliedDiscounts());
        self::assertContains($discount1Mock->getDescription(), $appliedDiscountsDescriptions);
        self::assertNotContains($discount2Mock->getDescription(), $appliedDiscountsDescriptions);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testApplyDiscountsDifferentCategories(): void
    {
        self::assertNotNull($this->discountService);

        $discount1Mock = $this->createMock(Discount::class);
        $discount1Mock
            ->method('getCategory')
            ->willReturn(new DiscountCategory(
                1,
                uniqid(),
                rand(1, 100),
                uniqid()
            ));
        $discount1Mock
            ->expects(self::once())
            ->method('isAcceptable')
            ->willReturn(true);
        $discount1Mock
            ->method('getDescription')
            ->willReturn(uniqid());

        $discount2Mock = $this->createMock(Discount::class);
        $discount2Mock
            ->expects(self::once())
            ->method('getCategory')
            ->willReturn(new DiscountCategory(
                2,
                uniqid(),
                rand(1, 100),
                uniqid()
            ));
        $discount2Mock
            ->expects(self::once())
            ->method('isAcceptable')
            ->willReturn(true);
        $discount2Mock
            ->method('getDescription')
            ->willReturn(uniqid());

        $priceQueryDto = new PriceQueryDto(
            (string)rand(1, 100000),
            new CarbonImmutable(),
            new CarbonImmutable(),
            null
        );

        $price = $this->discountService->applyDiscounts($priceQueryDto, [$discount1Mock, $discount2Mock]);

        $appliedDiscountsDescriptions = array_map(
            fn (Discount $discount) => $discount->getDescription(),
            $price->getAppliedDiscounts()
        );
        self::assertCount(2, $price->getAppliedDiscounts());
        self::assertContains($discount1Mock->getDescription(), $appliedDiscountsDescriptions);
        self::assertContains($discount2Mock->getDescription(), $appliedDiscountsDescriptions);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testApplyDiscounts(): void
    {
        self::assertNotNull($this->discountService);

        $discount1Mock = $this->createMock(Discount::class);
        $discount1Mock
            ->method('getCategory')
            ->willReturn(new DiscountCategory(
                1,
                uniqid(),
                rand(1, 100),
                uniqid()
            ));
        $discount1Mock
            ->expects(self::once())
            ->method('isAcceptable')
            ->willReturn(true);
        $discount1Mock
            ->method('getDescription')
            ->willReturn(uniqid());

        $discount2Mock = $this->createMock(Discount::class);
        $discount2Mock
            ->method('getCategory')
            ->willReturn(new DiscountCategory(
                2,
                uniqid(),
                rand(1, 100),
                uniqid()
            ));
        $discount2Mock
            ->expects(self::once())
            ->method('isAcceptable')
            ->willReturn(false);
        $discount2Mock
            ->method('getDescription')
            ->willReturn(uniqid());

        $discount3Mock = $this->createMock(Discount::class);
        $discount3Mock
            ->method('getCategory')
            ->willReturn(new DiscountCategory(
                3,
                uniqid(),
                rand(1, 100),
                uniqid()
            ));
        $discount3Mock
            ->expects(self::once())
            ->method('isAcceptable')
            ->willReturn(true);
        $discount3Mock
            ->method('getDescription')
            ->willReturn(uniqid());

        $priceQueryDto = new PriceQueryDto(
            (string)rand(1, 100000),
            new CarbonImmutable(),
            new CarbonImmutable(),
            null
        );

        $price = $this->discountService->applyDiscounts(
            $priceQueryDto,
            [$discount1Mock, $discount2Mock, $discount3Mock]
        );

        $appliedDiscountsDescriptions = array_map(
            fn (Discount $discount) => $discount->getDescription(),
            $price->getAppliedDiscounts()
        );
        self::assertCount(2, $price->getAppliedDiscounts());
        self::assertContains($discount1Mock->getDescription(), $appliedDiscountsDescriptions);
        self::assertContains($discount3Mock->getDescription(), $appliedDiscountsDescriptions);
    }

    /**
     * @throws ReflectionException
     */
    public function testApplyDiscount(): void
    {
        self::assertNotNull($this->discountService);

        $price = new Price('100000');

        $discount1 = new Discount(
            '0.55',
            [new AgeLessThan(18)],
            new DiscountCategory(1, uniqid(), rand(0, 100), uniqid()),
            uniqid(),
            null
        );
        $discount2 = new Discount(
            '0.05',
            [new PaidMonthEqual(uniqid())],
            new DiscountCategory(2, uniqid(), rand(0, 100), uniqid()),
            uniqid(),
            '50'
        );

        $this->invokeMethod($this->discountService, 'applyDiscount', [$price, $discount1]);

        self::assertEquals(45000, (float)$price->getPrice());

        $this->invokeMethod($this->discountService, 'applyDiscount', [$price, $discount2]);

        self::assertEquals(44950, (float)$price->getPrice());
    }

    /**
     * @throws ReflectionException
     */
    private function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->discountService = self::getContainer()->get(DiscountService::class);
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $this->discountService = null;
    }
}
