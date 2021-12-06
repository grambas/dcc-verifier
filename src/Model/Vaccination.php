<?php

declare(strict_types=1);

namespace Grambas\Model;

use DateTime;

/**
 * https://github.com/ehn-dcc-development/ehn-dcc-schema/blob/release/1.3.0/DCC.Types.schema.json
 */
class Vaccination implements CertificateInterface
{
    public const ID = 1;

    public const MP_PFIZER = 'EU/1/20/1528';
    public const MP_MODERNA = 'EU/1/20/1507';
    public const MP_ASTRA_ZENECA = 'EU/1/21/1529';
    public const MP_JANSSEN = 'EU/1/20/1525';

    /** vaccine types */
    public const mRNA = '1119349007';
    public const ANTIGEN = '1119305005';

    /**
     * The medicinal product used for this specific dose of vaccination
     *
     * @var string
     */
    public $medicalProduct;

    /**
     * The vaccine prophylaxis as defined by SNOMED CT
     *
     * @var string
     */
    public $vaccineProphylaxis;

    /**
     * The date when the described dose was received, in the format YYYY-MM-DD.
     *
     * @var DateTime
     */
    public $receiveDate;

    /**
     * https://github.com/Digitaler-Impfnachweis/certification-apis/blob/master/Implementation.md#values-for-dn-and-sd-for-base-vaccinations-with-the-same-vaccine
     * Sequence number (positive integer) of the dose given during this vaccination event
     *
     * @var int (positive)
     */
    public $doseNumber;

    /**
     * Total number of doses (positive integer) in a complete vaccination series according to the used vaccination protocol
     *
     * @var int (positive)
     */
    public $totalNumberOfDosesRequired;

    /**
     * Marketing authorisation holder or manufacturer, if no marketing authorisation holder is present.
     *
     * @var string
     */
    public $marketingAuthorisationHolder;

    /**
     * The disease agent targeted as defined by SNOMED CT. Currently for COVID-19, only "840539006" is to be used.
     *
     * @var string
     */
    public $diseaseAgentTargeted;

    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $issuer;

    /**
     * @var string
     */
    public $uniqueIdentifier;


    /** @var DateTime */
    public $validFrom;

    /**
     * Certificate validation expiration date
     *
     * @var DateTime
     */
    public $validTo;


    public function __construct(array $v, DateTime $validFrom, DateTime $validTo)
    {
        $data = $v[0];
        $this->uniqueIdentifier = $data['ci'];
        $this->medicalProduct = $data['mp'];
        $this->vaccineProphylaxis = $data['vp'];
        $this->receiveDate = new DateTime($data['dt']);
        $this->doseNumber = (int) $data['dn'];
        $this->totalNumberOfDosesRequired = (int) $data['sd'];
        $this->marketingAuthorisationHolder = $data['ma'];
        $this->diseaseAgentTargeted = $data['tg'];
        $this->country = $data['co'];
        $this->issuer = $data['is'];
        $this->validTo = $validTo;
        $this->validFrom = $validFrom;
    }

    public function getId(): int
    {
        return CertificateInterface::VACCINATION;
    }

    /**
     * @return bool if number of dose is accepted as fully vaccinated dependently on type of vaccine
     */
    public function isFullyVaccinated(): bool
    {
        return $this->doseNumber >= $this->totalNumberOfDosesRequired;
    }

    public function isValid(): bool
    {
        return $this->isFullyVaccinated();
    }

    public function isValidForDate(DateTime $date): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        return $date >= $this->getValidFrom() && $date <= $this->getValidTo();
    }

    public function getValidFrom(): DateTime
    {
        return $this->validFrom;
    }

    public function getValidTo(): DateTime
    {
        return $this->validTo;
    }
}
