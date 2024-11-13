<?php

namespace App\Controller;

use App\Entity\Pop;
use App\Form\PopType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    #[IsGranted('ROLE_USER')]
    public function feed(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $following = $user->getFollowing();

        // Récupérer tous les Pops des utilisateurs suivis et de l'utilisateur connecté
        $pops = $entityManager->getRepository(Pop::class)
            ->createQueryBuilder('p')
            ->where('p.author IN (:following) OR p.author = :user')
            ->setParameter('following', $following)
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Créer un nouveau Pop
        $newPop = new Pop();
        $form = $this->createForm(PopType::class, $newPop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPop->setAuthor($user);
            $newPop->setRelatedGroup(null);  // Ajoutez cette ligne

            // Gestion du fichier média
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile) {
                // Logique pour enregistrer le fichier
                $fileName = uniqid().'.'.$mediaFile->guessExtension();
                $mediaFile->move($this->getParameter('media_directory'), $fileName);
                $newPop->setMedia($fileName);
            }

            // Gestion du lien YouTube
            $youtubeLink = $form->get('youtubeLink')->getData();
            if ($youtubeLink) {
                $newPop->setYoutubeLink($youtubeLink);
            }

            $entityManager->persist($newPop);
            $entityManager->flush();

            $this->addFlash('success', 'Votre Pop a été publié !');
            return $this->redirectToRoute('home');
        }

        return $this->render('home/feed.html.twig', [
            'pops' => $pops,
            'form' => $form->createView(),
        ]);
    }
}