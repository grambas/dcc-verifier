<?php

declare(strict_types=1);

namespace Grambas;

use DateInterval;
use DateTime;
use Grambas\Exception\RuleSetException;
use Grambas\Model\DCC;
use Grambas\RuleSet\CertificateRuleSetInterface;
use Grambas\RuleSet\PcrTestRuleSet;
use Grambas\RuleSet\RapidTestRuleSet;
use Grambas\RuleSet\RecoveryRuleSet;
use Grambas\RuleSet\VaccinationRuleSet;

class DateValidator
{
    /** @var CertificateRuleSetInterface[] */
    protected $certificateRuleSets;
    protected $dcc;

    public function __construct(DCC $dcc, iterable $certificateRuleSets = null)
    {
        $this->dcc = $dcc;

        if (null === $certificateRuleSets) {
            $this->certificateRuleSets = [
                new PcrTestRuleSet(),
                new RapidTestRuleSet(),
                new VaccinationRuleSet(),
                new RecoveryRuleSet(),
            ];
        } else {
            $this->certificateRuleSets = $certificateRuleSets;
        }
    }

    private function getCertificateRuleSet(): CertificateRuleSetInterface
    {
        foreach ($this->certificateRuleSets as $certificateRuleSet) {
            if (!$certificateRuleSet::supports($this->dcc->getCurrentCertificate())) {
                continue;
            }

            return $certificateRuleSet;
        }

        throw new RuleSetException(
            sprintf('RuleSet for %s must be defined', get_class($this->dcc->getCurrentCertificate()))
        );
    }

    public function getValidFrom(): DateTime
    {
        $validFrom = clone $this->dcc->getCurrentCertificate()->getValidFrom();
        $waitInterval = $this->getCertificateRuleSet()::getWaitInterval();
        if (null !== $waitInterval) {
            $waitInterval = new DateInterval($waitInterval);
            $validFrom->add($waitInterval);
        }

        return $validFrom;
    }

    public function getValidTo(): DateTime
    {
        $cert = $this->dcc->getCurrentCertificate();

        if (null === $cert->getValidTo()) { // vaccination, tests
            $validTo = clone $cert->getValidFrom();
        } else {
            $validTo = clone $cert->getValidTo();
        }

        $validationInterval =  $this->getCertificateRuleSet()::getValidationInterval();
        if (null !== $validationInterval) {
            $interval = new DateInterval($validationInterval);
            $validTo->add($interval);
        }

        return $validTo;
    }

    public function isValidForDate(DateTime $date): bool
    {
        return $date >= $this->getValidFrom() && $date <= $this->getValidTo();
    }
}
