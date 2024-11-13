<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserSearchType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UserController extends AbstractController
{
    #[Route('/search', name: 'user_search')]
    #[IsGranted('ROLE_USER')]
    public function search(Request $request, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserSearchType::class);
        $form->handleRequest($request);

        $users = [];
        $movies = [];
        
        if ($form->isSubmitted() && $form->isValid()) {
            $searchTermUser = $form->get('pseudo')->getData(); // Récupérer le terme de recherche pour les utilisateurs
            if ($searchTermUser) {
                $users = $userRepository->findByPseudoLike($searchTermUser, $this->getUser());
            }

            $searchTermMovie = $form->get('movie')->getData(); // Récupérer le terme de recherche pour les films
            if ($searchTermMovie) {
                $movies = $this->searchMovies($searchTermMovie);
            }
        }

        return $this->render('user/search.html.twig', [
            'form' => $form->createView(),
            'users' => $users,
            'movies' => $movies,
        ]);
    }

    #[Route('/user/{id}/follow', name: 'user_follow')]
    #[IsGranted('ROLE_USER')]
    public function follow(User $userToFollow, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();

        if ($currentUser !== $userToFollow) {
            $currentUser->follow($userToFollow);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_search');
    }

    #[Route('/user/{id}/unfollow', name: 'user_unfollow')]
    #[IsGranted('ROLE_USER')]
    public function unfollow(User $userToUnfollow, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();

        if ($currentUser !== $userToUnfollow) {
            $currentUser->unfollow($userToUnfollow);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_search');
    }

    #[Route('/user/{id}', name: 'user_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(User $user): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    private function searchMovies(string $query): array
    {
        $client = new Client();
        // $apiKey = $this->getParameter('OMDB_API_KEY'); // Récupérer la clé API depuis les paramètres
        $url = 'http://www.omdbapi.com/?apikey=fd35f46b' . '&s=' . urlencode($query);
        $response = $client->request('POST', $url);
        $data = json_decode($response->getBody(), true);
       

        return $data['Search'] ?? []; // Retourne les résultats de recherche
    }
}
