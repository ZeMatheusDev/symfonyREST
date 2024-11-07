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

class EmpresaController extends AbstractController
{
    #[Route('/api/cadastrar/empresa', name: 'cadastrarEmpresa', methods: ['POST'])]
     /**
     * Cadastro de empresa.
     *
     * Essa API e responsavel por fazer cadastro de empresas deixando o CNPJ como unico, pedindo cnpj e nome.
     */
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
    /**
     * Pega todas empresas cadastradas.
     *
     * Essa API e responsavel por pegar todas empresas cadastradas no sistema, listando o id, token, cnpj e nome.
     */
    public function getEmpresas(EntityManagerInterface $entityManager): Response
    {
        $empresas = $entityManager->getRepository(Empresa::class)->findBy(['deleted' => 0], ['id' => 'ASC']);
        
        $empresasEmArray = [];

        foreach($empresas as $empresa){
            $empresasEmArray[] = [
                'id' => $empresa->getId(),
                'token' => $empresa->getToken(), 
                'cnpj' => $empresa->getCnpj(),
                'nome' => $empresa->getNome(),
            ];
        }

        return new JsonResponse(['empresas' => $empresasEmArray], Response::HTTP_OK);
    }

    #[Route('/api/minhaEmpresa', name: 'minhaEmpresa', methods: ['POST'])]
     /**
     * Pega uma empresa especifica.
     *
     * Essa API e responsavel por pegar uma empresa especifica no sistema, esperando o email do usuario para buscar a empresa do mesmo, retornando o id, token, nome e cnpj como response.
     */
    public function minhaEmpresa(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        $email = $valoresRequest['email'] ?? null; 

        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['deleted' => 0, 'email' => $email]);

        
        $empresa = $entityManager->getRepository(Empresa::class)->findOneBy(['deleted' => 0, 'id' => $usuario->getIdEmpresa()]);

        $empresaEmArray[] = [
            'id' => $empresa->getId(),
            'token' => $empresa->getToken(), 
            'nome' => $empresa->getNome(),
            'cnpj' => $empresa->getCnpj(),
        ];
    

        return new JsonResponse(['empresa' => $empresaEmArray], Response::HTTP_OK);
    }

    #[Route('/api/editEmpresa', name: 'editEmpresa', methods: ['POST'])]
    /**
     * Pega uma empresa especifica.
     *
     * Essa API e responsavel por pegar uma empresa especifica no sistema, esperando o token da mesma e retornando o id, token, nome e cnpj como response.
     */
    public function editEmpresa(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        $token = $valoresRequest['token'] ?? null; 
        
        $empresa = $entityManager->getRepository(Empresa::class)->findOneBy(['deleted' => 0, 'token' => $token]);

        $empresaEmArray[] = [
            'id' => $empresa->getId(),
            'token' => $empresa->getToken(), 
            'nome' => $empresa->getNome(),
            'cnpj' => $empresa->getCnpj(),
        ];
    

        return new JsonResponse(['empresa' => $empresaEmArray], Response::HTTP_OK);
    }

    #[Route('/api/updateEmpresa', name: 'updateEmpresa', methods: ['POST'])]
     /**
     * Atualiza uma empresa especifica.
     *
     * Essa API e responsavel por pegar uma empresa especifica no sistema e atualizar a mesma, ele espera o token da empresa, o nome e o cnpj para atualizar.
     */
    public function updateEmpresa(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        $token = $valoresRequest['token'] ?? null; 
        $nome = $valoresRequest['nome'] ?? null; 
        $cnpj = $valoresRequest['cnpj'] ?? null;
        
        $empresa = $entityManager->getRepository(Empresa::class)->findOneBy(['deleted' => 0, 'token' => $token]);

        if (!$empresa) {
            return new JsonResponse(['error' => 'Empresa não encontrada'], Response::HTTP_NOT_FOUND);
        }

        $empresa->setNome($nome);
        $empresa->setCnpj($cnpj);
        $entityManager->persist($empresa);
        $entityManager->flush();

        return new JsonResponse(['mensagem' => 'success'], Response::HTTP_OK);
    }

    #[Route('/api/deleteEmpresa', name: 'deleteEmpresa', methods: ['POST'])]
     /**
     * Deleta uma empresa especifica.
     *
     * Essa API e responsavel por pegar uma empresa especifica no sistema e deletar a mesma, esperando o token da mesma.
     */
    public function deleteEmpresa(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        $token = $valoresRequest['token'] ?? null; 
        
        $empresa = $entityManager->getRepository(Empresa::class)->findOneBy(['token' => $token]);

        if (!$empresa) {
            return new JsonResponse(['error' => 'Empresa não encontrada'], Response::HTTP_NOT_FOUND);
        }

        $empresa->setDeleted(1);
        $entityManager->persist($empresa);
        $entityManager->flush();

        return new JsonResponse(['mensagem' => 'success'], Response::HTTP_OK);
    }

    #[Route('/api/restaurarEmpresas', name: 'restaurarEmpresas', methods: ['POST'])]
     /**
     * Restaura todas empresas do sistema.
     *
     * Essa API e responsavel por pegar todas empresas no sistema e restaurar as mesmas, deixando de ser delatadas.
     */
    public function restaurarEmpresas(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoresRequest = json_decode($request->getContent(), true);
        
        $empresas = $entityManager->getRepository(Empresa::class)->findAll();

        foreach($empresas as $empresa){
            $empresa->setDeleted(0);
            $entityManager->persist($empresa);
            $entityManager->flush();
        }

        return new JsonResponse(['mensagem' => 'success'], Response::HTTP_OK);
    }
}
