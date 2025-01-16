<?php

namespace App\Controller\Api;

use App\Entity\Experience;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use DateTime;

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


    // Pobieramy dane o doświadczeniach z bazy danych do CMSu
    #[Route('/api/cms-experience', name: 'api_cms_experience', methods: ['GET'])]
    public function getCmsExperience(
        Request $request,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        // Pobieramy token z nagłówka Authorization
        $token = $request->headers->get('Authorization');

        // Jeśli token nie jest przesyłany w nagłówku, zwracamy błąd
        if (!$token) {
            return new JsonResponse(['message' => 'Token not provided'], 400);
        }

        // Usuwamy prefix "Bearer " (jeśli jest) z tokena
        $token = str_replace('Bearer ', '', $token);

        try {
            // Próba weryfikacji tokena
            $decodedToken = $jwtManager->parse($token);

            // Jeśli token jest poprawny, zwróć dane
            if ($decodedToken) {
                // Pobieramy dane o doświadczeniach z bazy danych
                $experiences = $entityManager->getRepository(Experience::class)->findBy(
                    [], // Brak kryteriów filtrowania (pobierz wszystkie wpisy)
                    ['fromdate' => 'DESC'] // Sortowanie po polu fromDate malejąco
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
            } else {
                return new JsonResponse(['message' => 'Unauthorized'], 401); // Unauthorized
            }
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Unauthorized'], 401); // Unauthorized
        }
    }

    
    // Zapis danych o doświadczeniach do bazy danych w CMS
    #[Route('/api/cms-edit-experience', name: 'api_cms_edit_experience', methods: ['PUT'])]
    public function getCmsEditExperience(
        Request $request,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        // Pobieramy token z nagłówka Authorization
        $token = $request->headers->get('Authorization');

        // Jeśli token nie jest przesyłany w nagłówku, zwracamy błąd
        if (!$token) {
            return new JsonResponse(['message' => 'Token not provided'], 400);
        }

        // Usuwamy prefix "Bearer " (jeśli jest) z tokena
        $token = str_replace('Bearer ', '', $token);

        try {
            // Próba weryfikacji tokena
            

            $decodedToken = $jwtManager->parse($token);
            // Jeśli token jest poprawny, zwróć dane
            if ($decodedToken) {
                
                // Pobieramy dane z body żądania
                $data = json_decode($request->getContent(), true);

                    // Jeśli mamy id w danych, sprawdzamy, czy jest większe od 0 (edycja)
                    if (isset($data['id']) && $data['id'] > 0) {
                        // Jeśli id jest większe od 0, próbujemy znaleźć encję do edycji
                        $experience = $entityManager->getRepository(Experience::class)->find($data['id']);
                        
                        if (!$experience) {
                            return new JsonResponse(['message' => 'Experience not found'], 404); // Jeśli nie znaleziono doświadczenia
                        }
                    } else {
                        // Jeśli id jest 0 lub nie ma go w danych, tworzymy nowy obiekt Experience
                        $experience = new Experience();
                    }
                    $experience->setName($data['name']);
                    $experience->setCompany($data['company']);
                    $experience->setFromdate(new DateTime($data['fromdate']));
                    $experience->setTodate(new DateTime($data['todate']));
                    $experience->setCurrent($data['current']);
                    $experience->setDescription($data['description']);

                    // Zapisujemy do bazy danych
                    $entityManager->persist($experience); // Przekazujemy encję do Doctrine
                    $entityManager->flush(); // Wykonujemy zapytanie do bazy danych

                    return new JsonResponse(['message' => 'Experience created successfully'], 201); // Odpowiedź na sukces
                
                
            } else {
                return new JsonResponse(['message' => 'Unauthorized1'], 401); // Unauthorized
            }
               
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Unauthorized2'], 401); // Unauthorized
        }
    }

    
    // Endpoint do pobrania doświadczenia na podstawie ID
    #[Route('/api/read-experience/{id}', name: 'api_cms_read_experience', methods: ['GET'])]
    public function getCmsReadExperience(
        int $id, // Automatyczne mapowanie ID z URL
        EntityManagerInterface $entityManager
    ): JsonResponse {
       
        // Pobieramy dane z body żądania
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
        }else{
            return new JsonResponse(['message' => 'Experience not found'], 404); // Jeśli nie znaleziono doświadczenia
        }
    }

    // Usuwamy dane o doświadczeniu z bazy danych
    #[Route('/api/cms-delete-experience/{id}', name: 'api_cms_delete_experience', methods: ['DELETE'])]
    public function deleteCmsExperience(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        // Pobieramy token z nagłówka Authorization
        $token = $request->headers->get('Authorization');

        // Jeśli token nie jest przesyłany w nagłówku, zwracamy błąd
        if (!$token) {
            return new JsonResponse(['message' => 'Token not provided'], 400);
        }

        // Usuwamy prefix "Bearer " (jeśli jest) z tokena
        $token = str_replace('Bearer ', '', $token);

        try {
            // Próba weryfikacji tokena
            $decodedToken = $jwtManager->parse($token);

            // Jeśli token jest poprawny, kontynuujemy
            if ($decodedToken) {
                // Pobieramy wpis o doświadczeniu z bazy danych
                $experience = $entityManager->getRepository(Experience::class)->find($id);

                // Sprawdzamy, czy wpis istnieje
                if (!$experience) {
                    return new JsonResponse(['message' => 'Experience not found'], 404);
                }

                // Usuwamy wpis z bazy danych
                $entityManager->remove($experience);
                $entityManager->flush();

                return new JsonResponse(['message' => 'Experience deleted successfully'], 200);
            } else {
                return new JsonResponse(['message' => 'Unauthorized'], 401); // Unauthorized
            }
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Unauthorized'], 401); // Unauthorized
        }
    }

}
