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
        return $this->render('package/index.html.twig', [
            'packages' => $packageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_package_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $package = new Package();
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
        return $this->render('package/view.html.twig', [
            'package' => $package,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_package_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Package $package, EntityManagerInterface $entityManager): Response
    {
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
        $entityManager->remove($package);
        $entityManager->flush();

        return $this->redirectToRoute('app_package');
    }
}
