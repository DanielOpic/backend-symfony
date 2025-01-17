<?php
// src/Service/JsonResponseService.php
namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponseService
{
    /**
     * Ułatwione zwracanie odpowiedzi JSON.
     */
    public function createJsonResponse(array $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }
}
