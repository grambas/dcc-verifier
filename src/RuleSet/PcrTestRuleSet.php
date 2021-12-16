<?php

declare(strict_types=1);

namespace Grambas\RuleSet;

use Grambas\Model\CertificateInterface;
use Grambas\Model\PcrTest;

class PcrTestRuleSet implements CertificateRuleSetInterface
{
    public static function getWaitInterval(): ?string
    {
        return null;
    }

    public static function supports(CertificateInterface $cert): bool
    {
        return $cert instanceof PcrTest;
    }

    public static function getValidationInterval(): ?string
    {
        return 'PT48H';
    }
}
