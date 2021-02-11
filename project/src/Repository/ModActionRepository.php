<?php

namespace App\Repository;

use App\Entity\ModAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ModAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModAction[]    findAll()
 * @method ModAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModAction::class);
    }

    // /**
    //  * @return ModAction[] Returns an array of ModAction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ModAction
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
