<?php

namespace App\Controller;

use App\Entity\RechercheSortie;
use App\Entity\Sortie;
use App\Form\RechercheSortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/sortie", name="main_home")
     */
    public function index(SortieRepository $sortieRepository,
                          EntityManagerInterface $entityManager,
                          Request $request): Response
    {
        $user = $this->getUser();

        $rechercheSortie = new RechercheSortie();
        $sortieParticipants = null;
        $formulaireListeAccueil = $this->createForm(RechercheSortieType::class, $rechercheSortie);

        // Hydratation de l'entité avec les données de la requête
        $formulaireListeAccueil ->handleRequest($request);

        if ($formulaireListeAccueil ->isSubmitted() && $formulaireListeAccueil->isValid()) {
            $sorties = $sortieRepository->rechercheAccueilSortie($rechercheSortie, $user);
            $participants = $sortieRepository->rechercheParticipantAccueilSortie($rechercheSortie, $user);
        } else{
            $sorties = $sortieRepository->rechercheAccueilSortie($rechercheSortie, $user);
            $participants = $sortieRepository->rechercheParticipantAccueilSortie($rechercheSortie, $user);
        }

        for($i = 0; $i < count($participants); $i++){
            if($user != null && $participants[$i]['idParticipant'] == $user->getId()){
                $sortieParticipants[$participants[$i]['idSortie']] = true ;
            }
        }

        return $this->render('main/index.html.twig', [
            'formulaireListeAccueil' => $formulaireListeAccueil->createView(),
            'sorties' => $sorties,
            'sortieParticipants' => $sortieParticipants
        ]);
    }



    /**
     * @Route("/", name="main_base")
     * Fonction permettant de rediriger lorsque l'on ne rentre rien dans l'url après public
     */
    public function redirectBase(SortieRepository $sortieRepository,
                          EntityManagerInterface $entityManager,
                          Request $request): Response
    {

        return $this->redirectToRoute('main_home');
    }


}
