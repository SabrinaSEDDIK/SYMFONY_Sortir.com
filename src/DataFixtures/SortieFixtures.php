<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $connection = $manager->getConnection();
        $connection->executeQuery("ALTER TABLE sortie AUTO_INCREMENT = 1;");

        $faker = \Faker\Factory::create('fr_FR');

        for($i = 0 ; $i < 50 ; $i++){
            $sortie = new Sortie();
            $sortie->setNom($faker->unique()->word());
            $dateDebut = $faker->dateTimeBetween("-1 month", "+ 3 months");
            $sortie->setDateHeureDebut($dateDebut);
            $sortie->setDuree($faker->numberBetween(30,300));
            $sortie->setDateLimiteInscription($faker->dateTimeBetween("-2 month", $dateDebut));
            $sortie->setNbInscriptionsMax($faker->numberBetween(3, 40));
            $sortie->setInfosSortie($faker->sentence(20));

            $sortie->setSiteOrganisateur($this->getReference(Campus::class.'_'.mt_rand(0,3)));
            $sortie->setLieu($this->getReference(Lieu::class.'_'.mt_rand(0,4)));

            $nombrePersonneSortie = mt_rand(0,$sortie->getNbInscriptionsMax());

            // Création d'un tableau contenant les id des participants
            for($j = 0 ; $j < 50 ; $j++){
                $tableauParticipant[] = $j;
            }

            for($k = 0 ; $k < $nombrePersonneSortie ; $k++){
                shuffle($tableauParticipant );
                //$participantChoisi = array_rand($tableauParticipant , $num = 1);
                $sortie->addParticipant($this->getReference(Participant::class.'_'.$tableauParticipant[0]));
                // On enlève ensuite le participant du tableau
                unset($tableauParticipant[array_search($tableauParticipant[0], $tableauParticipant)]);
            }

            $dateActuelle = new \DateTime();
            $dureeSortie = new DateInterval('PT'.$sortie->getDuree().'M');
            $dateDebutSortie = clone($sortie->getDateHeureDebut());
            $dateFinSortie = $dateDebutSortie->add($dureeSortie);

            // Si on est après la fin de la sortie, l'état est Passée
            if($dateActuelle > $dateFinSortie){
                $sortie->setEtat($this->getReference( Etat::class.'_4'));
            }
            // Si l'heure actuelle est entre le début et la fin de la sortie, l'état est En cours
            if($dateActuelle < $dateFinSortie && $dateActuelle > $sortie->getDateHeureDebut()){
                $sortie->setEtat($this->getReference( Etat::class.'_3'));
            }
            // Si on est entre la date de clôture et la date de début, on est à clôturée
            if($dateActuelle < $sortie->getDateHeureDebut() && $dateActuelle > $sortie->getDateLimiteInscription()){
                $sortie->setEtat($this->getReference( Etat::class.'_2'));
            }
            // Si la date actuelle est avant le début et qu'il n'y a plus de place, c'est clôturée
            if( $dateActuelle < $sortie->getDateLimiteInscription() && $sortie->getNbInscriptionsMax() == $nombrePersonneSortie){
                $sortie->setEtat($this->getReference( Etat::class.'_2'));
            }
            // Si on est avant la date de clôture et qu'au moins une personne est inscrite, on est ouverte
            if( $dateActuelle < $sortie->getDateLimiteInscription() && $nombrePersonneSortie > 0){
                $sortie->setEtat($this->getReference( Etat::class.'_1'));
            }
            // Si on est avant la date de clôture et que personne n'est inscrit, on est soit ouverte, soit créé
            if( $dateActuelle < $sortie->getDateLimiteInscription() && $nombrePersonneSortie == 0){
                $sortie->setEtat($this->getReference( Etat::class.'_'.mt_rand(0,1)));
            }

            $sortie->setOrganisateur($this->getReference(Participant::class.'_'.mt_rand(0, count($sortie->getParticipants()))));
            $manager->persist($sortie);
        }


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CampusFixtures::class,
            LieuFixtures::class,
            EtatFixtures::class,
            ParticipantFixtures::class
        ];
    }

}
