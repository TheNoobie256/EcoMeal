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

final class BusinessTypeController extends AbstractController
{
    #[Route('/business-type', name: 'app_business_type')]
    public function index(BusinessTypeRepository $businessTypeRepository): Response
    {
        $businessTypes = $businessTypeRepository->findAll();

        return $this->render('business_type/index.html.twig', [
            'businessTypes' => $businessTypes,
        ]);
    }

    #[Route('/business-type/{id}', name: 'app_business_type_view')]
    public function view(BusinessType $businessType): Response
    {
        return $this->render('business_type/view.html.twig', [
            'businessType' => $businessType,
        ]);
    }

    #[Route('/new/business-type', name: 'app_business_type_new', methods: ['GET', 'POST'])]
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
}
