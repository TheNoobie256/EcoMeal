<?php

namespace App\Controller;

use App\Entity\Consumer;
use App\Form\ConsumerFormType;
use App\Repository\ConsumerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/consumer')]
final class ConsumerController extends AbstractController
{
    #[Route('', name: 'app_consumer', methods: ['GET'])]
    public function index(ConsumerRepository $consumerRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('consumer/index.html.twig', [
            'consumers' => $consumerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_consumer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $consumer = new Consumer();
        $form = $this->createForm(ConsumerFormType::class, $consumer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($consumer);
            $entityManager->flush();

            return $this->redirectToRoute('app_consumer');
        }

        return $this->render('consumer/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_consumer_view', methods: ['GET'])]
    public function view(Consumer $consumer): Response
    {
        $this->checkConsumerAccess($consumer);

        return $this->render('consumer/view.html.twig', [
            'consumer' => $consumer,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_consumer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Consumer $consumer, EntityManagerInterface $entityManager): Response
    {
        $this->checkConsumerAccess($consumer);

        $form = $this->createForm(ConsumerFormType::class, $consumer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app_consumer_view', ['id' => $consumer->getId()]);
            }

            return $this->redirectToRoute('app_consumer');
        }

        return $this->render('consumer/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/remove', name: 'app_consumer_del', methods: ['GET'])]
    public function remove(Consumer $consumer, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($consumer);
        $entityManager->flush();

        return $this->redirectToRoute('app_consumer');
    }

    private function checkConsumerAccess(Consumer $consumer): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->getUser();

        if ($this->isGranted('ROLE_CONSUMER')) {
            if (!$user->getConsumer() || $user->getConsumer()->getId() !== $consumer->getId()) {
                throw $this->createAccessDeniedException('You can only view your own profile.');
            }
        } else {
            throw $this->createAccessDeniedException('Access Denied.');
        }
    }
}
