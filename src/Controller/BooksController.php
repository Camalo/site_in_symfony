<?php

namespace App\Controller;

use App\Entity\Books;
use App\Entity\BooksInCategories;
use App\Entity\Categories;
use App\Form\BookType;
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
        //Смещение на 'offset' записей
        $offset = max(0, $request->query->getInt('offset', 0));
        //отображение записей на одной странице, по умолчанию равно 10
        $perPage = max(1, $request->query->getInt('perPage', 10));


        $paginator = $bookRepository->getBooksPaginator($offset,$perPage);

        $categories=$entity_manager->getRepository(Categories::class)->findAll();

        return $this->render('index.html.twig', [
            'books' => $paginator,
            'categories' => $categories ,
            'previous' => $offset - $perPage,
            'next'=> min(count($paginator), $offset + $perPage),
            'perpage'=>$perPage
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
    //Реализация поиска
    /**
     * @Route("/searching", name="search_book")
     */
    public function searchBook(Request $request,BookRepository $bookRepository)
    {
        $q = $_REQUEST['q'];

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator=$bookRepository->findBooks($offset,$q);


        return $this->render('book/searching.html.twig',[
            'books'=> $paginator,
            'previous' => $offset - BookRepository::PAGINATOR_PER_PAGE,
            'next'=> min(count($paginator), $offset + BookRepository::PAGINATOR_PER_PAGE)
        ]);
    }



    /**
     * @Route("/manager/new", name="new_book")
     */
    public function newBook(Request $request):Response
    {
        $book = new Books();
        $form =$this->createForm(BookType::class,$book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $book=$form->getData();
            $em=$this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute("manager");
        }
        return $this->render("manager/new.html.twig",[
            'form'=>$form->createView()

        ]);
    }
    /**
     * @Route("/manager/update/{id}", name="update_book")
     */
    public function updateBook(Request $request, $id)//:Response
    {
        $em=$this->getDoctrine()->getManager();
        $book=$em->getRepository(Books::class)->find($id);

        $form = $this->createForm(BookType::class,$book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $book=$form->getData();
            //$em=$this->getDoctrine()->getManager();

            $em->flush();

            return $this->redirectToRoute("manager");
        }
        return $this->render("manager/update.html.twig",[
            'form'=>$form->createView(),
            'book'=>$book
        ]);
    }



    /**
     * @Route("/manager/delete/{id}", name="delete_book")
     */
    public function deleteBook($id)
    {
        $em=$this->getDoctrine()->getManager();
        $book=$em->getRepository(Books::class)->find($id);
        $em->remove($book);
        $em->flush();

        return $this->render('manager/delete.html.twig',[
            'book'=>$book
        ]);
    }


}
