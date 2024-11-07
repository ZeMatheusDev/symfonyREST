<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'login', methods: ['POST'])]
     /**
     * Faz login no sistema.
     *
     * Essa API e responsavel por fazer login no sistema, esperando o email e senha para autenticar.
     */
    public function login(Request $request, EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        $email = $valoresRequest['email'] ?? null;
        $senha = sha1($valoresRequest['senha']) ?? null;

        $verificarLogin = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email, 'senha' => $senha, 'deleted' => 0]);
        
        if($verificarLogin){
            $token = $jwtManager->create($verificarLogin);
            return new JsonResponse(['mensagem' => 'success', 'token' => $token], Response::HTTP_OK);
        }
        else{
            return new JsonResponse(['mensagem' => 'error'], Response::HTTP_OK);
        }

    }
}
