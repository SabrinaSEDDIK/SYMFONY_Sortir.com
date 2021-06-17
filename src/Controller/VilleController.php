<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\RechercheVilleType;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
    /**
     * @Route("/admin/ville/gestion", name="ville_gestion")
     * @Route("/admin/ville/modifier/{id}", name="ville_modifier")
     */
    public function gestion(Ville $ville=null, VilleRepository $villeRepository, Request $request,
                            EntityManagerInterface $entityManager): Response
    {

        $villes = $villeRepository->findBy([], ['nom' => 'ASC']);


        if(!$ville){
            $ville = new Ville();
        }

        $edit = $ville->getId() != null;

        $villeForm = $this->createForm(VilleType::class, $ville);

        $villeForm->handleRequest($request);


        if($villeForm->isSubmitted()&&$villeForm->isValid()){
            $entityManager->persist($ville);
            $entityManager->flush();
            if(!$edit){
                $this->addFlash('success', 'Ville ajoutée avec succès');
            }
            else{
                $this->addFlash('success', 'Ville modifiée avec succès');
            }
            return $this->redirectToRoute('ville_gestion');
        }

        return $this->render('ville/liste.html.twig', [
            "villes"=>$villes,
            "villeForm"=>$villeForm->createView(),
            "edit"=>$edit
        ]);
    }
    /**
     * @Route("/admin/ville/supprimer/{id}", name="ville_supprimer")
     */
    public function supprimer(Ville $ville, EntityManagerInterface $entityManager): Response
    {
        if($ville->getLieux()->getKeys()){
            $this->addFlash('fail', 'La ville est associée à un lieu de sortie');
            return $this->redirectToRoute("ville_gestion");
        }
        $entityManager->remove($ville);
        $entityManager->flush();
        $this->addFlash('success', 'Ville supprimée avec succès');
        return $this->redirectToRoute("ville_gestion");

    }
    /**
     * @Route("/admin/ville/rechercher", name="ville_rechercher")
     */
    public function rechercher(VilleRepository $villeRepository, Request $request): Response
    {
       $recherche = trim($request->query->get('nom'));
       $villesTrouvees = $villeRepository->findLike($recherche);
       $ville = new Ville();
       $villeForm = $this->createForm(VilleType::class, $ville);
       return $this->render('ville/liste.html.twig', [
           "villes"=>$villesTrouvees,
           "villeForm"=>$villeForm->createView(),
           "edit"=>false,
           "search"=>$recherche
       ]);

    }
}
