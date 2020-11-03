<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserLoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    /*
    public function index(): Response
    {
        //получить данные из формы
        return $this->render('user/login.html.twig'
        );
    }*/
    /**
     * @Route("/login", name="login")
     */
    public function actionForm(Request $request)
    {
        $form =$this->createForm(UserLoginType::class);
        //обработка данных
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            //check specialchars
            $data = $form->getData();
            // запрос к бд (выборка юзера с этим паролем и логином)
            $em=$this->getDoctrine()->getManager();
            $user=$em->getRepository(User::class)->
            findOneBy(['login'=>$data->getLogin(), 'password'=> $data->getPassword()]);
            // есть ли совпадение с этим логином и паролем в бд
            if ($user == null) return  $this->redirectToRoute('user/login.html.twig');

            $user_role=$user->getRole();
            // Переход на страницу админа
            if ($user_role=='admin') return $this->render('user/admin.html.twig');
            // Переход на страницу манагера
            return $this->render('user/manager.html.twig');
        }


        return $this->render('user/login.html.twig',[
            'form'=>$form->createView()
            ]);
    }



    /**
     * @Route("/admin", name="task_success")
     */
    public function success()
    {
        return $this->render('user/admin.html.twig',[
           'message'=>'успешный'
        ]);
    }



}
