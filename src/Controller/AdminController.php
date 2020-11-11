<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
        $users=$em->getRepository(User::class)->findBy(['roles'=> '\"ROLE_MANAGER\"']);
        //вывод пользователей в таблице с кнопками 'update' ,'delete'
        //кнопка добавить юзера
        return $this->render('admin/index.html.twig', [
            'users' => $users
        ]);
    }
}
