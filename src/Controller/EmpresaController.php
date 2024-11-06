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
