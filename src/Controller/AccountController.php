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

use function Symfony\Component\Clock\now;

class AccountController extends AbstractController
{
    #[Route('/api/cadastrar/socio', name: 'cadastrarSocio', methods: ['POST'])]
    public function cadastrarSocio(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        $email = $valoresRequest['email'] ?? null;
        $nome = $valoresRequest['nome'] ?? null;
        $senha = sha1($valoresRequest['senha']) ?? null;
        $empresa = $valoresRequest['empresaSelecionada'] ?? null;

        $verificarLogin = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email, 'deleted' => 0]);

        if(!$verificarLogin){
            $token = bin2hex(random_bytes(16));
            $usuario = new Usuario();
            $usuario->setEmail($email);
            $usuario->setNome($nome);
            $usuario->setSenha($senha);
            $usuario->setAdmin(false);
            $usuario->setToken($token);
            $usuario->setStatus(1);
            $usuario->setDeleted(0);
            $usuario->setIdEmpresa($empresa);
            $usuario->setCreatedAt(new \DateTime()); 
            $entityManager->persist($usuario);
            $entityManager->flush();

            return new JsonResponse(['mensagem' => 'success'], Response::HTTP_OK);
        }
        else{
            return new JsonResponse(['mensagem' => 'error'], Response::HTTP_OK);
        }
    }
}