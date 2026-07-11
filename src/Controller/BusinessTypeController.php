<?php

namespace App\Controller;

use App\Entity\BusinessType;
use App\Form\BusinessTypeFormType;
use App\Repository\BusinessTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/business-type')]
#[IsGranted('ROLE_ADMIN')]
final class BusinessTypeController extends AbstractController
{
    #[Route('', name: 'app_business_type', methods: ['GET'])]
    public function index(BusinessTypeRepository $repository): Response
    {
        return $this->render('business_type/index.html.twig', [
            'businessTypes' => $repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_business_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $businessType = new BusinessType();
        $form = $this->createForm(BusinessTypeFormType::class, $businessType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($businessType);
            $entityManager->flush();

            return $this->redirectToRoute('app_business_type');
        }

        return $this->render('business_type/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_business_type_view', methods: ['GET'])]
    public function view(BusinessType $businessType): Response
    {
        return $this->render('business_type/view.html.twig', [
            'businessType' => $businessType,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_business_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BusinessType $businessType, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BusinessTypeFormType::class, $businessType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_business_type');
        }

        return $this->render('business_type/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/remove', name: 'app_business_type_del', methods: ['GET'])]
    public function remove(BusinessType $businessType, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($businessType);
        $entityManager->flush();

        return $this->redirectToRoute('app_business_type');
    }
}
