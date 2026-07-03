<?php

namespace App\Controller;

use App\Entity\Business;
use App\Form\BusinessFormType;
use App\Repository\BusinessRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BusinessController extends AbstractController
{
    #[Route('/business', name: 'app_business')]
    public function index(BusinessRepository $businessRepository): Response
    {
        $businesses = $businessRepository->findAll();

        return $this->render('business/index.html.twig', [
            'businesses' => $businesses,
        ]);
    }

    #[Route('/business/{id}', name: 'app_business_view')]
    public function view(Business $business): Response
    {
        return $this->render('business/view.html.twig', [
            'business' => $business,
        ]);
    }

    #[Route('/new/business', name: 'app_business_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $business = new Business();
        $form = $this->createForm(BusinessFormType::class, $business);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($business);
            $entityManager->flush();

            return $this->redirectToRoute('app_business');
        }

        return $this->render('/business/new.html.twig',[
            'form' => $form,
        ]);
    }
}
