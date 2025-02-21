<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Entity\FormSchema;
use App\Form\DynamicFormType;
use App\Form\FormSchemaType;
use App\Repository\FormSchemaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/form_schema')]
class FormSchemaController extends AbstractController
{
    #[Route('/', name: 'app_form_schema_index', methods: ['GET'])]
    public function index(FormSchemaRepository $formSchemaRepository): Response
    {
        return $this->render('form_schema/index.html.twig', [
            'form_schemas' => $formSchemaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_form_schema_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formSchema = new FormSchema();
        $form = $this->createForm(FormSchemaType::class, $formSchema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($formSchema);
            $entityManager->flush();

            return $this->redirectToRoute('app_form_schema_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('form_schema/new.html.twig', [
            'form_schema' => $formSchema,
            'form' => $form,
        ]);
    }

    #[Route('/{id}.json', name: 'app_form_schema_json_show', methods: ['GET'])]
    public function showJson(FormSchema $formSchema): Response
    {
        $data = [];
        foreach ($formSchema->getFormFieldsOrderedByCreatedAt() as $formField) {
            $data[] = [
                'id' => $formField->getId(),
                'name' => $formField->getName(),
                'required' => $formField->isRequired(),
                'display_name' => $formField->getDisplayName(),
                'type' => $formField->getType(),
                'data_format' => $formField->getDateFormat(),
                'list' => $formField->getOptionList()
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'app_form_schema_show', methods: ['GET'])]
    public function show(FormSchema $formSchema): Response
    {
        return $this->render('form_schema/show.html.twig', [
            'form_schema' => $formSchema,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_form_schema_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FormSchema $formSchema, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormSchemaType::class, $formSchema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_form_schema_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('form_schema/edit.html.twig', [
            'form_schema' => $formSchema,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_form_schema_delete', methods: ['POST'])]
    public function delete(Request $request, FormSchema $formSchema, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$formSchema->getId(), $request->request->get('_token'))) {
            $entityManager->remove($formSchema);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_form_schema_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/dynamic-form/{id}', name: 'app_dynamic_form')]
    public function createDynamicForm(FormSchema $formSchema, Request $request): Response
    {
        $form = $this->createForm(DynamicFormType::class, null, ['form_schema' => $formSchema]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process submitted data
            $formData = $form->getData();

            // Example: Save or process the data
            $this->addFlash('success', 'Form submitted successfully!');
            return $this->redirectToRoute('app_dynamic_form', ['id' => $formSchema->getId()]);
        }

        return $this->render('dynamic_form/index.html.twig', [
            'form' => $form->createView(),
            'formSchema' => $formSchema,
        ]);
    }
}
