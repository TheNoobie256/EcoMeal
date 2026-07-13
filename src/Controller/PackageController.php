<?php

namespace App\Controller;

use App\DTO\PackageSearchFilter;
use App\Entity\Package;
use App\Form\PackageFiltersType;
use App\Form\PackageFormType;
use App\Repository\PackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/package')]
final class PackageController extends AbstractController
{
    #[Route('', name: 'app_package', methods: ['GET'])]
    public function index(Request $request, PackageRepository $packageRepository): Response
    {
        $filter = new PackageSearchFilter();
        $form = $this->createForm(PackageFiltersType::class, $filter);
        $form->handleRequest($request);

        return $this->render('package/index.html.twig', [
            'packages' => $packageRepository->findByFilter($filter),
            'package_filter_form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_package_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BUSINESS');

        $package = new Package();
        $package->setBusiness($this->getUser()->getBusiness());

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

    #[Route('/{id}/order-direct', name: 'app_package_order_direct', methods: ['POST'])]
    public function orderDirect(Package $package, EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_BUSINESS')) {
            throw $this->createAccessDeniedException('Only registered consumers can place orders.');
        }

        $this->denyAccessUnlessGranted('ROLE_CONSUMER');

        $order = new \App\Entity\Order();
        $order->setPackage($package);
        $order->setConsumer($this->getUser()->getConsumer());

        $entityManager->persist($order);
        $entityManager->flush();

        $this->addFlash('success', 'Your order for ' . $package->getName() . ' has been placed successfully!');

        return $this->redirectToRoute('app_order');
    }

    #[Route('/{id}', name: 'app_package_view', methods: ['GET'])]
    public function view(Package $package, \App\Repository\OrderRepository $orderRepository): Response
    {
        $orderExists = $orderRepository->findOneBy(['package' => $package]);
        $isAvailable = !$orderExists;

        return $this->render('package/view.html.twig', [
            'package' => $package,
            'isAvailable' => $isAvailable,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_package_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Package $package, EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Admins cannot edit packages. Only the business owner can do this.');
        }

        $this->checkPackageAccess($package);

        $form = $this->createForm(PackageFormType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_package');
        }

        return $this->render('package/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/remove', name: 'app_package_del', methods: ['GET'])]
    public function remove(Package $package, EntityManagerInterface $entityManager): Response
    {
        $this->checkPackageAccess($package);

        $entityManager->remove($package);
        $entityManager->flush();

        return $this->redirectToRoute('app_package');
    }

    private function checkPackageAccess(Package $package): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        if ($this->isGranted('ROLE_BUSINESS')) {
            if ($package->getBusiness() !== $this->getUser()->getBusiness()) {
                throw $this->createAccessDeniedException('You can only modify your own packages.');
            }
        } else {
            throw $this->createAccessDeniedException('You do not have permission to modify packages.');
        }
    }
}
