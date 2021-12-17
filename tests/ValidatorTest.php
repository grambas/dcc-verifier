<?php

declare(strict_types=1);

namespace Grambas\Test;

use DateTime;
use Grambas\Exception\RuleSetException;
use Grambas\Model\DCC;
use Grambas\Model\Recovery;
use Grambas\RuleSet\PcrTestRuleSet;
use Grambas\RuleSet\RapidTestRuleSet;
use Grambas\RuleSet\VaccinationRuleSet;
use Grambas\DateValidator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function test_rule_set_not_found(): void
    {
        $ruleSets = [
            new PcrTestRuleSet(),
            new RapidTestRuleSet(),
            new VaccinationRuleSet(),
            // new RecoveryRuleSet(), missing
        ];

        $dcc = static::createMock(DCC::class);
        $dcc->method('getCurrentCertificate')->willReturn(
            static::createMock(Recovery::class)
        );

        $validator = new DateValidator($dcc, $ruleSets);

        $this->expectException(RuleSetException::class);

        $validator->isValidForDate(new DateTime());
    }
}
