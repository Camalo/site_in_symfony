<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(AuthorizationCheckerInterface $authChecker): Response
    {
        if (false === $authChecker->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('manager');
        }

        $em=$this->getDoctrine()->getManager();
        $users=$em->getRepository(User::class)->findAll();
        //вывод пользователей в таблице с кнопками 'update' ,'delete'
        //кнопка добавить юзера
        return $this->render('admin/index.html.twig', [
            'users' => $users
        ]);
    }
    /**
     * @Route("/admin/create", name="create_user")
     */
    public function newUser(Request $request)
    {
        $user = new Books();
        $form =$this->createForm(UserType::class,$user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $user=$form->getData();
            $em=$this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("admin",[
            ]);
        }
        return $this->render('admin/newUser.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/admin/update/{id}", name="update_user")
     */
    public function updateUser(Request $request,UserPasswordEncoderInterface $encoder, $id)
    {
        //хз
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(User::class)->find($id);

        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $registered_user = $em->getRepository(User::class)->findOneBy(['id'=>$id]);
            //получить пассворт из формы

            //$password=$encoder->encodePassword($user,strval($request->request->get('password')));
            $user=$form->getData();
            $password=$password=$encoder->encodePassword($user,$user->getPassword());
            $registered_user->setPassword($password);
            $registered_user->setEmail($user->getEmail());
            //внести его в таблицу users
            $em->flush();

            return $this->redirectToRoute("admin");
        }
        return $this->render('admin/updateUser.html.twig',[
            'form'=>$form->createView()
        ]);
    }
    /**
     * @Route("/admin/delete/{id}", name="delete_user")
     */
    //delete, нельзя удалить себя(сделать проверку)
    public function deleteUser($id)
    {

        /*поменять
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(User::class)->find($id);
        $em->remove($user);
        $em->flush();
        */
        return $this->render('admin/deleteUser.html.twig');
    }
}
