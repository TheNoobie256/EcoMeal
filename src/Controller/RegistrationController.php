<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\Consumer;
use App\Entity\User;
use App\Form\BusinessRegistrationFormType;
use App\Form\ConsumerRegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator): Response
    {
        $user = new User();
        $consumer = new Consumer();
        $user->setConsumer($consumer);

        $form = $this->createForm(ConsumerRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setRoles(['ROLE_CONSUMER']);
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $packageUrl = $urlGenerator->generate('app_package', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $email = (new Email())
                ->from('welcome@foodrescue.com')
                ->to($user->getEmail())
                ->subject('Successful Registration')
                ->html(sprintf('<p>Welcome to our application! Link to <a href="%s">packages</a></p>', $packageUrl));

            $mailer->send($email);

            return $security->login($user, LoginFormAuthenticator::class, 'main');
        }

        return $this->render('registration/consumer_register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
    #[Route('/register/business', name: 'app_register_business')]
    public function registerBusiness(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $business = new Business();
        $user->setBusiness($business);

        $form = $this->createForm(BusinessRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setRoles(['ROLE_BUSINESS']);
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_business');
        }

        return $this->render('registration/business_register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
