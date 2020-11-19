<?php

namespace App\Controller;

use App\Entity\Books;
use App\Entity\BooksInCategories;
use App\Entity\Categories;
use App\Form\BookType;
use App\Form\CategoriesType;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ManagerController extends AbstractController
{
    /**
     * @Route("/manager", name="manager")
     */
    public function index(AuthorizationCheckerInterface $authChecker,Request $request,BookRepository $bookRepository): Response
    {
        //проверка прав доступа
        if (false === $authChecker->isGranted('ROLE_MANAGER')) {
            return $this->redirectToRoute('login');
        }
        $entity_manager=$this->getDoctrine()->getManager();
        $offset = max(0, $request->query->getInt('offset', 0));
        $perPage = max(1, $request->query->getInt('perPage', 10));

        $paginator = $bookRepository->getBooksPaginator($offset, $perPage);

        $categories=$entity_manager->getRepository(Categories::class)->findAll();
        //form
        $category = new Categories();
        $form=$this->createForm(CategoriesType::class,$category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $category=$form->getData();
            $em=$this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute("manager");
        }

        return $this->render('manager/index.html.twig', [
            'books' => $paginator,
            'categories' => $categories ,
            'previous' => $offset - $perPage,
            'next'=> min(count($paginator), $offset + $perPage),
            'perpage'=>$perPage,
            'form'=> $form->createView()
        ]);
    }


    /**
     * @Route("/manager/new", name="new_book")
     */
    public function newBook(Request $request,AuthorizationCheckerInterface $authChecker):Response
    {
        //проверка прав доступа
        if (false === $authChecker->isGranted('ROLE_MANAGER')) {
            return $this->redirectToRoute('app_login');
        }
        $book = new Books();
        $form =$this->createForm(BookType::class,$book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $book=$form->getData();
            $em=$this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute("add_to",[
                'id'=>$book->getId()
            ]);
        }
        return $this->render("manager/new.html.twig",[
            'form'=>$form->createView()

        ]);
    }
    /**
     * @Route("/manager/update/{id}", name="update_book")
     */
    public function updateBook(Request $request, $id,AuthorizationCheckerInterface $authChecker)//:Response
    {
        //проверка прав доступа
        if (false === $authChecker->isGranted('ROLE_MANAGER')) {
            return $this->redirectToRoute('app_login');
        }
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
    public function deleteBook($id,AuthorizationCheckerInterface $authChecker)
    {
        //проверка прав доступа
        if (false === $authChecker->isGranted('ROLE_MANAGER')) {
            return $this->redirectToRoute('app_login');
        }
        $em=$this->getDoctrine()->getManager();
        $book=$em->getRepository(Books::class)->find($id);
        $em->remove($book);
        $em->flush();

        return $this->render('manager/delete.html.twig',[
            'name'=>$book->getTitle()
        ]);
    }
    /**
     * @Route("/manager/update_cat/{id}", name="update_cat")
     */
    public function updateCat($id,Request $request,BookRepository $bookRepository,AuthorizationCheckerInterface $authChecker)
    {
        //проверка прав доступа
        if (false === $authChecker->isGranted('ROLE_MANAGER')) {
            return $this->redirectToRoute('app_login');
        }

        $em = $this->getDoctrine()->getManager();
        $editing_category = $em->getRepository(Categories::class)->find($id);

        //переименование категории
        $form =$this->createForm(CategoriesType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $category = $form->getData();
            $editing_category->setTitle($category->getTitle());
            $em->flush();
        }



        //вывести список книг из этой категории
        $offset = max(0, $request->query->getInt('offset', 0));
        $perPage = max(1, $request->query->getInt('perPage', 10));
        $paginator=$bookRepository->getBookInCat($offset,$perPage,$id);

        //удалить выбранную книгу из категории(удалить из таблицы bic)

        return $this->render('manager/updateCat.html.twig',[
            'books'=> $paginator,
            'category' => $editing_category,
            'previous' => $offset - $perPage,
            'next'=> min(count($paginator), $offset + $perPage),
            'perpage'=>$perPage,
            'form'=> $form->createView()
        ]);

    }

    /**
     * @Route("/manager/add_to/{id}", name="add_to")
     */
    public function addBook(Request $request,$id,AuthorizationCheckerInterface $authChecker)
    {
        //проверка прав доступа
        if (false === $authChecker->isGranted('ROLE_MANAGER')) {
            return $this->redirectToRoute('app_login');
        }
        //id-индекс книги
        $form =$this->createForm(CategoriesType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            //выбрать id категории по имени
            $categoryInForm=$form->getData();
            $em=$this->getDoctrine()->getManager();
            $category=$em->getRepository(Categories::class)
                ->findOneBy(['title'=>$categoryInForm->getTitle()]);
                if($category)
                {
                    $bic=new BooksInCategories();
                    $bic->setBookId((int)$id);
                    $bic->setCategoryId($category->getId());
                    $em->persist($bic);
                    $em->flush();
                    return $this->redirectToRoute('manager');
                }

            return $this->render('manager/error.html.twig',[
                'id' => $id
            ]);
        }
        return $this->render('manager/AddTo.html.twig',[
            'form'=> $form->createView(),
            'id' => $id
        ]);
    }
    /**
     * @Route("/manager/delete_category/{id}", name="delete_category")
     */
    public function deleteCategory($id,AuthorizationCheckerInterface $authChecker)
    {
        //проверка прав доступа
        if (false === $authChecker->isGranted('ROLE_MANAGER')) {
            return $this->redirectToRoute('app_login');
        }

        $em=$this->getDoctrine()->getManager();
        $category=$em->getRepository(Categories::class)->find($id);
        $em->remove($category);
        $em->flush();

        return $this->render('manager/delete.html.twig',[
            'name'=> $category->getTitle()
        ]);
    }

    /**
     * @Route("/manager/delete_from/{catid}/{bookid}", name="delete_from")
     */
    public function deleteFrom($catid,$bookid,AuthorizationCheckerInterface $authChecker)
    {
        //проверка прав доступа
        if (false === $authChecker->isGranted('ROLE_MANAGER')) {
            return $this->redirectToRoute('app_login');
        }
        $em=$this->getDoctrine()->getManager();
        $bic=$em->getRepository(BooksInCategories::class)
            ->findOneBy(['category_id'=>$catid,'book_id'=>$bookid]);
        $em->remove($bic);
        $em->flush();

        return $this->render('manager/delete.html.twig',[
            'name'=> ""
        ]);
    }

}
