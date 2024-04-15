<?php

namespace App\Form\Model;

use App\Api\Dto\RequestDtoInterface;

interface ValidableInterface
{
    public function toDto(): RequestDtoInterface;
}
