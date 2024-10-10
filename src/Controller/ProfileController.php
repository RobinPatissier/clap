<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $user = $this->getUser();
        $pops = $user->getPops();
        $following = $user->getFollowing();
        $groups = $user->getGroups();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'pops' => $pops,
            'following' => $following,
            'groups' => $groups,
        ]);
    }
}