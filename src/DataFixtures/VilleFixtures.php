<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $connection = $manager->getConnection();
        $connection->executeQuery("ALTER TABLE ville AUTO_INCREMENT = 1;");

        $faker = \Faker\Factory::create('fr_FR');

        for($i = 0 ; $i < 10 ; $i++){
            $ville = new Ville();
            $ville->setNom( ucwords($faker->unique()->word()) );
            $ville->setCodePostal($faker->unique()->numberBetween(10000, 99999));
            $manager->persist($ville);

            $this->addReference(Ville::class.'_'.$i, $ville);
        }

        $manager->flush();
    }
}
