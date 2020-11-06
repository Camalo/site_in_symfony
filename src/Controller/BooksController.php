<?php

namespace App\Controller;

use App\Entity\Books;
use App\Entity\BooksInCategories;
use App\Entity\Categories;
use App\Repository\BookRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;


class BooksController extends AbstractController
{

    //must be in repository


    /**
     * @Route("/", name="homepage")
     */
    public function homepage(Request $request,BookRepository $bookRepository): Response
    {
        $entity_manager=$this->getDoctrine()->getManager();
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $bookRepository->getBooksPaginator($offset);

        $categories=$entity_manager->getRepository(Categories::class)->findAll();

        return $this->render('index.html.twig', [
            'books' => $paginator,
            'categories' => $categories ,
            'previous' => $offset - BookRepository::PAGINATOR_PER_PAGE,
            'next'=> min(count($paginator), $offset + BookRepository::PAGINATOR_PER_PAGE)
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
    public function show_category($category,Request $request,BookRepository $bookRepository)
    {
        
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator=$bookRepository->getBookInCat($offset,$category);


        return $this->render('category/category.html.twig',[
            'books'=> $paginator,
            'category' => $category,
            'previous' => $offset - BookRepository::PAGINATOR_PER_PAGE,
            'next'=> min(count($paginator), $offset + BookRepository::PAGINATOR_PER_PAGE)
        ]);
    }




}
