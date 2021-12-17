<?php

declare(strict_types=1);

namespace Grambas\RuleSet;

use Grambas\Model\CertificateInterface;
use Grambas\Model\Recovery;

class RecoveryRuleSet implements CertificateRuleSetInterface
{
    public static function getWaitInterval(): ?string
    {
        return null;
    }

    public static function supports(CertificateInterface $cert): bool
    {
        return $cert instanceof Recovery;
    }

    public static function getValidationInterval(): ?string
    {
        return null;
    }
}
