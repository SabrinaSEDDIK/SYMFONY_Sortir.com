<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\AdminCampusType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * Affiche, ajoute et modifie les campus
     * @Route("/campus", name="campus")
     * @Route("/campus/modifier/{id}", name="campusModifier")
     */
    public function gestionCampus(
        CampusRepository $campusRepository,
        Campus $campus = null,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {

        $lesCampus = $campusRepository->findBy([], ['nom' => 'ASC']);

        if(!$campus){
            $campus = new Campus();
        }

        $edit = $campus->getId() != null;

        $adminCampusForm= $this->createForm(AdminCampusType::class,$campus);

        $adminCampusForm->handleRequest($request);

        if($adminCampusForm->isSubmitted()&&$adminCampusForm->isValid()){

            $entityManager->persist($campus);
            $entityManager->flush();
            if(!$edit){
                $this->addFlash('success', 'Campus ajoutée avec succès');
            }
            else{
                $this->addFlash('success', 'Campus modifiée avec succès');
            }
            return $this->redirectToRoute('admin_campus');
        }

        return $this->render('admin/adminCampus.html.twig', [
            'adminCampus' => $lesCampus,
            'adminCampusForm' => $adminCampusForm->createView(),
            'edit' => $edit
        ]);
    }


    /**
     * Supprime un campus
     * @param Campus $campus
     * @Route("/campusSupprimer/{id}", name="campusSupprimer")
     * @return RedirectResponse
     */
    public function supprimerCampus(
        EntityManagerInterface $entityManager,
        Campus $campus
    ): RedirectResponse
    {

            $entityManager->remove($campus);
            $entityManager->flush();

            $this->addFlash('success', 'Le campus a été supprimée !');

        return $this->redirectToRoute('admin_campus');
    }

    /**
     * Recherche un campus
     * @Route("/campus/rechercher", name="campus_rechercher")
     */
    public function rechercher(CampusRepository $villeRepository, Request $request): Response
    {
        $recherche = trim($request->query->get('nom'));
        $campusTrouvees = $villeRepository->findLike($recherche);
        $campus = new Campus();
        $campusForm = $this->createForm(AdminCampusType::class, $campus);
        return $this->render('admin/adminCampus.html.twig', [
            "adminCampus"=>$campusTrouvees,
            "adminCampusForm"=>$campusForm->createView(),
            "edit"=>false,
            "search"=>$recherche
        ]);

    }
//    /**
//     * Modifie un campus
//     * @Route("/campusModifier/{id}",requirements={"id"="\d+"}, name="campusModifier")
//     */
//    public function modifierCampus(
//        EntityManagerInterface $entityManager,
//        Request $request,
//        CampusRepository $campusRepository,
//        int $id
//    ): Response{
//
//        //On préremplie le champ avec le nom du campus que l'on souhaite modifier
//        $campus=$campusRepository->find($id);
//
//        //Création du formulaire
//        $campusForm=$this->createForm(AdminCampusType::class,$campus);
//        $campusForm->handleRequest($request);
//
//        if($campusForm->isSubmitted()&&$campusForm->isValid()){
//            $entityManager->persist($campus);
//            $entityManager->flush();
//            $this->addFlash('success','Campus modifié');
//            return $this->redirectToRoute('admin_campus');
//        }
//        return$this->render('admin/adminCampusModifier.html.twig',[
//            'modifierCampusForm'=>$campusForm->createView(),
//        ]);
//    }

//    /**
//     * Ajoute un campus
//     * @Route("/campus/{id}", name="campusCreer")
//     */
//    public function ajouterCampus(
//        EntityManagerInterface $entityManager,
//        Request $request
//    ): Response
//    {
//
//        $campus = new Campus();
//        $campus->setNom('Nom du campus');
//        $campusForm = $this->createForm(AdminCampusType::class,$campus);
//        $campusForm->handleRequest($request);
//        if($campusForm->isSubmitted()&&$campusForm->isValid()){
//            $entityManager->persist($campus);
//            $entityManager->flush();
//            $this->addFlash('success', 'Le campus a été créé !');
//            return $this->redirectToRoute('admin_campus');
//
//        }
//        return $this->render('admin/adminCampusCreer.html.twig',[
//            'ajouterCampusForm'=>$campusForm->createView()
//        ]);
//    }
}