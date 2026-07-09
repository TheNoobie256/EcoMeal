<?php

namespace App\Controller;

use App\Entity\Package;
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
    public function index(PackageRepository $packageRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('package/index.html.twig', [
            'packages' => $packageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_package_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_BUSINESS') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only businesses can create packages.');
        }

        $package = new Package();

        if ($this->isGranted('ROLE_BUSINESS')) {
            $package->setBusiness($this->getUser()->getBusiness());
        }

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

    #[Route('/{id}', name: 'app_package_view', methods: ['GET'])]
    public function view(Package $package): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('package/view.html.twig', [
            'package' => $package,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_package_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Package $package, EntityManagerInterface $entityManager): Response
    {
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
            // Consumers hit this block
            throw $this->createAccessDeniedException('You do not have permission to modify packages.');
        }
    }
}
