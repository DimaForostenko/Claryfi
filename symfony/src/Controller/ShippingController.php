<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CalculateShippingRequest;
use App\Exception\UnsupportedCarrierException;
use App\Service\ShippingCalculatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[Route('/api/shipping', name: 'api_shipping_')]
class ShippingController extends AbstractController
{
    private ShippingCalculatorService $calculatorService;
    private SerializerInterface $serializer;

    public function __construct(
        ShippingCalculatorService $calculatorService,
        SerializerInterface $serializer
    ) {
        $this->calculatorService = $calculatorService;
        $this->serializer = $serializer;
    }

    /**
     * Calculate shipping price
     * 
     * POST /api/shipping/calculate
     * 
     * Request body:
     * {
     *   "carrier": "transcompany",
     *   "weightKg": 15.5
     * }
     * 
     * Response:
     * {
     *   "carrier": "transcompany",
     *   "weightKg": 15.5,
     *   "currency": "EUR",
     *   "price": 100.0
     * }
     */
    #[Route('/calculate', name: 'calculate', methods: ['POST'])]
    public function calculate(Request $request): JsonResponse
    {
        try {
            // Deserialize JSON request to DTO
            $requestDto = $this->serializer->deserialize(
                $request->getContent(),
                CalculateShippingRequest::class,
                'json'
            );

            // Calculate shipping price
            $responseDto = $this->calculatorService->calculate($requestDto);

            // Serialize response DTO to JSON
            return $this->json($responseDto->toArray(), Response::HTTP_OK);

        } catch (ValidationFailedException $e) {
            // Handle validation errors
            $errors = [];
            foreach ($e->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return $this->json([
                'error' => 'Validation failed',
                'details' => $errors,
            ], Response::HTTP_BAD_REQUEST);

        } catch (UnsupportedCarrierException $e) {
            // Handle unsupported carrier
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);

        } catch (\InvalidArgumentException $e) {
            // Handle invalid weight or other arguments
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            // Handle unexpected errors
            return $this->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get list of available carriers
     * 
     * GET /api/shipping/carriers
     * 
     * Response:
     * {
     *   "carriers": [
     *     {"slug": "transcompany", "name": "TransCompany"},
     *     {"slug": "packgroup", "name": "PackGroup"}
     *   ]
     * }
     */
    #[Route('/carriers', name: 'carriers', methods: ['GET'])]
    public function getCarriers(): JsonResponse
    {
        try {
            $carriers = $this->calculatorService->getAvailableCarriers();

            return $this->json([
                'carriers' => $carriers,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to fetch carriers',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Health check endpoint
     * 
     * GET /api/shipping/health
     */
    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json([
            'status' => 'ok',
            'service' => 'shipping-calculator',
            'timestamp' => time(),
        ], Response::HTTP_OK);
    }
}