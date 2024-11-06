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
    #[Route('/api/login', name: 'login', methods: ['POST'])]
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

    #[Route('/api/cadastrar/empresa', name: 'cadastrarEmpresa', methods: ['POST'])]
    public function cadastrarEmpresa(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        $cnpj = $valoresRequest['cnpj'] ?? null;
        $nome = $valoresRequest['nome'] ?? null;

        $verificarLogin = $entityManager->getRepository(Empresa::class)->findOneBy(['cnpj' => $cnpj, 'deleted' => 0]);

        if(!$verificarLogin){
            $token = bin2hex(random_bytes(16));
            $empresa = new Empresa();
            $empresa->setNome($nome);
            $empresa->setCnpj($cnpj);
            $empresa->setToken($token);
            $empresa->setStatus(1);
            $empresa->setDeleted(0);
            $empresa->setCreatedAt(new \DateTime()); 
            $entityManager->persist($empresa);
            $entityManager->flush();

            return new JsonResponse(['mensagem' => 'success'], Response::HTTP_OK);
        }
        else{
            return new JsonResponse(['mensagem' => 'error'], Response::HTTP_OK);
        }
    }

    #[Route('/api/getEmpresas', name: 'getEmpresas', methods: ['GET'])]
    public function getEmpresas(EntityManagerInterface $entityManager): Response
    {
        $empresas = $entityManager->getRepository(Empresa::class)->findBy(['deleted' => 0]);
        
        $empresasEmArray = [];

        foreach($empresas as $empresa){
            $empresasEmArray[] = [
                'id' => $empresa->getId(),
                'nome' => $empresa->getNome(),
            ];
        }

        return new JsonResponse(['empresas' => $empresasEmArray], Response::HTTP_OK);
    }
}