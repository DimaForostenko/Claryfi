<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * DTO for shipping calculation response
 */
class CalculateShippingResponse
{
    private string $carrier;
    private float $weightKg;
    private string $currency;
    private float $price;

    public function __construct(
        string $carrier,
        float $weightKg,
        float $price,
        string $currency = 'EUR'
    ) {
        $this->carrier = $carrier;
        $this->weightKg = $weightKg;
        $this->price = $price;
        $this->currency = $currency;
    }

    public function getCarrier(): string
    {
        return $this->carrier;
    }

    public function getWeightKg(): float
    {
        return $this->weightKg;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Convert to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'carrier' => $this->carrier,
            'weightKg' => $this->weightKg,
            'currency' => $this->currency,
            'price' => $this->price,
        ];
    }
}