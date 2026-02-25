<?php

declare(strict_types=1);

namespace App\Domain\Interface;

/**
 * Interface for shipping carrier implementations
 * 
 * Each carrier must implement its own pricing logic
 */
interface ShippingCarrierInterface
{
    /**
     * Calculate shipping price based on weight
     * 
     * @param float $weightKg Weight in kilograms
     * @return float Price in EUR
     * @throws \InvalidArgumentException if weight is invalid
     */
    public function calculatePrice(float $weightKg): float;

    /**
     * Get carrier unique identifier/slug
     * 
     * @return string Carrier slug (e.g., 'transcompany', 'packgroup')
     */
    public function getSlug(): string;

    /**
     * Get carrier display name
     * 
     * @return string Carrier name
     */
    public function getName(): string;
}
