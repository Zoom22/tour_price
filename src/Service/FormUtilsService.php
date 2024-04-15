<?php

namespace App\Service;

use App\Api\Dto\RequestDtoInterface;
use App\Exception\FormValidationException;
use App\Form\Model\ValidableInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;

class FormUtilsService
{
    public function __construct(private FormFactoryInterface $formFactory)
    {
    }


    /**
     * @psalm-param class-string<FormTypeInterface> $type
     */
    public function getDtoForForm(string $type, mixed $data): RequestDtoInterface
    {
        $form = $this->formFactory->create($type);
        $form->submit($data);

        if (!$form->isValid()) {
            throw new FormValidationException($form);
        }

        /** @var ValidableInterface $formData */
        $formData = $form->getData();

        return $formData->toDto();
    }
}
