<?php

declare(strict_types=1);

namespace Grambas\Model;

use CBOR\TextStringObject;
use DateInterval;
use DateTime;

/**
 * https://github.com/ehn-dcc-development/ehn-dcc-schema/blob/release/1.3.0/DCC.Types.schema.json
 */
class AbstractTest
{
    public const TYPE_RAPID = 'LP217198-3';
    public const TYPE_NAAT = 'LP6464-4'; // Nucleic acid amplification with probe detection

    public const RESULT_POSITIVE = '260373001';
    public const RESULT_NEGATIVE = '260415000';

    /** @var string */
    public $type;

    /** @var string */
    public $diseaseAgentTargeted;

    /** @var string */
    public $country;

    /** @var string */
    public $uniqueIdentifier;

    /** @var string */
    public $issuer;

    /** @var string */
    public $testResult;

    /** @var DateTime */
    public $sampleCollectionDate;

    /** @var string|null */
    public $name;

    /** @var string */
    public $testingCenter;

    public function __construct(array $t)
    {
        $this->diseaseAgentTargeted = $t[0]['tg'];
        $this->type = $t[0]['tt'];
        $this->testResult =  $t[0]['tr'];
        $this->country =  $t[0]['co'];
        $this->issuer =  $t[0]['is'];
        $this->uniqueIdentifier =  $t[0]['ci'];

        $this->testingCenter =  $t[0]['tc'] ?? '';

        if ($t[0]['sc'] instanceof TextStringObject) {
            $this->sampleCollectionDate = new DateTime($t[0]['sc']->getNormalizedData());
        } else {
            $this->sampleCollectionDate =  new DateTime($t[0]['sc']);
        }

        $this->sampleCollectionDate->setTimezone(new \DateTimeZone('UTC'));
    }

    public function isRapidTest(): bool
    {
        return self::TYPE_RAPID === $this->type;
    }

    public function isNAATTest(): bool
    {
        return self::TYPE_NAAT === $this->type;
    }

    public function isNegative(): bool
    {
        return self::RESULT_NEGATIVE === $this->testResult;
    }

    public function isValid(): bool
    {
        return $this->isNegative();
    }

    public function isValidForDate(DateTime $date, string $intervalOffset = 'P1D'): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $tmp = clone $this->sampleCollectionDate;
        $maxDate = $tmp->add(new DateInterval($intervalOffset));

        return $date >= $this->sampleCollectionDate && $date <= $maxDate;
    }

    public function getValidFrom(): DateTime
    {
        return $this->sampleCollectionDate;
    }

    public function getValidTo(): DateTime
    {
        $tmp = clone $this->sampleCollectionDate;

        return $tmp->add(new DateInterval('P1D'));
    }
}
