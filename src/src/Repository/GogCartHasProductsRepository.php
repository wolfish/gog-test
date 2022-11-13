<?php

namespace App\Repository;

use App\Entity\GogCartHasProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GogCartHasProducts>
 *
 * @method GogCartHasProducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method GogCartHasProducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method GogCartHasProducts[]    findAll()
 * @method GogCartHasProducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GogCartHasProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GogCartHasProducts::class);
    }

    public function save(GogCartHasProducts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GogCartHasProducts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return GogCartHasProducts[] Returns an array of GogCartHasProducts objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GogCartHasProducts
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
