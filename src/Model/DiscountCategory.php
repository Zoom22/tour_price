<?php

namespace App\Model;

final readonly class DiscountCategory
{
    /**
     * @psalm-param ?numeric-string $defaultMaxDiscountValue
     */
    public function __construct(
        private int $id,
        private string $name,
        private int $priority,
        private string $description,
        private ?string $defaultMaxDiscountValue = null,
    ) {
        if ($this->defaultMaxDiscountValue !== null && !is_numeric($this->defaultMaxDiscountValue)) {
            throw new \InvalidArgumentException('Default max discount value should be numeric');
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return numeric-string
     */
    public function getDefaultMaxDiscountValue(): ?string
    {
        return $this->defaultMaxDiscountValue;
    }
}
