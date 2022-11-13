<?php

namespace App\Repository;

use App\Entity\GogCart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GogCart>
 *
 * @method GogCart|null find($id, $lockMode = null, $lockVersion = null)
 * @method GogCart|null findOneBy(array $criteria, array $orderBy = null)
 * @method GogCart[]    findAll()
 * @method GogCart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GogCartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GogCart::class);
    }

    public function save(GogCart $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GogCart $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
