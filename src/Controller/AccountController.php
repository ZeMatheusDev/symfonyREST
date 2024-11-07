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
     /**
     * Cadastro de socio.
     *
     * Essa API e responsavel por fazer cadastro de socios deixando o email como unico, pedindo email, nome, senha e empresa vinculada.
     */
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

    #[Route('/api/getUsuarios', name: 'getUsuarios', methods: ['GET'])]
    /**
     * Pega todos socios cadastrados.
     *
     * Essa API e responsavel por pegar todos socios cadastrados no sistema, listando o id, token, email, nome e empresa vinculada.
     */
    public function getUsuarios(EntityManagerInterface $entityManager): Response
    {
        $usuarios = $entityManager->getRepository(Usuario::class)->findBy(['deleted' => 0], ['id' => 'ASC']);
        
        $usuariosEmArray = [];

        foreach($usuarios as $usuario){
            $usuariosEmArray[] = [
                'id' => $usuario->getId(),
                'token' => $usuario->getToken(), 
                'email' => $usuario->getEmail(),
                'id_empresa' =>$usuario->getIdEmpresa(),
                'nome' => $usuario->getNome(),
            ];
        }

        return new JsonResponse(['usuarios' => $usuariosEmArray], Response::HTTP_OK);
    }

    #[Route('/api/editUsuario', name: 'editUsuario', methods: ['POST'])]
     /**
     * Pega um socio especifico.
     *
     * Essa API e responsavel por pegar um socio especifico no sistema, esperando o token do mesmo e retornando o id, token, nome, email e empresa vinculada como response.
     */
    public function editUsuario(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        $token = $valoresRequest['token'] ?? null; 
        
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['deleted' => 0, 'token' => $token]);

        $empresaEmArray[] = [
            'id' => $usuario->getId(),
            'token' => $usuario->getToken(), 
            'email' => $usuario->getEmail(),
            'id_empresa' =>$usuario->getIdEmpresa(),
            'nome' => $usuario->getNome(),
        ];
    

        return new JsonResponse(['empresa' => $empresaEmArray], Response::HTTP_OK);
    }

    #[Route('/api/updateUsuario', name: 'updateUsuario', methods: ['POST'])]
     /**
     * Atualiza um socio especifico.
     *
     * Essa API e responsavel por pegar um socio especifico no sistema e atualizar o mesmo, ele espera o token do socio, o nome, email, empresa vinculada para atualizar e caso envie a senha, se nao enviar a senha, ele deixa a atual.
     */
    public function updateUsuario(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        $token = $valoresRequest['token'] ?? null; 
        $nome = $valoresRequest['nome'] ?? null; 
        $email = $valoresRequest['email'] ?? null;
        $empresaSelecionada = $valoresRequest['empresaSelecionada'] ?? null;
        if($valoresRequest['senha'] != ''){
            $senha = sha1($valoresRequest['senha']) ?? null;
        }
        
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['deleted' => 0, 'token' => $token]);

        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario não encontrado'], Response::HTTP_NOT_FOUND);
        }

        $usuario->setNome($nome);
        $usuario->setEmail($email);
        $usuario->setIdEmpresa($empresaSelecionada);
        if($valoresRequest['senha'] != ''){
            $usuario->setSenha($senha);
        }
        $entityManager->persist($usuario);
        $entityManager->flush();

        return new JsonResponse(['mensagem' => 'success'], Response::HTTP_OK);
    }

    #[Route('/api/deleteUsuario', name: 'deleteUsuario', methods: ['POST'])]
     /**
     * Deleta um socio especifico.
     *
     * Essa API e responsavel por pegar um socio especifico no sistema e deletar o mesmo, esperando o token do mesmo.
     */
    public function deleteUsuario(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        $token = $valoresRequest['token'] ?? null; 
        
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['token' => $token]);

        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuário não encontrado'], Response::HTTP_NOT_FOUND);
        }

        $usuario->setDeleted(1);
        $entityManager->persist($usuario);
        $entityManager->flush();

        return new JsonResponse(['mensagem' => 'success'], Response::HTTP_OK);
    }

    #[Route('/api/restaurarUsuarios', name: 'restaurarUsuarios', methods: ['POST'])]
     /**
     * Restaura todos socios do sistema.
     *
     * Essa API e responsavel por pegar todos socios no sistema e restaurar os mesmos, deixando de ser delatados.
     */
    public function restaurarUsuarios(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        
        $usuarios = $entityManager->getRepository(Usuario::class)->findAll();

        foreach($usuarios as $usuario){
            $usuario->setDeleted(0);
            $entityManager->persist($usuario);
            $entityManager->flush();
        }

        return new JsonResponse(['mensagem' => 'success'], Response::HTTP_OK);
    }
}