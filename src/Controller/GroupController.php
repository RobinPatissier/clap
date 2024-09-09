<?php

namespace App\Controller;

use App\Entity\Group;
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
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajouter l'utilisateur créateur comme membre du groupe
            $group->addUser($this->getUser());
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
        // Vérifier si l'utilisateur est membre du groupe
        if (!$group->getUsers()->contains($this->getUser())) {
            $this->addFlash('error', 'You cannot delete a group you do not belong to.');
            return $this->redirectToRoute('group_list');
        }

        // Supprimer le groupe
        $entityManager->remove($group);
        $entityManager->flush();

        $this->addFlash('success', 'Group deleted successfully!');

        return $this->redirectToRoute('group_list');
    }

    // Méthode pour lister les groupes auxquels appartient l'utilisateur
    #[Route('/groups', name: 'group_list')]
    #[IsGranted('ROLE_USER')]
    public function list(): Response
    {
        $user = $this->getUser();
        $groups = $user->getGroups();

        return $this->render('group/list.html.twig', [
            'groups' => $groups,
        ]);
    }

    // Méthode pour afficher les détails d'un groupe et ses pops
    #[Route('/group/{id}', name: 'group_show')]
    #[IsGranted('ROLE_USER')]
    public function show(Group $group): Response
    {
        // Récupérer les pops associés au groupe
        $pops = $group->getPops();

        return $this->render('group/show.html.twig', [
            'group' => $group,
            'pops' => $pops,
        ]);
    }
}
