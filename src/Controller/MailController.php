<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{
    /**
     * @Route("login/mail", name="mail")
     */
    public function index(\Swift_Mailer $mailer, Request $request): Response
    {
        $user = new User();
        $form=$this->createForm(UserEmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            //'Smetanina2002sme@gmail.com'
            $user = $form->getData();
            // Create a message
            $message = (new \Swift_Message())
                //->setTo('camalovalsu29@gmail.com')
                ->setTo($user->getEmail())
                ->setFrom('symfonist.mailer@gmail.com')
                ->setSubject('наконец-то получилось')
                ->setBody(
                    $this->renderView(
                        '/mail/email.html.twig'
                    ),
                    'text/html'
                );
            $mailer->send($message);

            return $this->redirectToRoute('homepage');
        }


        return $this->render('mail/index.html.twig',[
            'form'=>$form->createView()
        ]);
    }
}
