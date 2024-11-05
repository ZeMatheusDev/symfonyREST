<?php

namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class AccountController extends AbstractController
{
    #[Route('/', name: 'app_account')]
    public function index(EntityManagerInterface $entityManager){
        $usuarios = $entityManager->getRepository(Usuario::class)->findBy(['deleted' => '0']);
        dump($usuarios);       
        die();
    }
}
