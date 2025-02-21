<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\FormSchema;

class DynamicFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var FormSchema $formSchema */
        $formSchema = $options['form_schema'];

        foreach ($formSchema->getFormFields() as $field) {
            $fieldType = $field->getType();
            $options = [
                'label' => $field->getDisplayName(),
                'required' => $field->isRequired(),
            ];

            if ($fieldType === ChoiceType::class && $field->getOptionList()) {
                $options['choices'] = json_decode($field->getOptionList(), true);
                $options['choice_label'] = function ($choice, $key, $value) {
                    return $key;
                };
            }

            if ($fieldType === DateType::class || $fieldType === DateTimeType::class) {
                $options['widget'] = 'single_text';
                if ($field->getDateFormat()) {
                    $options['format'] = $field->getDateFormat();
                }
            }

            $builder->add($field->getName(), $fieldType, $options);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('form_schema');
        $resolver->setAllowedTypes('form_schema', FormSchema::class);
    }
}
