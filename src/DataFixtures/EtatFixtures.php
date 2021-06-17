<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $connection = $manager->getConnection();
        $connection->executeQuery("ALTER TABLE etat AUTO_INCREMENT = 1;");

        $etat = new Etat();
        $etat->setLibelle("Créée");
        $manager->persist($etat);
        $this->addReference(Etat::class.'_0', $etat);

        $etat = new Etat();
        $etat->setLibelle("Ouverte");
        $manager->persist($etat);
        $this->addReference(Etat::class.'_1', $etat);

        $etat = new Etat();
        $etat->setLibelle("Clôturée");
        $manager->persist($etat);
        $this->addReference(Etat::class.'_2', $etat);

        $etat = new Etat();
        $etat->setLibelle("Activité en cours");
        $manager->persist($etat);
        $this->addReference(Etat::class.'_3', $etat);

        $etat = new Etat();
        $etat->setLibelle("Passée");
        $manager->persist($etat);
        $this->addReference(Etat::class.'_4', $etat);

        $etat = new Etat();
        $etat->setLibelle("Annulée");
        $manager->persist($etat);
        $this->addReference(Etat::class.'_5', $etat);


        $manager->flush();
    }
}
