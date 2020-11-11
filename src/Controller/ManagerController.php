<?php

namespace App\Controller;

use App\Entity\Books;
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
        if (false === $authChecker->isGranted('ROLE_MANAGER')) {
            return $this->redirectToRoute('app_login');
        }
        $entity_manager=$this->getDoctrine()->getManager();
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $bookRepository->getBooksPaginator($offset);

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
            'previous' => $offset - BookRepository::PAGINATOR_PER_PAGE,
            'next'=> min(count($paginator), $offset + BookRepository::PAGINATOR_PER_PAGE),
            'form'=> $form->createView()
        ]);
    }

}
