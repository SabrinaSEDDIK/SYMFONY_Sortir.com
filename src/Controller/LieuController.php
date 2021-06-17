<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LieuController extends AbstractController
{
    /**
     * @Route("/lieu/json", name="lieu_json")
     */
    public function getLieuxByVille(Request $request, LieuRepository $lieu, VilleRepository $ville, SerializerInterface $serializer): Response
    {
        /* On récupère la ville dans l'url */
        $city = $ville->findOneById($request->get("city"));

        $lieux = $lieu->findByVille($city);

        $events = [];

        /* Si la variable $city existe alors on récupère uniquement les lieux de cette ville */
        if($city && $lieux)
        {

            foreach($lieux as $lieu)
            {
                $events[] = [
                    "id"=> $lieu->getId(),
                    "ville"=> $lieu->getVille()->getNom(),
                    "codePostal"=>$lieu->getVille()->getCodePostal(),
                    "nom"=> $lieu->getNom(),
                    "rue"=> $lieu->getRue(),
                    "latitude"=> $lieu->getLatitude(),
                    "longitude"=> $lieu->getLongitude()
                ];
            }
        }

        $data = $serializer->serialize($events, "json");

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/lieu/create/json", name="lieu_create_json")
     */
    public function ajouterLieuJson(SerializerInterface $serializer,
                                    EntityManagerInterface $em,
                                    VilleRepository $villeRepository,
                                    LieuRepository $lieuRepository)
    {
        //récupère les données de la requête ajax (json)
        $data = file_get_contents("php://input");
        //du json à des données exploitables en php
        $data = json_decode($data, true);


        //STOCK DES DONNEES SEPAREES AVEC suppression des espaces début/fin
        $lieu = trim($data["modalLieu"]);
        $latitude = trim($data["modalLatitude"]);
        $longitude = trim($data["modalLongitude"]);
        $rue= trim($data["modalRue"]);
        $codePostal = trim($data["modalCp"]);
        $ville = trim($data["modalVille"]);

        //VALIDATION DES DONNEES

        $verifLieu =
            !empty($lieu)
            && strlen($lieu) <= 255;

        $verifLatitude = ($latitude >= -90 && $latitude <= 90) ? true : false;

        $verifLongitude = ($longitude >= -180 && $longitude <= 180) ? true : false;

        $verifRue =
            !empty($rue)
            && strlen($rue) <= 255;

        $verifCp =
            !empty($codePostal)
            && filter_var(
                $codePostal,
                FILTER_VALIDATE_REGEXP,
                array(
                    'options' => array(
                        'regexp' => "/^((0[1-9])|([1-8][0-9])|(9[0-8])|(2A)|(2B))[0-9]{3}$/"
                    )
                )
            );

        $verifVille =
            !empty($ville)
            && strlen($ville) <= 255
            && filter_var(
                $ville,
                FILTER_VALIDATE_REGEXP,
                array(
                    'options' => array(
                        'regexp' => "/^(?!^.*[0-9])/"
                    )
                )
            );

        // TABLEAU DES VERIFS
        $verifs = ["verifLieu"=> $verifLieu,
            "verifLatitude"=>$verifLatitude,
            "verifLongitude"=>$verifLongitude,
            "verifRue"=>$verifRue,
            "verifCp"=>$verifCp,
            "verifVille"=>$verifVille];

        $newLieu =null;

        if($this->dataIsValid($verifs))
        {
            //FORMATER les données
            $lieu = ucfirst($lieu);
            $rue = ucfirst(strtolower($rue));
            $ville = strtoupper($ville);

            //VERIFICATION : est-ce que le lieu entré par l'utilisateur existe déjà en BDD ?
            // --> OUI : on ajoute le nouveau lieu en BDD
            // --> NON : on préviendra l'utilisateur que ce lieu existe déjà
            $villeFound = $villeRepository->findOneByNom($ville); // null ou qqch

            // recherche du dessus --> par nom et pas par code postal car plusieurs villes peuvent avoir le même code postal
            $lieuxFound = $lieuRepository->findByNom($lieu);

            $nouveauLieu = true;
            if($lieuxFound)   //tableau vide
            {
                foreach($lieuxFound as $element)
                {
                    if($element->getVille() == $villeFound)
                    {
                        $nouveauLieu = false;
                    }
                }
            }

            //Ajout du nouveau lieu en BDD
            if($nouveauLieu)
            {

                // Si la ville est inexistante, on l'ajoute en BDD
                if(!$villeFound)
                {
                    $newVille = new Ville();
                    $newVille->setNom($ville);
                    $newVille->setCodePostal($codePostal);
                    $em->persist($newVille);
                    $em->flush();
                }

                $newlieu = new Lieu();
                if($villeFound)
                {
                    $newlieu->setVille($villeFound);
                }
                else
                {
                    $newlieu->setVille($newVille);
                }
                $newlieu->setNom($lieu);
                $newlieu->setRue($rue);
                $newlieu->setLatitude($latitude);
                $newlieu->setLongitude($longitude);

                $em->persist($newlieu);
                $em->flush();


                $events = [
                    "id"=> $newlieu->getId(),
                    "ville"=> [
                        "id"=> $newlieu->getVille()->getId(),
                        "codePostal"=> $newlieu->getVille()->getCodePostal(),
                        "nom"=> $newlieu->getVille()->getNom()
                    ],
                    "nom"=> $newlieu->getNom(),
                    "rue"=> $newlieu->getRue(),
                    "latitude"=> $newlieu->getLatitude(),
                    "longitude"=> $newlieu->getLongitude()
                ];
                $data = $serializer->serialize($events, "json");
                //dd($data);
            }
            else //le lieu entré est déjà existant
            {
                $errors["errors"]["lieuExistant"]= true;
                $data = $serializer->serialize($errors, "json");
            }
        }
        else  //les données entrées par l'utilisateur comportent des erreurs
        {
            $errors["errors"]["champsInvalides"] = $verifs;
            $data = $serializer->serialize($errors, "json");
        }

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    public function dataIsValid(array $array){
        $boolean = true;
        foreach($array as $element){
            if($element == false){
                $boolean = false;
            }
        }
        return $boolean;
    }

}