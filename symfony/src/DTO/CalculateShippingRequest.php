<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for shipping calculation request
 */
class CalculateShippingRequest
{
    /**
     * Carrier slug identifier
     */
    #[Assert\NotBlank(message: 'Carrier is required')]
    #[Assert\Type(type: 'string', message: 'Carrier must be a string')]
    private string $carrier;

    /**
     * Weight in kilograms
     */
    #[Assert\NotBlank(message: 'Weight is required')]
    #[Assert\Type(type: 'float', message: 'Weight must be a number')]
    #[Assert\Positive(message: 'Weight must be greater than 0')]
    private float $weightKg;

    public function __construct(string $carrier = '', float $weightKg = 0.0)
    {
        $this->carrier = $carrier;
        $this->weightKg = $weightKg;
    }

    public function getCarrier(): string
    {
        return $this->carrier;
    }

    public function setCarrier(string $carrier): self
    {
        $this->carrier = $carrier;
        return $this;
    }

    public function getWeightKg(): float
    {
        return $this->weightKg;
    }

    public function setWeightKg(float $weightKg): self
    {
        $this->weightKg = $weightKg;
        return $this;
    }
}