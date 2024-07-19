<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'posts.list')]
    public function postsList(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        $currentUser = $this->getUser();

        return $this->render('posts/posts.list.html.twig', [
            'posts' => $posts,
            'currentUser' => $currentUser,
        ]);
    }

    #[Route('/addPost', name: 'post.add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // Rediriger vers l'accueil si l'utilisateur n'est pas connecté
            return $this->redirectToRoute('home');
        } else {
            $post = new Post();
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Récupérer l'utilisateur connecté
                $user = $this->getUser();
                // Assurez-vous que l'utilisateur est connecté et a une méthode 'getName'
                if ($user) {
                    $username = $user->getName(); // Méthode pour obtenir le nom de l'utilisateur
                    $slug = $post->getName();
                    $userId = $user->getId();
                    $post->setAuthor($username)
                        ->setSlug($slug)
                        ->setUserId($userId);
                }

                $em->persist($post);
                $em->flush();

                return $this->redirectToRoute('posts.list', status: Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('posts/add.post.html.twig', [
            'form' => $form->createView(), // Ajout de createView() pour un rendu correct du formulaire
            'post' => $post
        ]);
    }
    #[Route('posts/{slug}-{id}', name: 'post.details', requirements: ['id' => '\d*'])]
    public function postDetails(PostRepository $postRepository, $id = null)
    {
        // Vérifier si l'ID est fourni
        if ($id !== null) {
            // Si l'ID est fourni, rechercher le post par ID
            $post = $postRepository->find($id);
            $currentUser = $this->getUser();
            if (!$post) {
                // Rediriger vers la page d'accueil si le post n'est pas trouvé
                return $this->redirectToRoute('home');
            }
        } else {
            return $this->redirectToRoute('home');
        }
        return $this->render('posts/post.details.html.twig', [
            'post' => $post,
            'currentUser' => $currentUser
        ]);
    }
    #[Route('/posts/{slug}-{id}/edit', name: 'post.edit')]
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login'); // Redirige vers la page de connexion si non connecté
        }

        // Vérifiez que l'utilisateur est l'auteur du post
        if ($user->getId() !== $post->getUserId()) {
            // Redirigez vers une page d'erreur ou la liste des posts
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier ce post.');
            return $this->redirectToRoute('posts.list');
        }
        // Créez le formulaire pour l'objet Post
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        // Vérifiez si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($post);
            $em->flush();

            // Redirigez vers la page de détails du post ou une autre page après la sauvegarde
            return $this->redirectToRoute('post.details', ['slug' => $post->getSlug(), 'id' => $post->getId()]);
        }

        return $this->render('posts/post.edit.html.twig', [
            'post' => $post,
            'editForm' => $form->createView(),
        ]);
    }

    #[Route('/posts/{slug}-{id}/delete', name: 'post.delete', methods: ['POST'])]
    public function delete(Post $post, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        // Assurez-vous que la méthode est appelée
        // dd('Delete method called');
        $user = $this->getUser();
        if (!$user) {
            dd('User not logged in');
            return $this->redirectToRoute('app_login');
        }

        if ($user->getId() !== $post->getuserId()) {
            dd('User not authorized');
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce post.');
            return $this->redirectToRoute('posts.list');
        }

        $em->remove($post);
        $em->flush();
        $this->addFlash('success', 'Post supprimé avec succès.');

        return $this->redirectToRoute('posts.list');
    }
}
