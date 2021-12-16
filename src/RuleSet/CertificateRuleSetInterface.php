<?php

declare(strict_types=1);

namespace Grambas\RuleSet;

use Grambas\Model\CertificateInterface;

interface CertificateRuleSetInterface
{
    public static function getWaitInterval(): ?string;
    public static function getValidationInterval(): ?string;
    public static function supports(CertificateInterface $cert): bool;
}
