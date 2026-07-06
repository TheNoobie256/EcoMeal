<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\Package;
use App\Form\BusinessFormType;
use App\Form\PackageFormType;
use App\Repository\BusinessRepository;
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
    #[Route('/business/{id}/edit', name: 'app_business_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Business $business, EntityManagerInterface $entityManager): Response
    {
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

    #[Route('/business/{id}/remove', name: 'app_business_del', methods: ['GET'])]
    public function remove(Business $business, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($business);
        $entityManager->flush();

        return $this->redirectToRoute('app_business');
    }

    #[Route('/business/{id}/add_package', name: 'app_business_add_package')]
    public function addPackage(Request $request,Business $business, EntityManagerInterface $entityManager): Response
    {
        {
            $package = new Package();
            $package->setBusiness($business);
            $form = $this->createForm(PackageFormType::class, $package);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($package);
                $entityManager->flush();

                return $this->redirectToRoute('app_package');
            }

            return $this->render('package/new.html.twig', [
                'form' => $form,
            ]);
        }
    }
}
