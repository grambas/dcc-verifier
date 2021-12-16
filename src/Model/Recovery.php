<?php

declare(strict_types=1);

namespace Grambas\Model;

use DateTime;

/**
 * https://github.com/ehn-dcc-development/ehn-dcc-schema/blob/release/1.3.0/DCC.Types.schema.json
 */
class Recovery implements CertificateInterface
{
    public const ID = 2;

    /**
     * @see DSC::DISEASE_AGENT_TARGET_COVID_19
     *
     * @var string
     */
    public $diseaseAgentTargeted;
    /**
     * ISO 8601 complete date of first positive NAA test result
     * @var string
     */
    public $positiveResultDate;

    /** @var DateTime */
    public $validFrom;

    /** @var DateTime */
    public $validTo;

    /** @var string */
    public $country;

    /** @var string */
    public $issuer;

    /** @var string */
    public $uniqueIdentifier;

    public function __construct(array $r)
    {
        $this->validFrom = new DateTime($r[0]['df']);
        $this->validTo = new DateTime($r[0]['du']);

        // optional for validation
        $this->diseaseAgentTargeted = $r[0]['tg'] ?? '';
        $this->positiveResultDate = $r[0]['fr'] ?? '';
        $this->country = $r[0]['co'] ?? '';
        $this->issuer = $r[0]['is'] ?? '';

        $this->uniqueIdentifier = $r[0]['ci'] ?? '';
    }

    public function getId(): int
    {
        return CertificateInterface::RECOVERY;
    }

    public function isValid(): bool
    {
        return true;
    }

    public function getValidFrom(): DateTime
    {
        return $this->validFrom;
    }

    public function getValidTo(): ?DateTime
    {
        return $this->validTo;
    }
}
