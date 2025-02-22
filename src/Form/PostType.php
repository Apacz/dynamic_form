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

use App\Entity\FormSchema;
use App\Entity\Post;
use App\Form\Type\DateTimePickerType;
use App\Form\Type\TagsInputType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Defines the form used to create and manipulate blog posts.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
final class PostType extends AbstractType
{
    // Form types are services, so you can inject other services in them if needed
    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // For the full reference of options defined by each form field type
        // see https://symfony.com/doc/current/reference/forms/types.html

        // By default, form fields include the 'required' attribute, which enables
        // the client-side form validation. This means that you can't test the
        // server-side validation errors from the browser. To temporarily disable
        // this validation, set the 'required' attribute to 'false':
        // $builder->add('title', null, ['required' => false, ...]);

        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true],
                'label' => 'label.title',
            ])
            ->add('summary', TextareaType::class, [
                'help' => 'help.post_summary',
                'label' => 'label.summary',
            ])
            ->add('content', null, [
                'attr' => ['rows' => 20],
                'help' => 'help.post_content',
                'label' => 'label.content',
            ])
            ->add('publishedAt', DateTimePickerType::class, [
                'label' => 'label.published_at',
                'help' => 'help.post_publication',
            ])
            ->add('tags', TagsInputType::class, [
                'label' => 'label.tags',
                'required' => false,
            ])
            ->add('formSchema', EntityType::class, [
                'class' => FormSchema::class,
                'required' => false,
                'placeholder' => 'Choose a form schema',
                'attr' => ['class' => 'form-schema-selector']
            ])
            // form events let you modify information or fields at different steps
            // of the form handling process.
            // See https://symfony.com/doc/current/form/events.html
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Post $post */
                $post = $event->getData();
                if (null === $post->getSlug() && null !== $post->getTitle()) {
                    $post->setSlug($this->slugger->slug($post->getTitle())->lower());
                }
            })
        ;
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!isset($data['formSchema']) || empty($data['formSchema'])) {
                return;
            }

            // Fetch fields dynamically
//            $this->addDynamicFields($form, $data['formSchema']);
        });
    }

    private function addDynamicFields(FormInterface $form, int $formSchemaId): void
    {
        // You can inject the repository or fetch fields via Ajax instead
        // Assume we have an array of fields here (you will get this via Ajax)
        $fields = [
            ['name' => 'exampleField', 'type' => TextType::class]
        ];

        foreach ($fields as $field) {
            $form->add($field['name'], $field['type']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'allow_extra_fields' => true, // Allow extra fields dynamically
        ]);
    }
}
