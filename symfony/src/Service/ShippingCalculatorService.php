<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CalculateShippingRequest;
use App\DTO\CalculateShippingResponse;
use App\Exception\UnsupportedCarrierException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Service for calculating shipping costs
 * 
 * Uses Strategy Pattern via CarrierRegistry
 */
class ShippingCalculatorService
{
    private CarrierRegistry $carrierRegistry;
    private ValidatorInterface $validator;

    public function __construct(
        CarrierRegistry $carrierRegistry,
        ValidatorInterface $validator
    ) {
        $this->carrierRegistry = $carrierRegistry;
        $this->validator = $validator;
    }

    /**
     * Calculate shipping price
     * 
     * @param CalculateShippingRequest $request
     * @return CalculateShippingResponse
     * @throws ValidationFailedException if validation fails
     * @throws UnsupportedCarrierException if carrier not supported
     * @throws \InvalidArgumentException if weight is invalid
     */
    public function calculate(CalculateShippingRequest $request): CalculateShippingResponse
    {
        // Validate request DTO
        $violations = $this->validator->validate($request);
        
        if (count($violations) > 0) {
            throw new ValidationFailedException($request, $violations);
        }

        // Get carrier strategy from registry
        $carrier = $this->carrierRegistry->get($request->getCarrier());

        // Calculate price using carrier strategy
        $price = $carrier->calculatePrice($request->getWeightKg());

        // Create and return response DTO
        return new CalculateShippingResponse(
            carrier: $request->getCarrier(),
            weightKg: $request->getWeightKg(),
            price: $price,
            currency: 'EUR'
        );
    }

    /**
     * Get all available carriers
     * 
     * @return array<array{slug: string, name: string}>
     */
    public function getAvailableCarriers(): array
    {
        return $this->carrierRegistry->getCarriersInfo();
    }
}