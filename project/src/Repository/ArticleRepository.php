<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\Status;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

	/**
	 * Base query builder to get published articles
	 */
	public function getArticlesPublished() {
		$q = $this->createQueryBuilder("a")
			->join("a.status", "stat")
			->andWhere("stat.code = :status")->setParameter("status", Status::CODE_PUBLISHED)
			->addOrderBy("a.publishDate", "desc");
		return $q;
	}

	/**
	 * Get features articles
	 * @param int $nb number of returned articles
	 */
	public function getFeatured($nb = 5) {
		return $this->getArticlesPublished()
			->andWhere("a.featured = true")
			->setMaxResults($nb)
			->getQuery()->getResult();
	}

	/**
	 * Get the latest articles published
	 * @param int $nb number of returned articles
	 */
	public function getLatest($nb = 5) {
		return $this->getArticlesPublished()
			->setMaxResults($nb)
			->getQuery()->getResult();
	}

	/**
	 * Get the number of published articles
	 * @return int number of articles
	 */
	public function numPublished() {
		return $this->getArticlesPublished()
			->select("COUNT(a.id)")
			->getQuery()
			->getSingleScalarResult();
	}

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
