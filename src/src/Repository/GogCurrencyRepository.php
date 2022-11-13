<?php

namespace App\Repository;

use App\Entity\GogCurrency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GogCurrency>
 *
 * @method GogCurrency|null find($id, $lockMode = null, $lockVersion = null)
 * @method GogCurrency|null findOneBy(array $criteria, array $orderBy = null)
 * @method GogCurrency[]    findAll()
 * @method GogCurrency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GogCurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GogCurrency::class);
    }

    public function save(GogCurrency $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GogCurrency $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
