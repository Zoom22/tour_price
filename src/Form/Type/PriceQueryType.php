<?php

namespace App\Form\Type;

use App\Form\Model\PriceQueryValidable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template-extends AbstractType<PriceQueryValidable>
 */
class PriceQueryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('basePrice', IntegerType::class)
            ->add('birthday', BirthdayType::class, [
                'input' => 'string',
                'widget' => 'single_text',
                'invalid_message' => 'Неверная дата рождения',
            ])
            ->add('startDate', DateType::class, [
                'input' => 'string',
                'widget' => 'single_text',
                'empty_data' => '',
                'invalid_message' => 'Неверная дата старта путешествия',
            ])
            ->add('paidDate', DateType::class, [
                'required' => false,
                'input' => 'string',
                'widget' => 'single_text',
                'empty_data' => '',
                'invalid_message' => 'Неверная дата оплаты',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriceQueryValidable::class,
        ]);
    }
}
