<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 */
class ContactRepository extends ServiceEntityRepository
{
    private const array SORTABLE_FIELDS = [
        'lastName',
        'firstName',
        'createdAt',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /**
     * @return Contact[]
     */
    public function findAllSorted(
        string $sort = 'lastName',
        string $order = 'asc',
    ): array {
        if (!in_array($sort, self::SORTABLE_FIELDS, true)) {
            $sort = 'lastName';
        }

        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        return $this->createQueryBuilder('c')
            ->leftJoin('c.phoneNumbers', 'p')
            ->addSelect('p')
            ->orderBy('c.' . $sort, $order)
            ->getQuery()
            ->getResult();
    }
}
