<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\FormSchema;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormSchema>
 *
 * @method FormSchema|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormSchema|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormSchema[]    findAll()
 * @method FormSchema[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormSchemaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormSchema::class);
    }

    public function findFirst(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('fs')
            ->orderBy('fs.createdAt', 'ASC');
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
