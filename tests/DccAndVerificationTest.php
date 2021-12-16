<?php

declare(strict_types=1);

namespace Grambas\Test;

use PHPUnit\Framework\TestCase;

class DccAndVerificationTest extends TestCase
{
    use DccAssertionHelperTrait;

    /**
     * @dataProvider validVaccines
     */
    public function test_valid_vaccines($file): void
    {
        [$data , $dgc] = $this->getDecodedDccAndExpected($file);

        static::assertDcc($data['JSON'], $dgc);

        static::assertVerification($data);
    }

    /**
     * @dataProvider validRecoveries
     */
    public function test_valid_recovery($file): void
    {
        [$data , $dgc] = $this->getDecodedDccAndExpected($file);

        static::assertDcc($data['JSON'], $dgc);

        static::assertVerification($data);
    }

    /**
     * @dataProvider validTests
     */
    public function test_valid_test($file): void
    {
        [$data , $dgc] = $this->getDecodedDccAndExpected($file);

        static::assertDcc($data['JSON'], $dgc);

        static::assertVerification($data);
    }

    public function validVaccines(): iterable
    {
        yield '/AT/2DCode/raw/1.json' => ['/AT/2DCode/raw/1.json'];
        yield '/BE/2DCode/raw/1.json' => ['/BE/2DCode/raw/1.json'];
        yield '/BG/2DCode/raw/3.json' => ['/BG/2DCode/raw/3.json'];
        yield '/CH/2DCode/raw/1.json' => ['/CH/2DCode/raw/1.json'];
        yield '/CY/2DCode/raw/5.json' => ['/CY/2DCode/raw/5.json']; //1/1
        yield '/CY/2DCode/raw/6.json' => ['/CY/2DCode/raw/6.json']; // 2/2
        yield '/DE/2DCode/raw/1.json' => ['/DE/2DCode/raw/1.json'];
        yield '/DK/2DCode/raw/1.json' => ['/DK/2DCode/raw/1.json'];
        yield '/FI/2DCode/raw/1.json' => ['/FI/2DCode/raw/1.json']; // 1/1
        yield '/FI/2DCode/raw/6.json' => ['/FI/2DCode/raw/6.json']; // 1/1
        yield '/FI/2DCode/raw/2.json' => ['/FI/2DCode/raw/2.json']; // 2/2
        yield '/FI/2DCode/raw/7.json' => ['/FI/2DCode/raw/7.json']; // 2/2
        yield '/GR/2DCode/raw/1.json' => ['/GR/2DCode/raw/1.json']; // 2/2
        yield '/HR/2DCode/raw/3.json' => ['/HR/2DCode/raw/3.json'];
        yield '/HU/2DCode/raw/1.json' => ['/HU/2DCode/raw/1.json'];
        yield '/IE/2DCode/raw/1.json' => ['/IE/2DCode/Raw/1.json'];
        yield '/IS/2DCode/raw/1.json' => ['/IS/2DCode/raw/1.json'];
        yield '/IS/2DCode/raw/5.json' => ['/IS/2DCode/raw/5.json'];
        yield '/IT/2DCode/raw/1.json' => ['/IT/2DCode/raw/1.json'];
        yield '/LI/2DCode/raw/3.json' => ['/LI/2DCode/raw/3.json'];
        yield '/LT/2DCode/raw/1.json' => ['/LT/2DCode/raw/1.json'];
        yield '/LU/2DCode/raw/INCERT_R_DCC_Vaccination.json' => ['/LU/2DCode/raw/INCERT_R_DCC_Vaccination.json'];
        yield '/LV/2DCode/raw/1.json' => ['/LV/2DCode/raw/1.json'];
        yield '/PT/1.0.0/2DCode/raw/1.json' => ['/PT/1.0.0/2DCode/raw/1.json']; // 1/2
        yield '/PT/1.0.0/2DCode/raw/2.json' => ['/PT/1.0.0/2DCode/raw/2.json']; // 2/2
        yield '/PT/1.3.0/2DCode/raw/1.json' => ['/PT/1.3.0/2DCode/raw/1.json']; // 1/2
        yield '/PT/1.3.0/2DCode/raw/2.json' => ['/PT/1.3.0/2DCode/raw/2.json']; // 2/2
        yield '/RO/2DCode/raw/1.json' => ['/RO/2DCode/raw/1.json'];
        yield '/RO/2DCode/raw/2.json' => ['/RO/2DCode/raw/2.json'];
        yield '/SE/2DCode/raw/1.json' => ['/SE/2DCode/raw/1.json'];
        yield '/SG/2DCode/raw/1.json' => ['/SG/2DCode/raw/1.json']; // https://github.com/eu-digital-green-certificates/dgc-testdata/pull/408
        yield '/SI/2DCode/raw/VAC.json' => ['/SI/2DCode/raw/VAC.json'];
        yield '/SK/2DCode/raw/1.json' => ['/SK/2DCode/raw/1.json'];
        yield '/SK/2DCode/raw/2.json' => ['/SK/2DCode/raw/2.json']; // DGC with vaccination entry Comirnaty (1 dose)
        yield '/SK/2DCode/raw/3.json' => ['/SK/2DCode/raw/3.json']; // DGC with vaccination entry Astra (2 doses)
        yield '/SK/2DCode/raw/4.json' => ['/SK/2DCode/raw/4.json']; // DGC with vaccination entry Moderna (1 dose)
        yield '/SK/2DCode/raw/5.json' => ['/SK/2DCode/raw/5.json']; // DGC with vaccination entry Janssen (1 dose)
        yield '/UA/2DCode/raw/1.json' => ['/UA/2DCode/raw/1.json']; // first dose
        yield '/UA/2DCode/raw/2.json' => ['/UA/2DCode/raw/2.json']; // second dose
        yield '/VA/2DCode/raw/1.json' => ['/VA/2DCode/raw/1.json'];
    }

    public function validRecoveries(): iterable
    {
        yield '/AT/2DCode/raw/2.json' => ['/AT/2DCode/raw/2.json'];
        yield '/BE/2DCode/raw/2.json' => ['/BE/2DCode/raw/2.json'];
        yield '/BG/2DCode/raw/4.json' => ['/BG/2DCode/raw/4.json'];
        yield '/CH/2DCode/raw/3.json' => ['/CH/2DCode/raw/3.json'];
        yield '/CY/2DCode/raw/8.json' => ['/CY/2DCode/raw/8.json'];
        yield '/CZ/2DCode/raw/2.json' => ['/CZ/2DCode/raw/2.json'];
        yield '/CZ/2DCode/raw/12.json' => ['/CZ/2DCode/raw/12.json'];
        yield '/DE/2DCode/raw/3.json' => ['/DE/2DCode/raw/3.json'];
        yield '/DK/2DCode/raw/3.json' => ['/DK/2DCode/raw/3.json'];
        yield '/FI/2DCode/raw/5.json' => ['/FI/2DCode/raw/5.json'];
        yield '/FI/2DCode/raw/10.json' => ['/FI/2DCode/raw/10.json'];
        yield '/GR/2DCode/raw/3.json' => ['/GR/2DCode/raw/2.json'];
        yield '/HR/2DCode/raw/1.json' => ['/HR/2DCode/raw/1.json'];
        yield '/HU/2DCode/raw/4.json' => ['/HU/2DCode/raw/4.json'];
        yield '/IE/2DCode/raw/4.json' => ['/IE/2DCode/Raw/4.json'];
        yield '/IT/2DCode/raw/2.json' => ['/IT/2DCode/raw/2.json'];
        yield '/LI/2DCode/raw/1.json' => ['/LI/2DCode/raw/1.json'];
        yield '/LT/2DCode/raw/4.json' => ['/LT/2DCode/raw/4.json'];
        yield '/LU/2DCode/raw/INCERT_R_DCC_Recovery.json' => ['/LU/2DCode/raw/INCERT_R_DCC_Recovery.json'];
        yield '/LV/2DCode/raw/13.json' => ['/LV/2DCode/raw/3.json'];
        yield '/PT/1.0.0/2DCode/raw/3.json' => ['/PT/1.0.0/2DCode/raw/3.json'];
        yield '/PT/1.3.0/2DCode/raw/3.json' => ['/PT/1.3.0/2DCode/raw/3.json'];
        yield '/RO/2DCode/raw/3.json' => ['/RO/2DCode/raw/3.json'];
        yield '/SE/2DCode/raw/5.json' => ['/SE/2DCode/raw/5.json'];
        yield '/SI/2DCode/raw/REC.json' => ['/SI/2DCode/raw/REC.json'];
        yield '/SK/2DCode/raw/6.json' => ['/SK/2DCode/raw/6.json'];
        yield '/UA/2DCode/raw/3.json' => ['/UA/2DCode/raw/3.json'];
        yield '/VA/2DCode/raw/3.json' => ['/VA/2DCode/raw/3.json'];
    }

    public function validTests(): iterable
    {
        yield '/AT/2DCode/raw/3.json' => ['/AT/2DCode/raw/3.json']; //NAA
        yield '/AT/2DCode/raw/4.json' => ['/AT/2DCode/raw/4.json']; //RA
        yield '/AE/2DCode/raw/test.json' => ['/AE/2DCode/raw/test.json'];
        yield '/BE/2DCode/raw/3.json' => ['/BE/2DCode/raw/3.json'];
        yield '/BG/2DCode/raw/5.json' => ['/BG/2DCode/raw/5.json'];
        yield '/CH/2DCode/raw/2.json' => ['/CH/2DCode/raw/2.json'];
        yield '/CY/2DCode/raw/7.json' => ['/CY/2DCode/raw/7.json'];
        yield '/CZ/2DCode/raw/3.json' => ['/CZ/2DCode/raw/3.json']; // NAA
        yield '/CZ/2DCode/raw/13.json' => ['/CZ/2DCode/raw/13.json']; // NAA
        yield '/CZ/2DCode/raw/4.json' => ['/CZ/2DCode/raw/4.json']; // RA
        yield '/CZ/2DCode/raw/14.json' => ['/CZ/2DCode/raw/14.json']; // RA
        yield '/DE/2DCode/raw/2.json' => ['/DE/2DCode/raw/2.json'];
        yield '/DK/2DCode/raw/2.json' => ['/DK/2DCode/raw/2.json']; // NAA
        yield '/DK/2DCode/raw/4.json' => ['/DK/2DCode/raw/4.json']; // RA
        yield '/FI/2DCode/raw/3.json' => ['/FI/2DCode/raw/3.json']; // NAA
        yield '/FI/2DCode/raw/8.json' => ['/FI/2DCode/raw/8.json']; // NAA
        yield '/FI/2DCode/raw/4.json' => ['/FI/2DCode/raw/4.json']; // RA
        yield '/FI/2DCode/raw/9.json' => ['/FI/2DCode/raw/9.json']; // RA
        yield '/GR/2DCode/raw/4.json' => ['/GR/2DCode/raw/4.json'];
        yield '/HR/2DCode/raw/2.json' => ['/HR/2DCode/raw/2.json']; // NAA
        yield '/HR/2DCode/raw/4.json' => ['/HR/2DCode/raw/4.json']; // RA
        yield '/HU/2DCode/raw/2.json' => ['/HU/2DCode/raw/2.json']; // NAA
        yield '/HU/2DCode/raw/3.json' => ['/HU/2DCode/raw/3.json'];
//        yield '/IE/2DCode/raw/2.json' => ['/IE/2DCode/Raw/2.json']; // RA // "tt": "COVID-19 test", ???
//        yield '/IE/2DCode/raw/3.json' => ['/IE/2DCode/Raw/3.json']; // "tt": "COVID-19 test", ???
        yield '/IS/2DCode/raw/2.json' => ['/IS/2DCode/raw/2.json'];
        yield '/IS/2DCode/raw/3.json' => ['/IS/2DCode/raw/3.json']; // NAA
        yield '/IT/2DCode/raw/3.json' => ['/IT/2DCode/raw/3.json']; // RA
        yield '/IT/2DCode/raw/4.json' => ['/IT/2DCode/raw/4.json']; // NAA
        yield '/LI/2DCode/raw/2.json' => ['/LI/2DCode/raw/2.json'];
        yield '/LT/2DCode/raw/3.json' => ['/LT/2DCode/raw/3.json'];
        yield '/LU/2DCode/raw/INCERT_R_DCC_NAAT.json' => ['/LU/2DCode/raw/INCERT_R_DCC_NAAT.json']; // NAAT
        yield '/LU/2DCode/raw/INCERT_R_DCC_RAT.json' => ['/LU/2DCode/raw/INCERT_R_DCC_RAT.json']; // RAAT
        yield '/LV/2DCode/raw/2.json' => ['/LV/2DCode/raw/2.json'];
        yield '/PT/1.0.0/2DCode/raw/4.json' => ['/PT/1.0.0/2DCode/raw/4.json']; // NAAT
        yield '/PT/1.0.0/2DCode/raw/5.json' => ['/PT/1.0.0/2DCode/raw/5.json']; // RAT
        yield '/PT/1.3.0/2DCode/raw/4.json' => ['/PT/1.3.0/2DCode/raw/4.json']; // NAAT
        yield '/RO/2DCode/raw/4.json' => ['/RO/2DCode/raw/4.json'];
        yield '/SE/2DCode/raw/2.json' => ['/SE/2DCode/raw/2.json']; // NAAT
        yield '/SE/2DCode/raw/3.json' => ['/SE/2DCode/raw/3.json']; // NAAT
        yield '/SE/2DCode/raw/4.json' => ['/SE/2DCode/raw/4.json']; // RAT
        yield '/SG/2DCode/raw/2.json' => ['/SG/2DCode/raw/2.json']; // NAAT // https://github.com/eu-digital-green-certificates/dgc-testdata/pull/408
        yield '/SG/2DCode/raw/3.json' => ['/SG/2DCode/raw/3.json']; // RAAT // https://github.com/eu-digital-green-certificates/dgc-testdata/pull/408
        yield '/SI/2DCode/raw/test-AG.json' => ['/SI/2DCode/raw/test-AG.json']; //RAT
        yield '/SI/2DCode/raw/test-PCR.json' => ['/SI/2DCode/raw/test-PCR.json']; // NAAT
        yield '/SK/2DCode/raw/7.json' => ['/SK/2DCode/raw/7.json']; // RAT
        yield '/SK/2DCode/raw/8.json' => ['/SK/2DCode/raw/8.json']; // NAAT
        yield '/UA/2DCode/raw/4.json' => ['/UA/2DCode/raw/4.json']; // NAAT
        yield '/UA/2DCode/raw/5.json' => ['/UA/2DCode/raw/5.json']; // RAT
        yield '/VA/2DCode/raw/2.json' => ['/VA/2DCode/raw/2.json'];
    }
}
