<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\FormField;
use App\Entity\FormSchema;
use App\Entity\Post;
use App\Entity\PostFormValue;
use App\Entity\User;
use App\Event\CommentCreatedEvent;
use App\Form\CommentType;
use App\Repository\FormSchemaRepository;
use App\Repository\PostFormValueRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller used to manage blog contents in the public part of the site.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
#[Route('/blog')]
final class BlogController extends AbstractController
{
    /**
     * NOTE: For standard formats, Symfony will also automatically choose the best
     * Content-Type header for the response.
     *
     * See https://symfony.com/doc/current/routing.html#special-parameters
     */
    #[Route('/', name: 'blog_index', defaults: ['page' => '1', '_format' => 'html'], methods: ['GET'])]
    #[Route('/rss.xml', name: 'blog_rss', defaults: ['page' => '1', '_format' => 'xml'], methods: ['GET'])]
    #[Route('/page/{page<[1-9]\d{0,8}>}', name: 'blog_index_paginated', defaults: ['_format' => 'html'], methods: ['GET'])]
    #[Cache(smaxage: 10)]
    public function index(
        Request $request,
        int $page,
        string $_format,
        PostRepository $posts,
        TagRepository $tags,
        FormSchemaRepository $formSchemaRepository
    ): Response
    {
        $tag = null;
        if ($request->query->has('tag')) {
            $tag = $tags->findOneBy(['name' => $request->query->get('tag')]);
        }
        $latestPosts = $posts->findLatest($page, $tag);

        // Every template name also has two extensions that specify the format and
        // engine for that template.
        // See https://symfony.com/doc/current/templates.html#template-naming
        return $this->render('blog/index.'.$_format.'.twig', [
            'paginator' => $latestPosts,
            'tagName' => $tag?->getName(),
            'formSchemas' => $formSchemaRepository->findAll()
        ]);
    }

    /**
     * NOTE: The $post controller argument is automatically injected by Symfony
     * after performing a database query looking for a Post with the 'slug'
     * value given in the route.
     *
     * See https://symfony.com/doc/current/doctrine.html#automatically-fetching-objects-entityvalueresolver
     */
    #[Route('/posts/{slug}', name: 'blog_post', methods: ['GET'])]
    public function postShow(Post $post): Response
    {
        // Symfony's 'dump()' function is an improved version of PHP's 'var_dump()' but
        // it's not available in the 'prod' environment to prevent leaking sensitive information.
        // It can be used both in PHP files and Twig templates, but it requires to
        // have enabled the DebugBundle. Uncomment the following line to see it in action:
        //
        // dump($post, $this->getUser(), new \DateTime());
        //
        // The result will be displayed either in the Symfony Profiler or in the stream output.
        // See https://symfony.com/doc/current/profiler.html
        // See https://symfony.com/doc/current/templates.html#the-dump-twig-utilities
        //
        // You can also leverage Symfony's 'dd()' function that dumps and
        // stops the execution

        return $this->render('blog/post_show.html.twig', ['post' => $post]);
    }


    #[Route('/{formSchemaName}/search', name: 'post_search', methods: ['GET', 'POST'])]
    public function searchByFromSchema(Request $request, string $formSchemaName, FormSchemaRepository $formSchemaRepository, PostFormValueRepository $postFormValueRepository)
    {
        /** @var FormSchema|null $formSchema */
        $formSchema = $formSchemaRepository->findOneBy(['name' => $formSchemaName]);
        if (!$formSchema) {
            throw new AccessDeniedHttpException();
        }
        // Create a form dynamically based on the form schema fields
        $formFields = $formSchema->getFormFields(); // Assume this gives you an iterable collection of form fields
        $formData = [];

        // Create an array to hold form data that will be used for the search form (for POST requests)
        foreach ($formFields as $field) {
            // Initialize a new input field based on the field type
            switch ($field->getType()) {
                case 'text':
                    $formData[$field->getName()] = $request->get($field->getName(), ''); // For text fields
                    break;
                case 'date':
                case 'dateTime':
                    $formData[$field->getName() . '_from'] = $request->get($field->getName() . '_from', ''); // From date range
                    $formData[$field->getName() . '_to'] = $request->get($field->getName() . '_to', ''); // To date range
                    break;
                case 'list':
                    $formData[$field->getName()] = $request->get($field->getName(), ''); // For list fields
                    break;
                case 'checkbox':
                    $formData[$field->getName()] = $request->get($field->getName(), null) ? true : false;  // true for checked, false for unchecked
                    break;
                case 'cost':
                    $formData[$field->getName()] = $request->get($field->getName(), null);
                    if ($formData[$field->getName()]) {
                        $formData[$field->getName()] = number_format($formData[$field->getName()], 2, '.', '');  // Format to two decimals
                    }
                    break;
                case 'number':
                case 'email':
                case 'url':
                    $formData[$field->getName()] = $request->get($field->getName(), null);
                    break;

                // Add additional cases for other types like 'checkbox', 'email', etc.
            }
        }

        // Handling POST search logic here, if needed.
        if ($request->isMethod('POST')) {
            $postFormValues = $postFormValueRepository->searchByFields($formData);
            // Perform the search logic (e.g., querying the database using the values from $formData)
            // You could search based on the inputs and return the results
            // For now, we can just return the form data as a placeholder
            return $this->render('blog/search_results.html.twig', [
                'formSchema' => $formSchema,
                'formData' => $formData,
                'postFormValues' => $postFormValues
            ]);
        }

        // Render the search form view (for GET requests)
        return $this->render('blog/search_form.html.twig', [
            'formSchema' => $formSchema,
            'formFields' => $formFields,
        ]);
    }


    /**
     * NOTE: The #[MapEntity] mapping is required because the route parameter
     * (postSlug) doesn't match any of the Doctrine entity properties (slug).
     *
     * See https://symfony.com/doc/current/doctrine.html#doctrine-entity-value-resolver
     */
    #[Route('/comment/{postSlug}/new', name: 'comment_new', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function commentNew(
        #[CurrentUser] User $user,
        Request $request,
        #[MapEntity(mapping: ['postSlug' => 'slug'])] Post $post,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
    ): Response {
        $comment = new Comment();
        $comment->setAuthor($user);
        $post->addComment($comment);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($comment);
            $entityManager->flush();

            // When an event is dispatched, Symfony notifies it to all the listeners
            // and subscribers registered to it. Listeners can modify the information
            // passed in the event and they can even modify the execution flow, so
            // there's no guarantee that the rest of this controller will be executed.
            // See https://symfony.com/doc/current/components/event_dispatcher.html
            //
            // If you prefer to process comments asynchronously (e.g. to perform some
            // heavy tasks on them) you can use the Symfony Messenger component.
            // See https://symfony.com/doc/current/messenger.html
            $eventDispatcher->dispatch(new CommentCreatedEvent($comment));

            return $this->redirectToRoute('blog_post', ['slug' => $post->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/comment_form_error.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * This controller is called directly via the render() function in the
     * blog/post_show.html.twig template. That's why it's not needed to define
     * a route name for it.
     *
     * The "id" of the Post is passed in and then turned into a Post object
     * automatically by the ValueResolver.
     *
     * See https://symfony.com/doc/current/doctrine.html#automatically-fetching-objects-entityvalueresolver
     */
    public function commentForm(Post $post): Response
    {
        $form = $this->createForm(CommentType::class);

        return $this->render('blog/_comment_form.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/search', name: 'blog_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        return $this->render('blog/search.html.twig', ['query' => (string) $request->query->get('q', '')]);
    }
}
