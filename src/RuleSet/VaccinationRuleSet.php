<?php

declare(strict_types=1);

namespace Grambas\RuleSet;

use Grambas\Model\CertificateInterface;
use Grambas\Model\Vaccination;

class VaccinationRuleSet implements CertificateRuleSetInterface
{
    public static function getWaitInterval(): ?string
    {
        return 'P15D';
    }

    public static function supports(CertificateInterface $cert): bool
    {
        return $cert instanceof Vaccination;
    }

    public static function getValidationInterval(): ?string
    {
        return 'P18M';
    }
}
