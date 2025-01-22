<?php
namespace App\Controller\Api;

use App\DTO\SkillsDTO;
use App\DTO\SkillsTypeDTO;
use App\Entity\Skills;
use App\Service\JsonResponseService;
use App\Service\SkillsService;
use App\Service\SkillsTypeService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class SkillsController extends AbstractController
{
    private JsonResponseService $jsonResponseService;
    private SkillsService $skillsService;
    private SkillsTypeService $skillsTypeService;
    private LoggerInterface $logger;

    public function __construct(
        JsonResponseService $jsonResponseService,
        SkillsService $skillsService,
        SkillsTypeService $skillsTypeService,
        LoggerInterface $logger
    ) {
        $this->jsonResponseService = $jsonResponseService;
        $this->skillsService = $skillsService;
        $this->skillsTypeService = $skillsTypeService;
        $this->logger = $logger;
    }

    private function createJsonResponseWithData(array $data, int $statusCode = 200): JsonResponse
    {
        return $this->jsonResponseService->createJsonResponse($data, $statusCode);
    }

    #[Route('/api/skills', name: 'api_skills', methods: ['GET'])]
    public function getSkills(Request $request): JsonResponse
    {
        try {

            // Pobieramy dane
            $data = $this->skillsTypeService->getGroupedSkillsByType();
            
            return $this->createJsonResponseWithData([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching Skills: ' . $e->getMessage());
            return $this->createJsonResponseWithData(['message' => 'Error fetching Skills'], 500);
        }
    }
}
