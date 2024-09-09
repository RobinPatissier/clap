<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/user/{id}/follow', name: 'user_follow')]
    #[IsGranted('ROLE_USER')]
    public function follow(User $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        $currentUser = $this->getUser();
        if ($currentUser !== $user) {
            $currentUser->follow($user);
            $entityManager->flush();

            $this->addFlash('success', 'You are now following ' . $user->getEmail());
        }

        return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
    }

    #[Route('/user/{id}/unfollow', name: 'user_unfollow')]
    #[IsGranted('ROLE_USER')]
    public function unfollow(User $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        $currentUser = $this->getUser();
        if ($currentUser !== $user) {
            $currentUser->unfollow($user);
            $entityManager->flush();

            $this->addFlash('success', 'You are no longer following ' . $user->getEmail());
        }

        return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
    }

    #[Route('/user/{id}', name: 'user_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(User $user): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
