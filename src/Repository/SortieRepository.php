<?php

namespace App\Repository;

use App\Entity\RechercheSortie;
use App\Entity\Sortie;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function infosSortiePourAnnulation($id) {
        return $this->createQueryBuilder('s')
            ->select('s.nom, s.dateHeureDebut, c.nom as nom_campus, l.nom as nom_lieu')
            ->andWhere('s.id = :id')
            ->innerJoin('s.siteOrganisateur', 'c', Join::WITH, 's.siteOrganisateur = c')
            ->innerJoin('s.lieu', 'l', Join::WITH, 's.lieu = l')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function rechercheAccueilSortie(RechercheSortie $rechercheSortie, $user): array {
        $requete = $this->createQueryBuilder('s')
            ->select('s.id, s.nom as nomSortie, s.dateHeureDebut, s.dateLimiteInscription , s.nbInscriptionsMax, e.libelle as libelleEtat, p.nom as nomOrganisateur, p.prenom as prenomOrganisateur, p.id as idOrganisateur, count(ps.nom) as nbParticipant, ps.id as idParticipant')
            ->innerJoin('s.etat', 'e', Join::WITH, 's.etat = e')
            ->leftJoin('s.participants', 'ps')
            ->innerJoin('s.organisateur', 'p', Join::WITH, 's.organisateur = p');

        // Condition sur le campus
        if($rechercheSortie->getCampus() != null){
            $requete->andWhere('s.siteOrganisateur = :campus')
                ->setParameter('campus', $rechercheSortie->getCampus());
        }

        // Condition sur le nom de la sortie
        if($rechercheSortie->getNom() != null){
            $requete->andWhere($requete->expr()->like('s.nom', ':nom'))
                ->setParameter('nom', '%'.$rechercheSortie->getNom().'%');
        }

        // Condition sur le date de début de recherche
        if($rechercheSortie->getDateDebutRecherche() != null){
            $requete->andWhere('s.dateHeureDebut > :dateDebutRecherche')
                ->setParameter('dateDebutRecherche', $rechercheSortie->getDateDebutRecherche());
        }

        // Condition sur le date de fin de recherche
        if($rechercheSortie->getDateFinRecherche() != null){
            $requete->andWhere('s.dateHeureDebut < :dateFinRecherche')
                ->setParameter('dateFinRecherche', $rechercheSortie->getDateFinRecherche());
        }

        // Condition si je suis l'organisateur
        if($rechercheSortie->getIsOrganisateur() == true){
            $requete->andWhere('p.id = :idOrganisateur')
                ->setParameter('idOrganisateur', $user->getId());
        }

        // Condition si je suis inscrit à cette sortie ou non
        if($rechercheSortie->getIsRegistered() === true){
            $requete->andWhere('ps.id = :idParticipant')
                ->setParameter('idParticipant', $user->getId());
        }

        if($rechercheSortie->getIsNotRegistered() == true){
            $requeteIntermediaire = $this->createQueryBuilder('s')
                ->select('s.id')
                ->innerJoin('s.participants', 'ps')
                ->andWhere('s.siteOrganisateur = :campus')
                ->andWhere('ps.id = :idParticipant')
                ->setParameter('campus', $rechercheSortie->getCampus())
                ->setParameter('idParticipant', $user->getId())
                ->getQuery()
                ->getResult();

            for($i = 0 ; $i < count($requeteIntermediaire) ; $i++){
                $requete->andWhere($requete->expr()->notIn('s.id', $requeteIntermediaire[$i]));
            }
        }

        // Condition sur les sorties passées
        if($rechercheSortie->getIsFinished() == true){
            $requete->andWhere($requete->expr()->like('e.libelle', ':etatLibelle'))
                ->setParameter('etatLibelle', 'Passée');
        }

        // Ajout de la condition d'archivage (sorties non consultables si réalisées depuis plus d'un mois.
        $dateActuelle = new \DateTime();
        $dureeArchivage = new DateInterval('P1M');
        $dateLimiteArchivage = clone($dateActuelle)->sub($dureeArchivage);

        $requete->andWhere('s.dateHeureDebut > :dateLimiteArchivage')
            ->setParameter('dateLimiteArchivage', $dateLimiteArchivage);

        $requete->groupBy('s.id');

        return $requete->getQuery()
                        ->getResult();
    }

    public function rechercheParticipantAccueilSortie(RechercheSortie $rechercheSortie, $user): array {
        $requete = $this->createQueryBuilder('s')
            ->select('s.id as idSortie, s.nom as nomSortie, ps.id as idParticipant, ps.nom as nomParticipant, ps.prenom as prenomParticipant')
            ->innerJoin('s.etat', 'e', Join::WITH, 's.etat = e')
            ->innerJoin('s.participants', 'ps')
            ->innerJoin('s.organisateur', 'p', Join::WITH, 's.organisateur = p');

        if($rechercheSortie->getCampus() != null){
            $requete->andWhere('s.siteOrganisateur = :campus')
                ->setParameter('campus', $rechercheSortie->getCampus());
        }

        return $requete->getQuery()
                ->getResult();
    }
}
