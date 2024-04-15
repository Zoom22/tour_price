<?php

namespace App\Tests\App\Model;

use App\Model\DiscountCategory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DiscountCategoryTest extends KernelTestCase
{
    public function testConstructInvalidMaxValue(): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Default max discount value should be numeric');

        new DiscountCategory(1, uniqid(), rand(0, 100), uniqid(), 'abc');
    }

    public function testGetId(): void
    {
        $id = rand(1, 10000);
        $discountCategory = new DiscountCategory($id, uniqid(), rand(0, 100), uniqid());
        $this->assertEquals($id, $discountCategory->getId());
    }

    public function testGetName(): void
    {
        $name = uniqid();
        $discountCategory = new DiscountCategory(rand(1, 10000), $name, rand(0, 100), uniqid());
        $this->assertEquals($name, $discountCategory->getName());
    }

    public function testGetPriority(): void
    {
        $priority = rand(1, 100);
        $discountCategory = new DiscountCategory(rand(1, 10000), uniqid(), $priority, uniqid());
        $this->assertEquals($priority, $discountCategory->getPriority());
    }

    public function testGetDescription(): void
    {
        $description = uniqid();
        $discountCategory = new DiscountCategory(
            rand(1, 10000),
            uniqid(),
            rand(1, 100),
            $description
        );
        $this->assertEquals($description, $discountCategory->getDescription());
    }

    public function testGetDefaultMaxDiscountValue(): void
    {
        $defaultMaxDiscountValue = (string)rand(1, 100);
        $discountCategory = new DiscountCategory(
            rand(1, 10000),
            uniqid(),
            rand(1, 100),
            uniqid(),
            $defaultMaxDiscountValue
        );
        $this->assertEquals($defaultMaxDiscountValue, $discountCategory->getDefaultMaxDiscountValue());
    }
}
