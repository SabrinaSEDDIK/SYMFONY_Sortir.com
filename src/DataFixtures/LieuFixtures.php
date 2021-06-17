<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $connection = $manager->getConnection();
        $connection->executeQuery("ALTER TABLE lieu AUTO_INCREMENT = 1;");

        $faker = \Faker\Factory::create();

        for($i = 0 ; $i < 50 ; $i++){
            $lieu = new Lieu();
            $lieu->setNom( ucwords($faker->unique()->word()) );
            $lieu->setRue($faker->unique()->numberBetween(1, 200) .' ' .$faker->unique()->sentence(3));
            $lieu->setLatitude($faker->randomFloat(2, -90, 90));
            $lieu->setLongitude($faker->randomFloat(2, -90, 90));
            $lieu->setVille($this->getReference(Ville::class.'_'.mt_rand(0,4)));

            $manager->persist($lieu);
            $this->addReference(Lieu::class.'_'.$i, $lieu);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            VilleFixtures::class
        ];
    }
}
