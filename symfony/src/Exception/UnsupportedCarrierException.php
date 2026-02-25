<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Exception thrown when requested carrier is not supported
 */
class UnsupportedCarrierException extends \RuntimeException
{
    public function __construct(string $carrier, array $availableCarriers = [])
    {
        $message = sprintf(
            'Carrier "%s" is not supported.',
            $carrier
        );

        if (!empty($availableCarriers)) {
            $message .= sprintf(
                ' Available carriers: %s',
                implode(', ', $availableCarriers)
            );
        }

        parent::__construct($message, 400);
    }
}