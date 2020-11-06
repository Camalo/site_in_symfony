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
    //hash_password=$argon2i$v=19$m=65536,t=4,p=1$dDV5bHQ2V01vY1JibjRsMg$xPp5Sy4ddiWSM0nONn3AiBT+4W0EGCHtmAyIEGOXic4
    /*
    public function index(): Response
    {
        //получить данные из формы
        return $this->render('user/login.html.twig'
        );
    }*/
    /**
     * @Route("/logina", name="login")
     */
    public function actionForm(Request $request)
    {
        $form =$this->createForm(UserLoginType::class);



        return $this->render('user/login.html.twig',[
            'login_form'=>$form->createView()
            ]);
    }



    /**
     * @Route("/admina", name="task_success")
     */
    public function success()
    {
        return $this->render('user/admin.html.twig',[
           'message'=>'успешный'
        ]);
    }



}
