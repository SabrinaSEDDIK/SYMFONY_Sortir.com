<?php


namespace App\Services;


use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class OnLoad implements EventSubscriberInterface
{
    private $entityManager;
    private $sortieRepository;
    private $etatRepository;

    public function __construct(EntityManagerInterface $em, SortieRepository $sortieRepo, EtatRepository $etatRepo)
    {
        $this->entityManager = $em;
        $this->sortieRepository = $sortieRepo;
        $this->etatRepository = $etatRepo;
    }

    public function onLoad()
    {
        $sorties = $this->sortieRepository->findAll();
        $dateActuelle = new \DateTime();
        $modification = false;

        for($i = 0 ; $i < count($sorties) ; $i++){
            $modification = false;
            $dureeSortie = new DateInterval('PT'.$sorties[$i]->getDuree().'M');
            $dateDebutSortie = clone($sorties[$i]->getDateHeureDebut());
            $dateFinSortie = $dateDebutSortie->add($dureeSortie);

            // Si on se trouve entre l'heure de début et de fin de la sortie et que l'état est ouverte, on change l'état
            if($sorties[$i]->getDateHeureDebut() <= $dateActuelle && $dateActuelle <= $dateFinSortie && $sorties[$i]->getEtat()->getLibelle() === 'Ouverte'){
                $etatEnCours = $this->etatRepository->findBy(['id' => 4]);
                $sorties[$i]->setEtat($etatEnCours[0]);
                $modification = true;
            }

            // Si on se trouve après la fin de la sortie
            if($dateActuelle > $dateFinSortie) {
                $etatEnCours = $this->etatRepository->findBy(['id' => 5]);
                $sorties[$i]->setEtat($etatEnCours[0]);
                $modification = true;
            }

            if($modification){
                $this->entityManager->persist($sorties[$i]);
                $modification = false;
            }
        }

        $this->entityManager->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onLoad',
        ];
    }
}