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

final class PackageController extends AbstractController
{
    #[Route('/package', name: 'app_package')]
    public function index(PackageRepository $packageRepository): Response
    {
        $packages = $packageRepository->findAll();

        return $this->render('package/index.html.twig', [
            'packages' => $packages,
        ]);
    }

    #[Route('/package/{id}', name: 'app_package_view')]
    public function view(Package $package): Response
    {
        return $this->render('package/view.html.twig', [
            'package' => $package,
        ]);
    }

}
