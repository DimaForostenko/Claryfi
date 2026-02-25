<?php

declare(strict_types=1);

namespace App\Domain\Carrier;

use App\Domain\Interface\ShippingCarrierInterface;

/**
 * PackGroup shipping carrier implementation
 * 
 * Pricing logic:
 * - 1 EUR per kilogram
 */
class PackGroupCarrier implements ShippingCarrierInterface
{
    private const SLUG = 'packgroup';
    private const NAME = 'PackGroup';
    
    private const PRICE_PER_KG = 1.0;

    /**
     * {@inheritDoc}
     */
    public function calculatePrice(float $weightKg): float
    {
        if ($weightKg <= 0) {
            throw new \InvalidArgumentException('Weight must be greater than 0');
        }

        return $weightKg * self::PRICE_PER_KG;
    }

    /**
     * {@inheritDoc}
     */
    public function getSlug(): string
    {
        return self::SLUG;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }
}
