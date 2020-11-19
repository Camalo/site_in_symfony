<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEmailType;
use App\Form\PasswordType;
use App\Form\UserPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MailController extends AbstractController
{
    /**
     * @Route("login/mail", name="mail")
     */
    public function index(\Swift_Mailer $mailer, Request $request): Response
    {
        $user = new User();
        $form=$this->createForm(  UserEmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $user = $form->getData();
            //find a user
            $em=$this->getDoctrine()->getManager();
            $registered_user = $em->getRepository(User::class)->findOneBy(['email'=>$user->getEmail()]);
            if ($registered_user)
            {
                // Create a message
                $message = (new \Swift_Message())
                    ->setTo($registered_user->getEmail())
                    ->setFrom('symfonist.mailer@gmail.com')
                    ->setSubject('Воостановление доступа')
                    ->setBody(
                        $this->renderView(
                            '/mail/email.html.twig',
                            ['userid'=>$registered_user->getId()]
                        ),
                        'text/html'
                    );
                $mailer->send($message);

                return $this->render('mail/waiting.html.twig');

            }


            return $this->render('mail/error.html.twig');
        }


        return $this->render('mail/index.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("login/reset", name="resetPassword")
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $userid=$_GET['userid'];

        $form=$this->createForm(UserPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {

            //найти юзера в бд
            $em=$this->getDoctrine()->getManager();
            $registered_user = $em->getRepository(User::class)->findOneBy(['id'=>$userid]);
            //получить пассворт из формы

            //$password=$encoder->encodePassword($user,strval($request->request->get('password')));
            $user=$form->getData();
            $password=$password=$encoder->encodePassword($user,$user->getPassword());
            $registered_user->setPassword($password);
            //внести его в таблицу users
            $em->flush();
            return $this ->redirectToRoute('app_login');
        }
        return $this->render('mail/resetPassword.html.twig',[
            'form'=>$form->createView()
        ]);
    }

}
