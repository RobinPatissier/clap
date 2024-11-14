<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $pops = $user->getPops();
        $following = $user->getFollowing();
        $groups = $user->getGroups();

        // Créer le formulaire d'édition du profil
        $form = $this->createForm(ProfileType::class, $user);

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'pops' => $pops,
            'following' => $following,
            'groups' => $groups,
            'form' => $form->createView(), // Passer le formulaire au template
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de la photo de profil
            $profilePictureFile = $form->get('profilePicture')->getData();
            if ($profilePictureFile) {
                $fileName = uniqid().'.'.$profilePictureFile->guessExtension();
                $profilePictureFile->move($this->getParameter('media_directory'), $fileName);
                $user->setProfilePicture($fileName); // Assurez-vous d'avoir une méthode setProfilePicture dans User
            }

            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour !');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}