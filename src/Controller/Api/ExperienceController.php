<?php
namespace App\Controller\Api;

use DateTime;
use App\DTO\ExperienceDTO;
use App\Entity\Experience;
use App\Service\ExperienceService;
use App\Service\TokenService;
use App\Service\JsonResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Log\LoggerInterface;


class ExperienceController extends AbstractController
{
    private JsonResponseService $jsonResponseService;
    private TokenService $tokenService;
    private ExperienceService $experienceService;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;

    // Wstrzykiwanie serwisów w konstruktorze
    public function __construct(
        JsonResponseService $jsonResponseService,
        TokenService $tokenService,
        ExperienceService $experienceService,
        ValidatorInterface $validator,
        LoggerInterface $logger
    )
    {
        $this->jsonResponseService = $jsonResponseService;
        $this->tokenService = $tokenService;
        $this->experienceService = $experienceService;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    // Pomocnicza metoda do generowania odpowiedzi JSON
    private function createJsonResponseWithData(array $data, int $statusCode = 200): JsonResponse
    {
        return $this->jsonResponseService->createJsonResponse($data, $statusCode);
    }

    #[Route('/api/experience', name: 'api_experience', methods: ['GET'])]
    public function getExperience(Request $request): JsonResponse
    {
        try {
            // Parametry do paginacji
            $page = $request->query->getInt('page', 1); // domyślnie strona 1
            $limit = $request->query->getInt('limit', 50); // domyślnie 10 elementów na stronę

            // Pobieramy dane z paginacją
            $experiencesQuery = $this->experienceService->findAllOrderedByFromDateQuery(); // Zakładam, że masz tę metodę w service

            // Paginacja
            $paginator = new Paginator($experiencesQuery);
            $paginator->getQuery()
                ->setFirstResult(($page - 1) * $limit) // Początkowy element
                ->setMaxResults($limit); // Limit na stronę

            $totalCount = count($paginator); // Całkowita liczba elementów
            $experiences = iterator_to_array($paginator);

            $data = [];
            foreach ($experiences as $experience) {
                $data[] = [
                    'id'          => $experience->getId(),
                    'name'        => $experience->getName(),
                    'company'     => $experience->getCompany(),
                    'fromdate'    => $experience->getFromdate()?->format('Y-m-d'),
                    'todate'      => $experience->getTodate()?->format('Y-m-d'),
                    'current'     => $experience->getCurrent(),
                    'description' => $experience->getDescription(),
                ];
            }

            // Dodajemy informacje o paginacji w odpowiedzi
            return $this->createJsonResponseWithData([
                'data' => $data,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages'  => ceil($totalCount / $limit),
                    'total_count'  => $totalCount,
                    'limit'        => $limit
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching experiences: ' . $e->getMessage());
            return $this->createJsonResponseWithData(['message' => 'Error fetching experiences'], 500);
        }
    }

    #[Route('/api/cms-edit-experience', name: 'api_cms_edit_experience', methods: ['PUT'])]
    public function getCmsEditExperience(Request $request): JsonResponse
    {
        try {

            $token = $request->headers->get('Authorization');
            if (!$this->tokenService->validateToken($token)) {
                return $this->createJsonResponseWithData(['message' => 'Unauthorized'], 401);
            }
            
            // Dekodowanie danych wejściowych
            $data = json_decode($request->getContent(), true);
            $experienceDTO = new ExperienceDTO();
            $experienceDTO->name = $data['name'];
            $experienceDTO->company = $data['company'];
            $experienceDTO->fromdate = $data['fromdate'];
            $experienceDTO->todate = $data['todate'];
            $experienceDTO->current = $data['current'];
            $experienceDTO->description = $data['description'];

            // Walidacja DTO
            $errors = $this->validator->validate($experienceDTO);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                $this->logger->warning('Validation errors: ' . implode(', ', $errorMessages));
                return $this->createJsonResponseWithData(['errors' => $errorMessages], 400);
            }

            // Jeśli brak błędów, wykonujemy operację
            $experience = (isset($data['id']) && $data['id'] > 0) ? $this->experienceService->findById($data['id']) : new Experience();

            if (!$experience) {
                $this->logger->error('Experience not found for id: ' . $data['id']);
                throw new NotFoundHttpException('Experience not found');
            }

            $experience->setName($experienceDTO->name);
            $experience->setCompany($experienceDTO->company);
            $experience->setFromdate(new DateTime($experienceDTO->fromdate));
            $experience->setTodate(new DateTime($experienceDTO->todate));
            $experience->setCurrent($experienceDTO->current);
            $experience->setDescription($experienceDTO->description);

            $this->experienceService->saveExperience($experience);

            return $this->createJsonResponseWithData(['message' => 'Experience saved successfully'], 201);
        } catch (\Exception $e) {
            $this->logger->error('Error editing experience: ' . $e->getMessage());
            return $this->createJsonResponseWithData(['message' => 'Error editing experience'], 500);
        }
    }

    #[Route('/api/cms-delete-experience/{id}', name: 'api_cms_delete_experience', methods: ['DELETE'])]
    public function deleteCmsExperience(Request $request, int $id): JsonResponse
    {
        try {
            // Pobieramy token z nagłówka
            $token = $request->headers->get('Authorization');
            
            // Weryfikujemy token
            if (!$this->tokenService->validateToken($token)) {
                return $this->createJsonResponseWithData(['message' => 'Unauthorized'], 401);
            }
            
            // Znajdź doświadczenie po ID
            $experience = $this->experienceService->findById($id);

            if (!$experience) {
                $this->logger->error('Experience not found for id: ' . $id);
                return $this->createJsonResponseWithData(['message' => 'Experience not found'], 404);
            }

            // Usuwamy doświadczenie
            $this->experienceService->deleteExperience($experience);

            return $this->createJsonResponseWithData(['message' => 'Experience deleted successfully'], 200);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting experience: ' . $e->getMessage());
            return $this->createJsonResponseWithData(['message' => 'Error deleting experience'], 500);
        }
    }

    #[Route('/api/read-experience/{id}', name: 'api_cms_read_experience', methods: ['GET'])]
    public function getCmsReadExperience(int $id): JsonResponse
    {
        try {
            $experience = $this->experienceService->findById($id);

            if (!$experience) {
                $this->logger->error('Experience not found for id: ' . $id);
                return $this->createJsonResponseWithData(['message' => 'Experience not found'], 404);
            }

            $data = [
                'id'          => $experience->getId(),
                'name'        => $experience->getName(),
                'company'     => $experience->getCompany(),
                'fromdate'    => $experience->getFromdate()?->format('Y-m-d'),
                'todate'      => $experience->getTodate()?->format('Y-m-d'),
                'current'     => $experience->getCurrent(),
                'description' => $experience->getDescription(),
            ];

            return $this->createJsonResponseWithData($data);
        } catch (\Exception $e) {
            $this->logger->error('Error reading experience: ' . $e->getMessage());
            return $this->createJsonResponseWithData(['message' => 'Error reading experience'], 500);
        }
    }
}
