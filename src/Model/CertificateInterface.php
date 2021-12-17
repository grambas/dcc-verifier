<?php

declare(strict_types=1);

namespace Grambas\Model;

use DateTime;

interface CertificateInterface
{
    /**
     * binary certificate type values for checking validation
     */
    public const VACCINATION = 1;
    public const RECOVERY = 2;
    public const PCR_TEST = 4;
    public const RAPID_TEST = 8;

    public function getId(): int;
    public function isValid(): bool;
    public function getValidFrom(): DateTime;
    public function getValidTo(): ?DateTime;
}
