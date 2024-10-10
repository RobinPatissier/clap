<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\User;
use App\Form\GroupType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GroupController extends AbstractController
{
    #[Route('/group/new', name: 'new_group')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérification explicite que l'utilisateur est authentifié
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajouter l'utilisateur créateur comme membre du groupe
            $group->addUser($user);
            $group->setCreatedAt(new \DateTimeImmutable());

            // Sauvegarder le groupe
            $entityManager->persist($group);
            $entityManager->flush();

            $this->addFlash('success', 'Group created successfully!');

            return $this->redirectToRoute('group_list');
        }

        return $this->render('group/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/group/{id}/delete', name: 'delete_group')]
    #[IsGranted('ROLE_USER')]
    public function delete(Group $group, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérification explicite que l'utilisateur est authentifié
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'utilisateur est membre du groupe
        if (!$group->getUsers()->contains($user)) {
            $this->addFlash('error', 'You cannot delete a group you do not belong to.');
            return $this->redirectToRoute('group_list');
        }

        // Supprimer le groupe
        $entityManager->remove($group);
        $entityManager->flush();

        $this->addFlash('success', 'Group deleted successfully!');

        return $this->redirectToRoute('group_list');
    }

    #[Route('/groups', name: 'group_list')]
    #[IsGranted('ROLE_USER')]
    public function list(): Response
    {
        $user = $this->getUser();

        // Vérification explicite que l'utilisateur est authentifié
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $groups = $user->getGroups();

        return $this->render('group/list.html.twig', [
            'groups' => $groups,
        ]);
    }

    #[Route('/group/{id}', name: 'group_show')]
    #[IsGranted('ROLE_USER')]
    public function show(Group $group): Response
    {
        $user = $this->getUser();

        // Vérification explicite que l'utilisateur est authentifié
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les pops associés au groupe
        $pops = $group->getPops();

        return $this->render('group/show.html.twig', [
            'group' => $group,
            'pops' => $pops,
        ]);
    }
}
