<?php

namespace App\Controller\Api;

use DateTime;

use App\Entity\Experience;
use App\Service\TokenService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManagerInterface;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


class ExperienceController extends AbstractController
{
    #[Route('/api/experience', name: 'api_experience', methods: ['GET'])]
    public function getExperience(EntityManagerInterface $entityManager): JsonResponse
    {
        // Pobieramy dane o doświadczeniach z bazy danych
        $experiences = $entityManager->getRepository(Experience::class)->findBy(
            [],
            ['fromdate' => 'DESC'] // Sortowanie po polu Fromdate malejąco
        );

        // Tworzymy tablicę wyników
        $data = [];

        // Zbieramy dane z encji do formatu tablicy
        foreach ($experiences as $experience) {
            $data[] = [
                'id'            => $experience->getId(),
                'name'          => $experience->getName(),
                'company'       => $experience->getCompany(),
                'fromdate'      => $experience->getFromdate()->format('Y-m-d'),
                'todate'        => $experience->getTodate()->format('Y-m-d'),
                'current'       => $experience->getCurrent(),
                'description'   => $experience->getDescription(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/read-experience/{id}', name: 'api_cms_read_experience', methods: ['GET'])]
    public function getCmsReadExperience(
        int $id,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $experience = $entityManager->getRepository(Experience::class)->find($id);

        if ($experience) {
            $data = [
                'id'            => $experience->getId(),
                'name'          => $experience->getName(),
                'company'       => $experience->getCompany(),
                'fromdate'      => $experience->getFromdate()->format('Y-m-d'),
                'todate'        => $experience->getTodate()->format('Y-m-d'),
                'current'       => $experience->getCurrent(),
                'description'   => $experience->getDescription(),
            ];
            return new JsonResponse($data);
        }

        return new JsonResponse(['message' => 'Experience not found'], 404);
    }

    /* ------------------------------------------*/
    /* --- CMS edit --- */
    /* ------------------------------------------*/

    #[Route('/api/cms-edit-experience', name: 'api_cms_edit_experience', methods: ['PUT'])]
    public function getCmsEditExperience(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenService $tokenService // Wstrzykujemy TokenService
    ): JsonResponse {
        // Pobieramy token z nagłówka Authorization
        $token = $request->headers->get('Authorization');
    
        // Jeśli token nie jest przesyłany w nagłówku, zwracamy błąd
        if (!$token) {
            return new JsonResponse(['message' => 'Token not provided'], 400);
        }
    
        // Usuwamy prefix "Bearer " (jeśli jest) z tokena
        $token = str_replace('Bearer ', '', $token);
    
        // Używamy TokenService do weryfikacji tokena
        $decodedToken = $tokenService->parseToken($token);
    
        // Jeśli token jest niepoprawny, zwracamy błąd
        if (!$decodedToken) {
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }
    
        // Token jest poprawny, kontynuujemy logikę
        $data = json_decode($request->getContent(), true);
    
        if (isset($data['id']) && $data['id'] > 0) {
            $experience = $entityManager->getRepository(Experience::class)->find($data['id']);
            if (!$experience) {
                return new JsonResponse(['message' => 'Experience not found'], 404);
            }
        } else {
            $experience = new Experience();
        }
    
        $experience->setName($data['name']);
        $experience->setCompany($data['company']);
        $experience->setFromdate(new DateTime($data['fromdate']));
        $experience->setTodate(new DateTime($data['todate']));
        $experience->setCurrent($data['current']);
        $experience->setDescription($data['description']);
    
        $entityManager->persist($experience);
        $entityManager->flush();
    
        return new JsonResponse(['message' => 'Experience saved successfully'], 201);
    }

    #[Route('/api/cms-delete-experience/{id}', name: 'api_cms_delete_experience', methods: ['DELETE'])]
    public function deleteCmsExperience(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        TokenService $tokenService // Wstrzykujemy TokenService
    ): JsonResponse {
        // Pobieramy token z nagłówka Authorization
        $token = $request->headers->get('Authorization');

        // Jeśli token nie jest przesyłany w nagłówku, zwracamy błąd
        if (!$token) {
            return new JsonResponse(['message' => 'Token not provided'], 400);
        }

        // Usuwamy prefix "Bearer " (jeśli jest) z tokena
        $token = str_replace('Bearer ', '', $token);

        // Używamy TokenService do weryfikacji tokena
        $decodedToken = $tokenService->parseToken($token);

        // Jeśli token jest niepoprawny, zwracamy błąd
        if (!$decodedToken) {
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        // Token jest poprawny, kontynuujemy logikę
        $experience = $entityManager->getRepository(Experience::class)->find($id);

        if (!$experience) {
            return new JsonResponse(['message' => 'Experience not found'], 404);
        }

        $entityManager->remove($experience);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Experience deleted successfully'], 200);
    }

    
}
