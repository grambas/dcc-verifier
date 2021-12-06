<?php

declare(strict_types=1);

namespace Grambas\Model;

use DateTime;

/**
 * https://github.com/ehn-dcc-development/hcert-spec/blob/main/hcert_spec.md
 * https://ec.europa.eu/health/sites/default/files/ehealth/docs/covid-certificate_json_specification_en.pdf
 * https://github.com/ehn-dcc-development/ehn-dcc-schema/
 * https://dgcg.covidbevis.se/tp/
 */
class DCC
{
    /** @var DateTime */
    public $validFrom;

    /** @var DateTime */
    public $validTo;

    /**
     * Max 80 UTF-8 characters
     *
     * @var string
     */
    public $issuer;

    /**
     * a 2-letter ISO3166 code (RECOMMENDED) or a
     * reference to an international organisation responsible for the vaccination event
     * (such as UNHCR or WHO). A coded value from the value set
     * country-2-codes.json
     *
     * @var string
     */
    public $country;

    /**
     * @var ?string
     */
    public $firstName;

    /**
     * Surname(s) of the holder transliterated using the same
     * convention as the one used in the holder’s machine
     * readable travel documents (such as the rules defined in
     * ICAO Doc 9303 Part 3).
     * Exactly 1 (one) non-empty field MUST be provided, only
     * including characters A-Z and <. Maximum leng
     *
     * @var ?string
     */
    public $standardisedFirstName;


    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $standardisedLastName;

    /**
     * @var string ISO 8601 YYYY-MM-DD, YYYY-MM, YYYY or empty
     */
    public $dateOfBirth;

    /**
     * @var string
     */
    public $version;

    /** @var ?Vaccination */
    public $vaccination;

    /** @var ?AbstractTest */
    public $test;

    /** @var ?Recovery */
    public $recovery;

    /**
     * @var CertificateInterface
     */
    public $subject;

    public function __construct(array $payload)
    {
        $this->validFrom = (new DateTime())->setTimestamp((int) $payload[4]);
        $this->validTo = (new DateTime())->setTimestamp((int) $payload[6]);
        $data = $payload[-260][1];

        $this->dateOfBirth = $data['dob'];
        $this->lastName = $data['nam']['fn'];
        $this->standardisedLastName = $data['nam']['fnt'];
        $this->version = $data['ver'];

        $this->country = $payload[1] ?? null; // optional, ISO 3166-1 alpha-2 of issuer
        $this->firstName = $data['nam']['gn'] ??  null;
        $this->standardisedFirstName = $data['nam']['gnt'] ?? null;

        // v, t or r. Can only be one type and only one in array
        if (isset($data['v'])) {
            $this->vaccination = new Vaccination($data['v'], $this->validFrom, $this->validTo);
            $this->subject = $this->vaccination;
        } elseif (isset($data['t'])) {
            if (AbstractTest::TYPE_NAAT === $data['t'][0]['tt']) {
                $this->test = new PcrTest($data['t']);
                $this->subject = $this->test;
            }

            if (AbstractTest::TYPE_RAPID === $data['t'][0]['tt']) {
                $this->test = new RapidTest($data['t']);
                $this->subject = $this->test;
            }
        } elseif (isset($data['r'])) {
            $this->recovery = new Recovery($data['r']);
            $this->subject = $this->recovery;
        }

        if (null === $this->subject) {
            throw new \RuntimeException('no subject parsed');
        }
    }

    public function isValidFor(int $types): bool
    {
        return $types & $this->getCurrentCertificate()->getId() && $this->getCurrentCertificate()->isValid();
    }

    public function isValidForDate(DateTime $date, int $types = 0): bool
    {
        // certificate is not in asked types
        if (($types & $this->getCurrentCertificate()->getId()) === 0) {
            return false;
        }

        return $this->getCurrentCertificate()->isValidForDate($date);
    }

    public function getCurrentCertificate(): CertificateInterface
    {
        return $this->subject;
    }
}
