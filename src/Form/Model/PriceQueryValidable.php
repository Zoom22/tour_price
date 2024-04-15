<?php

namespace App\Form\Model;

use App\Api\Dto\PriceQueryDto;
use Carbon\CarbonImmutable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PriceQueryValidable implements ValidableInterface
{
    #[Assert\NotBlank(message: "Базовая стоимость обязательна")]
    public ?float $basePrice;
    #[Assert\NotBlank(message: "Дата рождения участника обязательна")]
    #[Assert\Date(message: "Неверная дата рождения участника")]
    public ?string $birthday;
    #[Assert\Date(message: "Неверная дата старта путешествия")]
    public ?string $startDate;
    #[Assert\AtLeastOneOf(
        constraints: [
            new Assert\Blank(),
            new Assert\Date(),
        ],
        message: "Неверная дата оплаты"
    )]
    public ?string $paidDate = null;

    public function toDto(): PriceQueryDto
    {
        return new PriceQueryDto(
            (string)($this->basePrice ?? 0),
            new CarbonImmutable($this->birthday),
            $this->startDate ? new CarbonImmutable($this->startDate) : new CarbonImmutable(),
            empty($this->paidDate) ? null : new CarbonImmutable($this->paidDate)
        );
    }
}
