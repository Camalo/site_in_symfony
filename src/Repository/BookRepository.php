<?php

namespace App\Repository;

use App\Entity\Books;
use App\Entity\BooksInCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Books|null find($id, $lockMode = null, $lockVersion = null)
 * @method Books|null findOneBy(array $criteria, array $orderBy = null)
 * @method Books[]    findAll()
 * @method Books[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 2;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Books::class);
    }
    public function getBooksPaginator(int $offset, int $perPage): Paginator
    {
        //offset смещение
        //perPage на одной странице
        $query = $this->createQueryBuilder('b')
            ->setMaxResults($perPage)
            ->setFirstResult($offset)
            ->getQuery();


        return new Paginator($query);
    }

    public function getBookInCat(int $offset,int $category):Paginator
    {
        $query = $this->createQueryBuilder('b')
            ->innerJoin(BooksInCategories::class, 'bic','WITH', 'bic.book_id = b.id')
            ->andWhere('bic.category_id= :category')
            ->setParameter('category',$category)
            ->groupBy('b.title')
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery();

        return new Paginator($query);
    }

    public function getBooksToAdd(int $offset, int $perPage,int $category)
    {
        $query = $this->createQueryBuilder('b')
            //НЕ ИЗМЕНЯЛА ЕЩЕ
            ->join(BooksInCategories::class, 'bic','WITH', 'bic.book_id = b.id')
            ->andWhere('bic.category_id <> :category')
            ->setParameter('category',$category)
            ->groupBy('b.title')
            ->setMaxResults($perPage)
            ->setFirstResult($offset)
            ->getQuery();

        return new Paginator($query);
    }
    public function findBooks(int $offset, $q)
    {
        $query = $this->createQueryBuilder('b')
            ->where('b.title LIKE :search_item')
            ->orWhere('b.author LIKE :search_item')
            ->orWhere('b.description LIKE :search_item')
            ->setParameter('search_item', '%'.$q.'%')
            ->groupBy('b.title')
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery();

        return new Paginator($query);
    }

    // /**
    //  * @return Book[] Returns an array of Book objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Book
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
