<?php

namespace App\Exception;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class FormValidationException extends \Exception
{
    private array $plainErrors;

    public function __construct(private readonly FormInterface $form)
    {
        $this->plainErrors = self::getFormErrors($form);

        $message = sprintf('FormValidationException: %s', json_encode($this->plainErrors));
        parent::__construct($message);
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getErrors(): array
    {
        return $this->plainErrors;
    }

    private static function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            /** @psalm-var FormError $error */
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if (($childForm instanceof FormInterface) && $childErrors = self::getFormErrors($childForm)) {
                $errors[$childForm->getName()] = $childErrors;
            }
        }

        return array_filter($errors);
    }
}
