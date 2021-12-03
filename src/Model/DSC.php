<?php

declare(strict_types=1);

namespace Grambas\Model;

/**
 * minimal data transfer object for digital signer certificate
 */
class DSC
{
    /** @var string */
    private $rawData;

    /** @var string */
    private $kid;

    public function __construct(string $rawData, string $kid)
    {
        $this->rawData = $rawData;
        $this->kid = $kid;
    }

    public function getRawData(): string
    {
        return $this->rawData;
    }

    public function getKid(): string
    {
        return $this->kid;
    }
}
