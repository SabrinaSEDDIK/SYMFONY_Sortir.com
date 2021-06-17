<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $connection = $manager->getConnection();
        $connection->executeQuery("ALTER TABLE campus AUTO_INCREMENT = 1;");

        $campus = new Campus();
        $campus->setNom('SAINT-HERBLAIN');
        $manager->persist($campus);
        $this->addReference(Campus::class.'_0', $campus);

        $campus = new Campus();
        $campus->setNom('RENNES');
        $manager->persist($campus);
        $this->addReference(Campus::class.'_1', $campus);

        $campus = new Campus();
        $campus->setNom('QUIMPER');
        $manager->persist($campus);
        $this->addReference(Campus::class.'_2', $campus);

        $campus = new Campus();
        $campus->setNom('NIORT');
        $manager->persist($campus);
        $this->addReference(Campus::class.'_3', $campus);

        $manager->flush();
    }
}
