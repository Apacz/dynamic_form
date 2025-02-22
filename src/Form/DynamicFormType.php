<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
            $fieldType = $this->mapFieldType($field->getType());
            $options = [
                'label' => $field->getDisplayName(),
                'required' => $field->isRequired(),
            ];

            // Handle "list" type field (ChoiceType)
            if ($fieldType === ChoiceType::class && $field->getOptionList()) {
                $options['choices'] = json_decode($field->getOptionList(), true);
                $options['choice_label'] = function ($choice, $key, $value) {
                    return $key;
                };
            }

            // Handle Date and DateTime types
            if ($fieldType === DateType::class || $fieldType === DateTimeType::class) {
                $options['widget'] = 'single_text';
                if ($field->getDateFormat()) {
                    $options['format'] = $field->getDateFormat();
                }
            }

            // Special handling for cost field to limit precision
            if ($field->getType() === 'cost') {
                $options['attr'] = ['step' => '0.01']; // Limit input to two decimal places
                $options['scale'] = 2; // Limit decimal precision to two digits
            }

            $builder->add($field->getName(), $fieldType, $options);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('form_schema');
        $resolver->setAllowedTypes('form_schema', FormSchema::class);
    }

    private function mapFieldType(string $type): string
    {
        return match ($type) {
            'text' => TextType::class,
            'date' => DateType::class,
            'dateTime' => DateTimeType::class,
            'list' => ChoiceType::class,
            'checkbox' => CheckboxType::class,
            'number' => NumberType::class,
            'cost' => NumberType::class,
            'email' => EmailType::class,
            'url' => UrlType::class,
            default => TextType::class,
        };
    }
}
