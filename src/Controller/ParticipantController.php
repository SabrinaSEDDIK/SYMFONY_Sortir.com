<?php

namespace App\Controller;


use App\Entity\Images;
use App\Entity\Participant;
use App\Entity\Ville;
use App\Form\AjoutUserType;
use App\Form\GestionProfilType;
use App\Form\VilleType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;



/**
 * @Route("/", name="participant_")
 */
class ParticipantController extends AbstractController
{
    /**
     * Modifier son profil
     * @Route("monprofil", name="monprofil")
     */
    public function monProfil(
        Request $request,
        EntityManagerInterface $entityManager,
        ParticipantRepository $participantRepository,
        Security $security,
        UserPasswordEncoderInterface $userPasswordEncoder
    ): Response
    {
        //Instanciation
        $user = $security->getUser();
        //On récupère l'utilisateur et on préremplie chaque champs avec les informations du participant
        $gestionProfil = $participantRepository->findOneBy(['email'=>$user->getUsername()]);

        //On récupère tous les profils pour les comparer plus tard (vérification mail et pseudo unique)
        $listProfil = $participantRepository->findAll();
        //On génère une instance du formulaire avec le $this->createForm
        // 1er argument: nom complet de classe, 2ème argument: entité associée au formulaire
        $gestionProfilForm = $this->createForm(GestionProfilType::class,$gestionProfil);

        //Prendre les données soumises et injecter dans $gestionProfilForm
        $gestionProfilForm->handleRequest($request);

        //Haschage du MDP
        $HashmotDePasse = $userPasswordEncoder->encodePassword($gestionProfil,$gestionProfil->getPassword());
        $gestionProfil->setPassword($HashmotDePasse);

            //On vérifie que le formulaire est remplie et que les données sont valides avant de le soumettre à la bdd
            if($gestionProfilForm->isSubmitted() && $gestionProfilForm->isValid()) {
                //On vérifie que le pseudo et l'email ne se trouvent pas déjà dans la bdd
                //On passe en revue tous les profils
                foreach($listProfil as $profil) {
                    //On compare tous les emails et tous les pseudos entre eux
                   if($profil->getEmail() === $gestionProfil->getEmail() || $profil->getPseudo() === $gestionProfil->getPseudo()){
                       //On saute l'ID du même utilisateur car sinon on rentre dans la boucle
                       if($gestionProfil->getId() != $profil->getId()) {
                       $this->addFlash('fail','Le pseudo ou l\'email existe déjà, trouvez autre chose !');
                       $entityManager->refresh($gestionProfil);
                       return $this->redirectToRoute('participant_monprofil');
                       }
                   }
                }

                /*
                 * ##########################################
                 * ########### GESTION DE L'IMAGE ###########
                 * ##########################################
                 */
                // On récupère l'image transmise
                $image = $gestionProfilForm->get('image')->getData();

                if($image){
                    // On génère un nouveau nom de fichier
                    $fichier = md5(uniqid()). '.'. $image->guessExtension();

                    // On copie le fichier dans le dossier img/uploads
                    $image->move(
                        $this->getParameter('images_directory'),
                        $fichier
                    );

                    // On crée l'objet contenant l'image qui va être stocké
                    // Si l'utilisateur a déjà une image, on récupère l'ancienne
                    // S'il n'en a pas, on créé un nouvel objet Images
                    $imageStockee = $gestionProfil->getImage() == null ? new Images() : $gestionProfil->getImage();

                    // Si l'image qui va être stockée a déjà un id
                    // C'est qu'elle existe déjà, et donc il faut supprimer l'ancienne du serveur
                    if($imageStockee->getId() != null){
                        // On reconstruit le nom complet de l'image
                        $nomImage = $this->getParameter('images_directory').'/'.$imageStockee->getNom();

                        // Si l'image existe sur le serveur, on la supprime
                        if(file_exists($nomImage)){
                            unlink($nomImage);
                        }
                    }

                    // On remplace le nom de l'image et on remplace l'image dans l'objet Participant
                    $imageStockee->setNom($fichier);
                    $gestionProfil->setImage($imageStockee);
                }

                $entityManager->persist($gestionProfil);
                $entityManager->flush();
                $this->addFlash('success','Profil modifié !');
                return $this->redirectToRoute('participant_profil',['id' => $gestionProfil->getId()]);
            }

        // On évite la déconnexion dès qu'on fait appel à une autre url avec un refresh
        // car sinon Symfony possède un objet utilisateur qui n'est plus en phase avec sa session
        $entityManager->refresh($gestionProfil);
        //On reste sur la même page à la fin
        return $this->render('participant/monProfil.html.twig', [
            'affichageProfilForm' => $gestionProfilForm->createView()
        ]);
    }

    /**
     * Afficher le profil d'un participant
     * @Route("profil/{id}",name="profil")
     */
    public function profil(
        int $id,
        ParticipantRepository $participantRepository,
        CampusRepository $campusRepository
    ):Response
    {

        //On récupère les informations du profil demandé ainsi que les infos du campus associé via l'ID
        $profil = $participantRepository->find($id);
        //On recherche le numéro de campus de la table participant car l'id précédent n'est pas le même
        $campusId=$profil->getCampus();

        //On recherche les infos du campus
        $campus = $campusRepository->find($campusId);

        return $this->render('participant/profil.html.twig',[
            'profil' => $profil,
            'campus' => $campus

        ]);
    }

    /**
     * @Route("/admin/users/", name="liste")
     */
    public function liste(ParticipantRepository $participantRepository): Response
    {
        $etudiants = $participantRepository->findBy([], ['nom' => 'ASC']);

        return $this->render('etudiant/liste.html.twig', [
            'etudiants'=>$etudiants
        ]);

    }

    /**
     * @Route("/admin/users/ajouter/", name="ajouter")
     */
    public function ajouter(EntityManagerInterface $entityManager,
                            Request $request,
                            UserPasswordEncoderInterface $userPasswordEncoder): Response
    {
        $participant = new Participant();

        $participantForm = $this->createForm(AjoutUserType::class, $participant);

        //première hydratation de $participant
        $HashmotDePasse = $userPasswordEncoder->encodePassword($participant,'Pa$$w0rd');
        $participant->setPassword($HashmotDePasse);
        //il hydrate en vérifiant les contraintes
        $participantForm->handleRequest($request);


        if($participantForm->isSubmitted()&&$participantForm->isValid()){
            //hydratation des attributs non demandés dans le formulaire
            if($participant->getAdministrateur()){
                $participant->setRoles(['ROLE_ADMIN']);
            }
            $participant->setActif(true);
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'L\'enregistrement a été effectué avec succès');
            return $this->redirectToRoute('participant_liste');

        }

        return $this->render('etudiant/ajouter.html.twig', [
            'participantForm'=>$participantForm->createView()
        ]);

    }
    /**
     * @Route("/admin/users/ajouter/csv", name="ajouter_csv")
     */
    public function ajouterCSV(Request $request,
                               EntityManagerInterface $entityManager,
                               CampusRepository $campusRepository): Response
    {

        //récupérer des données au format string
        $fichier = file_get_contents($request->files->get('fichier'));

        //préparer le serializer
        $normalizers = [new ObjectNormalizer()];
        $encoders = [new CsvEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        //récupérer un tableau
        $utilisateurs = $serializer->decode($fichier,'csv');

        //construire les instances de Participant et les insérer en bdd
        foreach($utilisateurs as $utilisateur){
            $participant = new Participant();
            $participant->setCampus($campusRepository->findOneById($utilisateur['campus_id']));
            $participant->setNom($utilisateur['nom']);
            $participant->setPrenom($utilisateur['prenom']);
            $participant->setTelephone($utilisateur['telephone']);
            if($utilisateur['administrateur']==1){
                $participant->setAdministrateur(true);
                $participant->setRoles(['ROLE_ADMIN']);
            }else{
                $participant->setAdministrateur(false);
            }
            if($utilisateur['actif']==1){
                $participant->setActif(true);
            }else{
                $participant->setActif(false);
            }
            $participant->setEmail($utilisateur['email']);
            $participant->setPassword($utilisateur['password']);
            $participant->setPseudo($utilisateur['pseudo']);

            $entityManager->persist($participant);
            $entityManager->flush();
        }

        $this->addFlash('success', 'Les utilisateurs ont été enregistrés en base de donnée avec succès');

        return $this->redirectToRoute('participant_liste');
    }



    /**
     * @Route("/admin/users/supprimer/{id}", name="supprimer")
     */
    public function supprimer(Participant $participant,
                              EntityManagerInterface $entityManager):Response
    {
        $entityManager->remove($participant);
        $entityManager->flush();
        $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès');
        return $this->redirectToRoute('participant_liste');

    }

    /**
     * @Route("/admin/users/rechercher", name="rechercher")
     */
    public function rechercher(Request $request,
                               ParticipantRepository $participantRepository,
                               EntityManagerInterface $entityManager): Response
    {

        $recherche = trim($request->query->get('nom'));
        $participantsTrouves = $participantRepository->findLike($recherche);
        return $this->render('etudiant/liste.html.twig', [
            "etudiants"=>$participantsTrouves,
            "search"=>$recherche
        ]);

    }

    /**
     * @Route("admin/users/action", name="user_action", methods={"POST"})
     */
    public function modifierPlusieurs(Request $request,
                                      ParticipantRepository $participantRepository,
                                      EntityManagerInterface $entityManager,
                                      SortieRepository $sortieRepository):Response

    {
        //non null si cette action est sélectionnée
        $actionSupprimer = ($request->get('supprimer') === "") ? true : false;
        $actionActiver = ($request->get('actif') === "") ? true : false;
        $actionDesactiver = ($request->get('inactif') === "") ? true : false;

        $participants = $request->get("etudiant");

        if(!isset($participants))
        {
            return $this->redirectToRoute('participant_liste');
        }

        $inc = 0;

        foreach($participants as $index => $array)
        {
            foreach($array as $id => $checkbox)
            {
                $utilisateur = $participantRepository->findOneById($id);

                if($actionSupprimer){
                    $sortiesOrganisees = $sortieRepository->findOneByOrganisateur($id);

                    if($sortiesOrganisees !== null){
                        $inc++;
                    }else{
                        $entityManager->remove($utilisateur);
                    }
                }

                if($actionActiver){
                    $utilisateur -> setActif(true);
                    $entityManager->persist($utilisateur);
                }

                if($actionDesactiver){
                    $utilisateur -> setActif(false);
                    $entityManager->persist($utilisateur);
                }
            }
        }

        if($inc>0 and $actionSupprimer){
            $this->addFlash(
                'fail',
                $inc. " utilisateurs n'ont pas pu être supprimés car ils organisent des sorties"
            );
        }else if($actionSupprimer and $inc==0){
            $this->addFlash(
                'success',
                "Les utilisateurs ont bien été supprimés"
            );
        }

        $entityManager->flush();

        return $this->redirectToRoute('participant_liste');

    }

}

