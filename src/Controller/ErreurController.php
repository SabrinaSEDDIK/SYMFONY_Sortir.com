<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErreurController extends AbstractController
{
    /**
     * @Route("/erreur", name="erreur_403")
     */
    public function index(): Response
    {

        return $this->render('erreurs/403.html.twig', [
            'controller_name' => 'ErreurController',
        ]);
    }
}
