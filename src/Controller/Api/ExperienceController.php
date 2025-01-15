<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ExperienceController extends AbstractController
{
    #[Route('/api/experience', name: 'api_experience', methods: ['GET'])]
    public function getExperience(): JsonResponse
    {
        // Przykładowe dane - zastąp później danymi z bazy
        $data = [
            [
                'id' => 1,
                'title' => 'Frontend Developer',
                'company' => 'Firma X',
                'dateFrom' => 'styczeń 2020',
                'dateTo' => 'current',
                'description' => 'Tworzenie dynamicznych aplikacji webowych w technologii React.js.',
            ],
            [
                'id' => 2,
                'title' => 'Backend Developer',
                'company' => 'Firma Y',
                'dateFrom' => 'lipiec 2018',
                'dateTo' => 'grudzień 2019',
                'description' => 'Rozwój aplikacji backendowych z wykorzystaniem CakePHP oraz MySQL.',
            ],
            [
                'id' => 3,
                'title' => 'Junior Web Developer',
                'company' => 'Firma Zz',
                'dateFrom' => 'wrzesień 2016',
                'dateTo' => 'czerwiec 2018',
                'description' => 'Wsparcie w tworzeniu stron internetowych oraz wdrażanie funkcjonalności w jQuery.',
            ],
        ];

        return new JsonResponse($data);
    }
}
