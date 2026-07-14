<?php

namespace App\Controller;

use App\DTO\PackageSearchFilter;
use App\Entity\Package;
use App\Form\PackageFiltersType;
use App\Form\PackageFormType;
use App\Repository\PackageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/package')]
final class PackageController extends AbstractController
{
    #[Route('/', name: 'app_package', methods: ['GET'])]
    public function index(Request $request, PackageRepository $packageRepository): Response
    {
        $filter = new PackageSearchFilter();
        $form = $this->createForm(PackageFiltersType::class, $filter);
        $form->handleRequest($request);

        $preferredCategories = null;
        if ($this->isGranted('ROLE_CONSUMER')) {
            $preferredCategories = $this->getUser()->getConsumer()->getPreferredCategories();
        }

        $myBusiness = null;
        if ($this->isGranted('ROLE_BUSINESS')) {
            $myBusiness = $this->getUser()->getBusiness();
        }

        return $this->render('package/index.html.twig', [
            'packages' => $packageRepository->findByFilter($filter, $preferredCategories, $myBusiness),
            'package_filter_form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_package_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_BUSINESS');

        $package = new Package();

        $business = $this->getUser()->getBusiness();
        $package->setBusiness($business);

        $form = $this->createForm(PackageFormType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($package);
            $entityManager->flush();

            $fans = $userRepository->createQueryBuilder('u')
                ->join('u.consumer', 'c')
                ->join('c.favorite_businesses', 'b')
                ->where('b = :business')
                ->setParameter('business', $business)
                ->getQuery()
                ->getResult();

            $packageUrl = $urlGenerator->generate('app_package_view', ['id' => $package->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            foreach ($fans as $fan) {
                $email = (new Email())
                    ->from('alerts@foodrescue.com')
                    ->to($fan->getEmail())
                    ->subject('🚨 New Surprise Bag from ' . $business->getName() . '!')
                    ->html(sprintf(
                        '<p>Great news! Your favorite store just posted a new package: <strong>%s</strong>.</p>
                         <p><a href="%s">Order it now before it sells out!</a></p>',
                        $package->getName(),
                        $packageUrl
                    ));

                $mailer->send($email);
            }

            $this->addFlash('success', 'Package created and fans notified!');
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

    #[Route('/favorites', name: 'app_package_favorites', methods: ['GET'])]
    public function favorites(PackageRepository $packageRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CONSUMER');

        $consumer = $this->getUser()->getConsumer();
        $favoriteBusinesses = $consumer->getFavoriteBusinesses();

        $packages = [];

        if (!$favoriteBusinesses->isEmpty()) {
            $packages = $packageRepository->createQueryBuilder('p')
                ->where('p.business IN (:businesses)')
                ->setParameter('businesses', $favoriteBusinesses)
                ->andWhere('NOT EXISTS (SELECT 1 FROM App\Entity\Order o WHERE o.package = p)') // Available only
                ->getQuery()
                ->getResult();
        }

        return $this->render('package/favorites.html.twig', [
            'packages' => $packages,
        ]);
    }

    #[Route('/live-feed', name: 'app_package_live_feed', methods: ['GET'])]
    public function liveFeed(Request $request, PackageRepository $packageRepository): Response
    {
        $filter = new PackageSearchFilter();
        $form = $this->createForm(PackageFiltersType::class, $filter);
        $form->handleRequest($request);

        $preferredCategories = null;
        if ($this->isGranted('ROLE_CONSUMER')) {
            $preferredCategories = $this->getUser()->getConsumer()->getPreferredCategories();
        }

        $myBusiness = null;
        if ($this->isGranted('ROLE_BUSINESS')) {
            $myBusiness = $this->getUser()->getBusiness();
        }

        return $this->render('package/feed.html.twig', [
            'packages' => $packageRepository->findByFilter($filter, $preferredCategories, $myBusiness),
        ]);
    }
    #[Route('/favorites/live-feed', name: 'app_package_favorites_live', methods: ['GET'])]
    public function favoritesLiveFeed(PackageRepository $packageRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CONSUMER');

        $consumer = $this->getUser()->getConsumer();
        $favoriteBusinesses = $consumer->getFavoriteBusinesses();

        $packages = [];

        if (!$favoriteBusinesses->isEmpty()) {
            $packages = $packageRepository->createQueryBuilder('p')
                ->where('p.business IN (:businesses)')
                ->setParameter('businesses', $favoriteBusinesses)
                ->andWhere('NOT EXISTS (SELECT 1 FROM App\Entity\Order o WHERE o.package = p)')
                ->getQuery()
                ->getResult();
        }

        // Return JUST the fragment, not the whole page!
        return $this->render('package/favorites_feed.html.twig', [
            'packages' => $packages,
        ]);
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
