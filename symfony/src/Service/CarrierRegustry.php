<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\ShippingCarrierInterface;
use App\Exception\UnsupportedCarrierException;

/**
 * Registry for managing shipping carrier strategies
 * 
 * Implements Strategy Pattern for carrier selection
 */
class CarrierRegistry
{
    /**
     * @var array<string, ShippingCarrierInterface>
     */
    private array $carriers = [];

    /**
     * Register a carrier strategy
     * 
     * @param ShippingCarrierInterface $carrier
     * @return void
     */
    public function register(ShippingCarrierInterface $carrier): void
    {
        $this->carriers[$carrier->getSlug()] = $carrier;
    }

    /**
     * Get carrier by slug
     * 
     * @param string $slug Carrier slug
     * @return ShippingCarrierInterface
     * @throws UnsupportedCarrierException if carrier not found
     */
    public function get(string $slug): ShippingCarrierInterface
    {
        if (!$this->has($slug)) {
            throw new UnsupportedCarrierException($slug, $this->getAvailableSlugs());
        }

        return $this->carriers[$slug];
    }

    /**
     * Check if carrier exists
     * 
     * @param string $slug Carrier slug
     * @return bool
     */
    public function has(string $slug): bool
    {
        return isset($this->carriers[$slug]);
    }

    /**
     * Get all registered carriers
     * 
     * @return array<string, ShippingCarrierInterface>
     */
    public function getAll(): array
    {
        return $this->carriers;
    }

    /**
     * Get available carrier slugs
     * 
     * @return array<string>
     */
    public function getAvailableSlugs(): array
    {
        return array_keys($this->carriers);
    }

    /**
     * Get carriers info for API response
     * 
     * @return array<array{slug: string, name: string}>
     */
    public function getCarriersInfo(): array
    {
        return array_map(
            fn(ShippingCarrierInterface $carrier) => [
                'slug' => $carrier->getSlug(),
                'name' => $carrier->getName(),
            ],
            array_values($this->carriers)
        );
    }
}