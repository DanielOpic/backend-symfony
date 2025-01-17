<?php
namespace App\Controller\Api;

use DateTime;
use App\DTO\PortfolioDTO;
use App\Entity\Portfolio;
use App\Service\PortfolioService;
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


class PortfolioController extends AbstractController
{
    private JsonResponseService $jsonResponseService;
    private TokenService $tokenService;
    private PortfolioService $portfolioService;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;

    // Wstrzykiwanie serwisów w konstruktorze
    public function __construct(
        JsonResponseService $jsonResponseService,
        TokenService $tokenService,
        PortfolioService $portfolioService,
        ValidatorInterface $validator,
        LoggerInterface $logger
    )
    {
        $this->jsonResponseService = $jsonResponseService;
        $this->tokenService = $tokenService;
        $this->portfolioService = $portfolioService;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    // Pomocnicza metoda do generowania odpowiedzi JSON
    private function createJsonResponseWithData(array $data, int $statusCode = 200): JsonResponse
    {
        return $this->jsonResponseService->createJsonResponse($data, $statusCode);
    }

    #[Route('/api/portfolio', name: 'api_portfolio', methods: ['GET'])]
    public function getPortfolio(Request $request): JsonResponse
    {
        try {
            // Parametry do paginacji
            $page = $request->query->getInt('page', 1); // domyślnie strona 1
            $limit = $request->query->getInt('limit', 50); // domyślnie 50 elementów na stronę

            // Pobieramy dane z paginacją
            $portfolioQuery = $this->portfolioService->findAllOrderedByDateQuery(); // Zakładam, że masz tę metodę w service

            // Paginacja
            $paginator = new Paginator($portfolioQuery);
            $paginator->getQuery()
                ->setFirstResult(($page - 1) * $limit) // Początkowy element
                ->setMaxResults($limit); // Limit na stronę

            $totalCount = count($paginator); // Całkowita liczba elementów
            $portfolios = iterator_to_array($paginator);

            $data = [];
            foreach ($portfolios as $portfolio) {
                $data[] = [
                    'id'            => $portfolio->getId(),
                    'name'          => $portfolio->getName(),
                    'description'   => $portfolio->getDescription(),
                    'link'          => $portfolio->getLink(),
                    'fromdate'      => $portfolio->getFromdate()?->format('Y-m-d'),
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
            $this->logger->error('Error fetching portfolio: ' . $e->getMessage());
            return $this->createJsonResponseWithData(['message' =>  'Error fetching portfolios'.$e->getMessage()], 500);
        }
    }

    #[Route('/api/cms-edit-portfolio', name: 'api_cms_edit_portfolio', methods: ['PUT'])]
    public function getCmsEditPortfolio(Request $request): JsonResponse
    {
        try {

            $token = $request->headers->get('Authorization');
            if (!$this->tokenService->validateToken($token)) {
                return $this->createJsonResponseWithData(['message' => 'Unauthorized'], 401);
            }
            
            // Dekodowanie danych wejściowych
            $data = json_decode($request->getContent(), true);
            $portfolioDTO = new PortfolioDTO();
            $portfolioDTO->name = $data['name'];
            $portfolioDTO->link = $data['link'];
            $portfolioDTO->fromdate = $data['fromdate'];
            $portfolioDTO->description = $data['description'];

            // Walidacja DTO
            $errors = $this->validator->validate($portfolioDTO);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                $this->logger->warning('Validation errors: ' . implode(', ', $errorMessages));
                return $this->createJsonResponseWithData(['errors' => $errorMessages], 400);
            }

            // Jeśli brak błędów, wykonujemy operację
            $portfolio = (isset($data['id']) && $data['id'] > 0) ? $this->portfolioService->findById($data['id']) : new Portfolio();

            if (!$portfolio) {
                $this->logger->error('portfolio not found for id: ' . $data['id']);
                throw new NotFoundHttpException('Portfolio not found');
            }

            $portfolio->setName($portfolioDTO->name);
            $portfolio->setLink($portfolioDTO->link);
            $portfolio->setFromdate(new DateTime($portfolioDTO->fromdate));
            $portfolio->setDescription($portfolioDTO->description);

            $this->portfolioService->saveportfolio($portfolio);

            return $this->createJsonResponseWithData(['message' => 'Portfolio saved successfully'], 201);
        } catch (\Exception $e) {
            $this->logger->error('Error editing portfolio: ' . $e->getMessage());
            return $this->createJsonResponseWithData(['message' => 'Error editing portfolio'], 500);
        }
    }

    #[Route('/api/cms-delete-portfolio/{id}', name: 'api_cms_delete_portfolio', methods: ['DELETE'])]
    public function deleteCmsPortfolio(Request $request, int $id): JsonResponse
    {
        try {
            // Pobieramy token z nagłówka
            $token = $request->headers->get('Authorization');

            // Weryfikujemy token
            if (!$this->tokenService->validateToken($token)) {
                return $this->createJsonResponseWithData(['message' => 'Unauthorized'], 401);
            }

            // Znajdź portfolio po ID
            $portfolio = $this->portfolioService->findById($id);

            if (!$portfolio) {
                $this->logger->error('Portfolio not found for id: ' . $id);
                return $this->createJsonResponseWithData(['message' => 'Portfolio not found'], 404);
            }

            // Usuwamy portfolio
            $this->portfolioService->deletePortfolio($portfolio);

            return $this->createJsonResponseWithData(['message' => 'Portfolio deleted successfully'], 200);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting portfolio: ' . $e->getMessage());
            return $this->createJsonResponseWithData(['message' => 'Error deleting portfolio'.$e->getMessage()], 500);
        }
    }

    #[Route('/api/read-portfolio/{id}', name: 'api_cms_read_portfolio', methods: ['GET'])]
    public function getCmsReadPortfolio(int $id): JsonResponse
    {
        try {
            $portfolio = $this->portfolioService->findById($id);

            if (!$portfolio) {
                $this->logger->error('Portfolio not found for id: ' . $id);
                return $this->createJsonResponseWithData(['message' => 'Portfolio not found'], 404);
            }

            $data = [
                'id'          => $portfolio->getId(),
                'name'        => $portfolio->getName(),
                'description' => $portfolio->getDescription(),
                'link'        => $portfolio->getLink(),
                'fromdate'    => $portfolio->getFromdate()?->format('Y-m-d'),
            ];

            return $this->createJsonResponseWithData($data);
        } catch (\Exception $e) {
            $this->logger->error('Error reading portfolio: ' . $e->getMessage());
            return $this->createJsonResponseWithData(['message' => 'Error reading portfolio'], 500);
        }
    }
}
