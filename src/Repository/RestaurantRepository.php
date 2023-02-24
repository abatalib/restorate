<?php

namespace App\Repository;

use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Restaurant>
 *
 * @method Restaurant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Restaurant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Restaurant[]    findAll()
 * @method Restaurant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Restaurant::class);
    }

    public function add(Restaurant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Restaurant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getRestaurants(array $criteria, array $pagination)
    {

        $query= $this->createQueryBuilder('r');

        if(!empty($criteria)) {
            if (!empty($criteria['name'])) {
                $name = $criteria['name'];
                $query = $query->andWhere('r.name LIKE :nameR');
                $query->setparameter('nameR', "%$name%");
            }

            if (!empty($criteria['city'])) {
                $query = $query->andWhere('r.city = :city');
                $query->setparameter('city', $criteria['city']);
            }
        }else {
            $query = $query->setMaxResults($pagination[0])->setFirstResult($pagination[1]);
        }

        $query = $query->orderBy("r.created_at",'DESC');


        $query=$query->getquery();
        return $query->getresult();
    }
}
