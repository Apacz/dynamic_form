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

use App\Entity\Post;
use App\Entity\Tag;
use App\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\String\u;

/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 *
 * See https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 *
 * @method Post|null findOneByTitle(string $postTitle)
 *
 * @template-extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findLatest(int $page = 1, Tag $tag = null): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('a', 't')
            ->innerJoin('p.author', 'a')
            ->leftJoin('p.tags', 't')
            ->where('p.publishedAt <= :now')
            ->orderBy('p.publishedAt', 'DESC')
            ->setParameter('now', new \DateTime())
        ;

        if (null !== $tag) {
            $qb->andWhere(':tag MEMBER OF p.tags')
                ->setParameter('tag', $tag);
        }

        return (new Paginator($qb))->paginate($page);
    }

    /**
     * @return Post[]
     */
    public function findBySearchQuery(string $query, int $limit = Paginator::PAGE_SIZE): array
    {
        $searchTerms = $this->extractSearchTerms($query);

        if (0 === \count($searchTerms)) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder('p');

        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->orWhere('p.title LIKE :t_'.$key)
                ->setParameter('t_'.$key, '%'.$term.'%')
            ;
        }

        /** @var Post[] $result */
        $result = $queryBuilder
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    /**
     * Transforms the search string into an array of search terms.
     *
     * @return string[]
     */
    private function extractSearchTerms(string $searchQuery): array
    {
        $terms = array_unique(u($searchQuery)->replaceMatches('/[[:space:]]+/', ' ')->trim()->split(' '));

        // ignore the search terms that are too short
        return array_filter($terms, static function ($term) {
            return 2 <= $term->length();
        });
    }

    public function searchByFields(array $criteria)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.postFormValues', 'pfv')
            ->leftJoin('pfv.schema', 's')
            ->leftJoin('pfv.field', 'f');

        // Dynamically filter based on the criteria provided
        foreach ($criteria as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if (strpos($key, '_from') !== false || strpos($key, '_to') !== false) {
                // Handle date ranges
                $fieldName = str_replace('_from', '', $key);
                $dateFrom = $criteria[$fieldName . '_from'] ?? null;
                $dateTo = $criteria[$fieldName . '_to'] ?? null;

                if ($dateFrom && $dateTo) {
                    $qb->orWhere('p.createdAt BETWEEN :dateFrom AND :dateTo')
                        ->setParameter('dateFrom', $dateFrom)
                        ->setParameter('dateTo', $dateTo);
                }
            } else {
                // For text, checkbox, number, etc.
                $qb->orWhere('f.name = :fieldName AND pfv.value LIKE :value')
                    ->setParameter('fieldName', $key)
                    ->setParameter('value', '%' . $value . '%');
            }
        }

        return $qb->getQuery()->getResult();
    }
}
