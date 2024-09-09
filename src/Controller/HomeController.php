<?php

// src/Controller/HomeController.php
namespace App\Controller;

use App\Entity\Pop;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    #[IsGranted('ROLE_USER')]
    public function feed(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $following = $user->getFollowing();

        // Récupérer tous les Pops des utilisateurs suivis
        $pops = $entityManager->getRepository(Pop::class)
            ->createQueryBuilder('p')
            ->where('p.author IN (:following)')
            ->setParameter('following', $following)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('home/feed.html.twig', [
            'pops' => $pops,
        ]);
    }
}
