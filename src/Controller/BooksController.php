<?php

namespace App\Controller;

use App\Entity\Books;
use App\Entity\BooksInCategories;
use App\Entity\Categories;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BooksController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage(): Response
    {
        $entity_manager=$this->getDoctrine()->getManager();

        $books=$entity_manager->getRepository(Books::class)->findAll();
        $categories=$entity_manager->getRepository(Categories::class)->findAll();

        return $this->render('index.html.twig', [
            'books' => $books, 'categories' => $categories
        ]);
    }


    /**
     * @Route("/books/{book}", name="show_book")
     */
    public function show_book($book)
    {
        $entity_manager=$this->getDoctrine()->getManager();

        $inf_book=$entity_manager->getRepository(Books::class)->find($book);

        //find id of categories
        $categories_of_book=$entity_manager->getRepository(BooksInCategories::class)
            ->findBy(['book_id'=>$inf_book->getId()]);

        $id=[];
        foreach($categories_of_book as $category)
        {
            $id[]=$category->getCategoryId();
        }

        $categories=$entity_manager->getRepository(Categories::class)
            ->findBy(['id'=>$id]);

        return $this->render('book/one_book.html.twig',[
            'book'=> $inf_book, 'categories'=>$categories
        ]);

    }
    /**
     * @Route("/categories/{category}", name="show_category")
     */
    public function show_category($category)
    {
        $entity_manager=$this->getDoctrine()->getManager();
        // Get objects from table books_in_categories
        $books_id = $entity_manager->getRepository(BooksInCategories::class)
            ->findBy(['category_id'=>$category],['book_id'=>'ASC']);

        // Get a id of book and put it in array $id
        $id=[];
        foreach($books_id as $book_id)
        {
            $id[]=$book_id->getBookId();
        }

        // Get objects from table books where id of book match value in array $id
        $books=$entity_manager->getRepository(Books::class)
            ->findBy(['id' => $id]); //=>$id);
        //Get title of category
        $category_title=$entity_manager->getRepository(Categories::class)
            ->findOneBy(['id'=>$category]);

        return $this->render('category/category.html.twig',[
            'books'=> $books, 'category_title'=>$category_title
        ]);
    }
}
