<?php

namespace App\Controller;

use App\Entity\Pop;
use App\Entity\Group;
use App\Form\PopType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PopController extends AbstractController
{
    #[Route('/group/{id}/pop/new', name: 'new_pop')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, Group $group, EntityManagerInterface $entityManager): Response
    {
        $pop = new Pop();
        $form = $this->createForm(PopType::class, $pop);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Remplir les champs `author` et `group`
            $pop->setAuthor($this->getUser());
            $pop->setRelatedGroup($group);
            $pop->setCreatedAt(new \DateTimeImmutable());

            // Sauvegarder le pop en base de données
            $entityManager->persist($pop);
            $entityManager->flush();

            $this->addFlash('success', 'Pop successfully posted!');

            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }

        return $this->render('pop/new.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
        ]);
    }
}
