<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\FormField;
use Doctrine\DBAL\Types\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
            $builder
                ->add('name', TextType::class)
                ->add('displayName', TextType::class)
                ->add('required', CheckboxType::class, [
                    'required' => false, // Default to true in processing
                    'data' => true, // Default checked
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'Textarea' => TextType::class,
                        'Date' => DateType::class,
                        'DateTime' => DateTimeType::class,
                        'Select' => ChoiceType::class,
                    ],
                    'placeholder' => 'Choose field type',
                    'attr' => ['class' => 'field-type'],
                ])
                ->add('dateFormat', ChoiceType::class, [
                    'choices' => [
                        'YYYY-MM-DD' => 'Y-m-d',
                        'DD-MM-YYYY' => 'd-m-Y',
                        'MM/DD/YYYY' => 'm/d/Y',
                        'Full DateTime' => 'Y-m-d H:i:s',
                    ],
                    'placeholder' => 'Choose date format',
                    'required' => false,
                    'attr' => ['class' => 'date-format'],
                ])
                ->add('optionList', TextType::class, [
                    'required' => false,
                    'attr' => ['placeholder' => 'Enter options as JSON', 'class' => 'option-list'],
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormField::class,
        ]);
    }
}
