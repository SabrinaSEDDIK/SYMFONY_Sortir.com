<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $connection = $manager->getConnection();
        $connection->executeQuery("ALTER TABLE participant AUTO_INCREMENT = 1;");
        $connection->executeQuery("ALTER TABLE participant_sortie AUTO_INCREMENT = 1;");

        $faker = \Faker\Factory::create('fr_FR');

        for($i = 0 ; $i < 50 ; $i++){
            $participant = new Participant();
            $nomExplose = explode(" ", $faker->name());

            $search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
            $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
            //$MaChaine = str_replace($search, $replace, $MaChaine);

            $nom = $nomExplose[count($nomExplose) - 1];
            $nomMinuscule = str_replace($search, $replace, mb_strtolower($nom));
            $participant->setNom($nom);
            $prenom = $faker->firstName();
            $prenomMinuscule = str_replace($search, $replace, mb_strtolower($prenom));
            $participant->setPrenom($prenom);
            $participant->setPseudo($faker->unique()->word());
            $participant->setTelephone($faker->numberBetween(1000000000,9999999999));
            $email = $nomMinuscule.'.'.$prenomMinuscule.'@'.$faker->safeEmailDomain;
            $participant->setEmail($email);
            $participant->setRoles(['ROLE_USER']);

            $password = $this->encoder->encodePassword($participant, 'rudasalska');
            $participant->setPassword($password);


            $participant->setAdministrateur($faker->numberBetween(0,1));
            $participant->setActif($faker->numberBetween(0,1));
            $participant->setCampus($this->getReference(Campus::class.'_'.mt_rand(0,3)));

            $manager->persist($participant);

            $this->addReference(Participant::class.'_'.$i, $participant);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CampusFixtures::class
        ];
    }
}
