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

#[Route('/business')]
final class BusinessController extends AbstractController
{
    #[Route('', name: 'app_business', methods: ['GET'])]
    public function index(BusinessRepository $businessRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('business/index.html.twig', [
            'businesses' => $businessRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_business_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $business = new Business();
        $form = $this->createForm(BusinessFormType::class, $business);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($business);
            $entityManager->flush();

            return $this->redirectToRoute('app_business');
        }

        return $this->render('business/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_business_view', methods: ['GET'])]
    public function view(Business $business): Response
    {
        return $this->render('business/view.html.twig', [
            'business' => $business,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_business_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Business $business, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BusinessFormType::class, $business);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_business');
        }

        return $this->render('business/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/remove', name: 'app_business_del', methods: ['GET'])]
    public function remove(Business $business, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($business);
        $entityManager->flush();

        return $this->redirectToRoute('app_business');
    }

    #[Route('/{id}/add_package', name: 'app_business_add_package', methods: ['GET', 'POST'])]
    public function addPackage(Request $request, Business $business, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BUSINESS');

        if ($this->getUser()->getBusiness() !== $business) {
            throw $this->createAccessDeniedException('Nice try! You can only add packages to your own business.');
        }

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
