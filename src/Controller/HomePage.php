<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomePage extends AbstractController
{
    #[Route('/')]
    public function home(): Response
    {
        return $this->render('mainPage.html.twig');
    }
}
