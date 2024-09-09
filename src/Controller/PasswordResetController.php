<?php

// src/Controller/PasswordResetController.php
namespace App\Controller;

use App\Entity\User;
use App\Entity\PasswordResetToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; 
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordResetController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function request(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => 'Enter your email address',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Request Password Reset',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $data['email'];

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiresAt = new \DateTime('+1 hour');

                $resetToken = new PasswordResetToken();
                $resetToken->setToken($token);
                $resetToken->setExpiresAt($expiresAt);
                $resetToken->setUser($user);

                $entityManager->persist($resetToken);
                $entityManager->flush();

                $resetUrl = $urlGenerator->generate('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                $email = (new TemplatedEmail())
                    ->from('noreply@example.com')
                    ->to($user->getEmail())
                    ->subject('Password Reset Request')
                    ->htmlTemplate('emails/password_reset.html.twig')
                    ->context(['resetUrl' => $resetUrl]);

                // Envoyer l'e-mail directement sans utiliser Messenger
                try {
                    $mailer->send($email);
                    $this->addFlash('success', 'If an account with this email exists, a reset link has been sent.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'There was a problem sending your email: ' . $e->getMessage());
                }
            } else {
                $this->addFlash('success', 'If an account with this email exists, a reset link has been sent.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('password_reset/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(string $token, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $resetToken = $entityManager->getRepository(PasswordResetToken::class)->findOneBy(['token' => $token]);

        if (!$resetToken || $resetToken->getExpiresAt() < new \DateTime()) {
            $this->addFlash('error', 'Token is invalid or expired.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createFormBuilder()
            ->add('password', PasswordType::class, [
                'label' => 'New Password',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Reset Password',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $resetToken->getUser();
            $newPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($newPassword);

            $entityManager->remove($resetToken);
            $entityManager->flush();

            $this->addFlash('success', 'Password has been reset.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('password_reset/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
