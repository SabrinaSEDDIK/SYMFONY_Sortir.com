<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Campus;
use App\Entity\Sortie;
use App\Form\AnnulationSortieType;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Entity\Participant;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie/liste", name="sortie_liste")
     */
    public function liste(): Response
    {

        return $this->render('sortie/liste.html.twig');
    }

    /**
     * @Route("/sortie/creer", name="sortie_creer")
     */
    public function creer(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository, ParticipantRepository $participantRepository): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);

         // hydrater l'entité avec les données de la requête
        $sortieForm ->handleRequest($request);

        if ($sortieForm ->isSubmitted() && $sortieForm->isValid())
        {
            $sortie = $sortieForm->getData();
            dump($request);
            if($request->request->get('valider') == "Enregistrer sans publier")
            {
                $sortie->setEtat($etatRepository->findOneById(1)); //"Créée"
            }
            else
            {
                $sortie->setEtat($etatRepository->findOneById(2)); // "Ouverte"
            }
            $sortie->setOrganisateur($this->getUser()); //l'organisateur est l'utilisateur
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_details', ['id'=>$sortie->getId()]);
        }

        return $this->render('sortie/creer.html.twig', [
            'sortieForm'=>$sortieForm->createView(),
            'sortie' => $sortie
        ]);
    }

    /**
     * @Route("/sortie/publier/{id}", name="sortie_publier")
     */
    public function publier($id, SortieRepository $sortieRepository, EtatRepository $etatRepository,
                            EntityManagerInterface  $entityManager): Response
    {
        $sortie = $sortieRepository->findOneById($id);
        if(!$sortie){
            return $this->render('erreurs/404.html.twig');
        }
        if ($this->getUser() != $sortie->getOrganisateur())
        {
            $this->addFlash('fail', 'Vous ne pouvez publier que les sorties que vous avez organisées');
            return $this->redirectToRoute('sortie_details', ['id'=>$id]);
        }
        if ($sortie->getEtat()->getId()!=1){
            $this->addFlash('fail', 'Seules les sorties enregistrées peuvent être publiées');
            return $this->redirectToRoute('sortie_details', ['id'=>$id]);
        }

        $sortie->setEtat($etatRepository->findOneById(2)); //Ouverte
        $entityManager->persist($sortie);
        $entityManager->flush();
        $this->addFlash('success', 'Sortie publiée, les inscriptions sont ouvertes');
        return $this->redirectToRoute('sortie_details', ['id'=>$id]);

    }

    /**
     * @Route("/sortie/inscrire/{id}", name="sortie_inscrire")
     */
    public function inscrire(int $id,
                             SortieRepository $sortieRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser();
        // Si la sortie demandée existe
        if($sortie != null){
            $dateActuelle = new \DateTime();
            // Si la sortie demandée est ouverte, que la date limite d'inscription n'est pas passée
            // Et qu'il reste des places
            if($sortie->getEtat()->getLibelle() === 'Ouverte' && $sortie->getDateLimiteInscription() > $dateActuelle){
                // On vérifie qu'il reste des places
                if(count($sortie->getParticipants()) < $sortie->getNbInscriptionsMax()){
                    // On vérifie que la personne qui essaie de s'inscrire ne soit pas déjà inscrite
                    if($sortie->getParticipants()->contains($user)){
                        $this->addFlash('fail', "Vous êtes déjà inscrit à cette sortie");
                        return $this->redirectToRoute('sortie_details', ['id'=>$id]);
                    }
                    // Si elle n'est pas inscrite, on l'ajoute aux participants et on persiste
                    else{
                        $sortie->addParticipant($user);
                        $entityManager->persist($sortie);
                        $entityManager->flush();
                        $this->addFlash('success', "Vous êtes bien inscrit à cette sortie.");
                        return $this->redirectToRoute('sortie_details', ['id'=>$id]);
                    }
                } else{
                    $this->addFlash('fail', "La sortie est complète. Désolé");
                    return $this->redirectToRoute('sortie_details', ['id'=>$id]);
                }


            }
            else{
                // Si la sortie n'est pas ouverte, on l'indique par un message flash
                $this->addFlash('fail', "Vous ne pouvez pas vous inscrire à cette sortie, elle n'est pas ouverte à l'inscription");
                return $this->redirectToRoute('sortie_details', ['id'=>$id]);
            }
        }
        else{
            // Si la sortie n'existe pas, on l'indique par un message flash
            $this->addFlash('fail', "Cette sortie n'existe pas");
            return $this->redirectToRoute('main_home');
        }
    }

    /**
     * @Route("/sortie/desister/{id}", name="sortie_desister")
     */
    public function desister(int $id,
                             SortieRepository $sortieRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser();
        // Si la sortie demandée existe
        if($sortie != null){
            // On vérifie que l'on peut encore se désister
            if($sortie->getEtat()->getLibelle() === 'Ouverte' || $sortie->getEtat()->getLibelle() === 'Clôturée'){
                // On vérifie que la personne qui essaie de s'inscrire ne soit pas déjà inscrite
                if($sortie->getParticipants()->contains($user)) {
                    $sortie->removeParticipant($user);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $this->addFlash('success', "Vous êtes bien désinscrit de cette sortie.");
                    return $this->redirectToRoute('sortie_details', ['id'=>$id]);
                }
                // Si elle n'est pas inscrite, elle ne peut pas se désister
                else{
                    $this->addFlash('fail', "Vous n'est pas inscrit à cette sortie. Impossible de se désister");
                    return $this->redirectToRoute('sortie_details', ['id'=>$id]);
                }
            }
            // Si elle n'est ni ouverte, ni clôturée, on ne peut pas se désister
            else{
                $this->addFlash('fail', "Il n'est plus possible de se désinscrire de cette sortie.");
                return $this->redirectToRoute('sortie_details', ['id'=>$id]);
            }
        } else{
            // Si la sortie n'existe pas, on l'indique par un message flash
            $this->addFlash('fail', "Cette sortie n'existe pas");
            return $this->redirectToRoute('main_home');
        }

    }


    /**
     * @Route("/sortie/modifier/{id}", name="sortie_modifier")
     */
    public function modifier(int $id, SortieRepository $sortieRepository, EtatRepository $etatRepository, Request $request, EntityManagerInterface $entityManager): Response
    {

        $sortie = $sortieRepository->find($id);

        if(!$sortie){
            return $this->render('erreurs/404.html.twig');
        }
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        // hydrater l'entité avec les données de la requête
        $sortieForm ->handleRequest($request);

        if ($sortieForm ->isSubmitted() && $sortieForm->isValid())
        {

            $sortie = $sortieForm->getData();
            dump($request);
            if($request->request->get('valider') == "Enregistrer sans publier")
            {
                $sortie->setEtat($etatRepository->findOneById(1)); //"Créée"
            }
            else
            {
                $sortie->setEtat($etatRepository->findOneById(2)); // "Ouverte"
            }
            $sortie->setOrganisateur($this->getUser()); //l'organisateur est l'utilisateur
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_details', ['id'=>$sortie->getId()]);

        }
        return $this->render('sortie/modifier.html.twig', [
            'sortieForm'=>$sortieForm->createView(),
            'sortie' => $sortie
        ]);
    }

    /**
     * @Route("/sortie/annuler/{id}", name="sortie_annuler")
     */
    public function annuler(int $id,
                            SortieRepository $sortieRepository,
                            EtatRepository $etatRepository,
                            Request $request,
                            EntityManagerInterface $entityManager): Response
    {
        // On récupère la sortie affichée
        $sortie = $sortieRepository->find($id);
        // On récupère l'utilisateur
        $user = $this->getUser();

        // Si la personne qui arrive sur cette page n'est pas l'organisateur de cette sortie
        // Ou si la sortie n'existe pas
        // on le renvoie vers la page d'accueil
        if(!$sortie || ($sortie->getOrganisateur()->getEmail() !== $user->getUsername() && !in_array( 'ROLE_ADMIN' , $user->getRoles() , false ))){ // TODO : Ou granted admin
            $this->addFlash(
                'fail',
                "La sortie n'existe pas ou vous n'êtes pas l'organisateur/trice de cette sortie."
            );
            return $this->redirectToRoute('main_home');
        }
        // Sinon, on  vérifie que la sortie est ouverte ou clôturée pour pouvoir l'annuler
        // On ajoute le motif et on passe la sortie à Annulée
        else{
            if($sortie->getEtat()->getLibelle() === 'Ouverte' || $sortie->getEtat()->getLibelle() === 'Clôturée'){
                // On met de côté la description de la sortie
                $infosSortie = $sortie->getInfosSortie();

                $AnnulationSortieForm = $this->createForm(AnnulationSortieType::class, $sortie);

                // Utilisation de la vrai et d'une fausse date limite d'inscription
                // Pour pouvoir annuler une sortie dont la date limite d'inscription est dépassée
                // Car les contraintes sur les champs dans l'entité bloque le handleRequest sinon
                $vraiDateLimiteInscription = $sortie->getDateLimiteInscription();
                $fausseDateLimiteInscription = new \DateTime("tomorrow");
                $sortie->setDateLimiteInscription($fausseDateLimiteInscription);

                // Hydratation de l'entité avec les données de la requête
                $AnnulationSortieForm ->handleRequest($request);

                // Après le handleRequest, on remet la vrai date limite d'inscription
                $sortie->setDateLimiteInscription($vraiDateLimiteInscription);

                if ($AnnulationSortieForm ->isSubmitted() && $AnnulationSortieForm->isValid())
                {
                    $dateActuelle = new \DateTime();
                    $diff = $dateActuelle->diff($sortie->getDateHeureDebut());
                    // Si la sortie est dans plus de 24h, on peut l'annuler

                    if($diff->days > 1){

                        $etatAnnulee = $etatRepository->findBy(['libelle' => 'Annulée']);
                        $sortie->setInfosSortie($infosSortie .' - Motif d\'Annulation : '.$sortie->getInfosSortie());
                        $sortie->setEtat($etatAnnulee[0]);
                        $entityManager->persist($sortie);
                        $entityManager->flush();

                        $this->addFlash('success', 'La sortie a été annulée avec succès !');

                        return $this->redirectToRoute('sortie_details', [
                            'id'=>$sortie->getId()
                        ]);
                    }
                    else {
                        $this->addFlash(
                            'fail',
                            "La sortie est dans moins de 24h, vous ne pouvez plus l'annuler !"
                        );
                        return $this->redirectToRoute('main_home');
                    }

                }
                // Si l'on peut l'annulée et que l'on n'a pas encore validé le formulaire
                // On renvoie vers la page d'annulation avec le formulaire.
                return $this->render('sortie/annuler.html.twig', [
                    'annulationSortieForm' => $AnnulationSortieForm->createView(),
                    'sortie' => $sortie
                ]);
            } else{
                $this->addFlash(
                    'fail',
                    "Vous ne pouvez pas annuler cette sortie !"
                );
                return $this->redirectToRoute('main_home');
            }

        }
    }

    /**
     * @Route("/sortie/{id}", name="sortie_details")
     */
    public function details(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        if(!$sortie){
            return $this->render('erreurs/404.html.twig');
        }
        $dateActuelle = new \DateTime();
        $dureeArchivage = new DateInterval('P1M');
        $dateLimiteArchivage = clone($dateActuelle)->sub($dureeArchivage);

        if($sortie->getDateHeureDebut() < $dateLimiteArchivage){
            $this->addFlash(
                'fail',
                "La sortie est archivée, vous ne pouvez plus la consulter"
            );
            return $this->redirectToRoute('main_home');
        } else{
            return $this->render('sortie/details.html.twig', compact("sortie"));
        }


    }

    /**
     * @Route("/sortie/supprimer/{id}", name="sortie_supprimer")
     */
    public function supprimer(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->findOneById($id);
        if(!$sortie){
            return $this->render('erreurs/404.html.twig');
        }
        dump($this->getUser(), $sortie->getOrganisateur());
        //Conditions pour supprimer une sortie : être son organisateur +  sortie à l'état créée
        if($this->getUser() == $sortie->getOrganisateur() && $sortie->getEtat()->getId()==1){
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success','La sortie a été supprimée avec succès');
        }else{
            if($sortie->getEtat()->getId()!=1){
                $this->addFlash('error',"Vous ne pouvez pas supprimer une sortie publiée");
            }else{
                $this->addFlash('error',"Vous ne disposez pas des droits requis");
            }

        }

        return $this->redirectToRoute('main_home');
    }
}
