<?php

declare(strict_types=1);

namespace App\Domain\Carrier;

use App\Domain\Interface\ShippingCarrierInterface;

/**
 * TransCompany shipping carrier implementation
 * 
 * Pricing logic:
 * - Weight <= 10kg: 20 EUR
 * - Weight > 10kg: 100 EUR
 */
class TransCompanyCarrier implements ShippingCarrierInterface
{
    private const SLUG = 'transcompany';
    private const NAME = 'TransCompany';
    
    private const WEIGHT_THRESHOLD = 10.0;
    private const PRICE_LIGHT = 20.0;
    private const PRICE_HEAVY = 100.0;

    /**
     * {@inheritDoc}
     */
    public function calculatePrice(float $weightKg): float
    {
        if ($weightKg <= 0) {
            throw new \InvalidArgumentException('Weight must be greater than 0');
        }

        if ($weightKg <= self::WEIGHT_THRESHOLD) {
            return self::PRICE_LIGHT;
        }

        return self::PRICE_HEAVY;
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
