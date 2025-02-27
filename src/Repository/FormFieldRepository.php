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

use App\Entity\FormField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormField>
 *
 * @method FormField|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormField|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormField[]    findAll()
 * @method FormField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormField::class);
    }

    public function findFirst(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('ff')
            ->orderBy('ff.createdAt', 'ASC');
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
