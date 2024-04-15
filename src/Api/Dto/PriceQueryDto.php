<?php

namespace App\Api\Dto;

use Carbon\CarbonImmutable;

readonly class PriceQueryDto implements RequestDtoInterface
{
    /**
     * @psalm-param numeric-string $basePrice
     */
    public function __construct(
        private string $basePrice,
        private CarbonImmutable $birthday,
        private CarbonImmutable $startDate,
        private ?CarbonImmutable $paidDate = null
    ) {
    }

    /**
     * @psalm-return numeric-string $basePrice
     */
    public function getBasePrice(): string
    {
        return $this->basePrice;
    }

    public function getBirthday(): CarbonImmutable
    {
        return $this->birthday;
    }

    public function getStartDate(): CarbonImmutable
    {
        return $this->startDate;
    }

    public function getPaidDate(): ?CarbonImmutable
    {
        return $this->paidDate;
    }
}
