<?php

declare(strict_types=1);

namespace Grambas\Test;

use _PHPStan_76800bfb5\Nette\Utils\DateTime;
use Grambas\Model\CertificateInterface;
use PHPUnit\Framework\TestCase;

class BusinessLogicRulesTest extends TestCase
{
    use DccAssertionHelperTrait;

    public function test_fully_vaccinated_with_one(): void
    {
        $dcc = $this->decode('/PL/1.3.0/2DCode/raw/13.json');

        static::assertTrue($dcc->vaccination->isValid());
        static::assertTrue($dcc->isValidFor(CertificateInterface::VACCINATION));

        static::assertFalse($dcc->isValidForDate(new \DateTime('now'), CertificateInterface::VACCINATION));

        $now = new \DateTime();
        $dcc->getCurrentCertificate()->validFrom = (clone $now)->modify('- 2 hours');
        $dcc->getCurrentCertificate()->validTo = (clone $now)->modify('+ 2 hours');
        static::assertTrue($dcc->isValidForDate((clone $now), CertificateInterface::VACCINATION));
        static::assertFalse($dcc->isValidForDate((clone $now), CertificateInterface::RAPID_TEST));
    }

    public function test_fully_vaccinated_with_two(): void
    {
        $dcc = $this->decode('/CY/2DCode/raw/6.json');

        static::assertTrue($dcc->vaccination->isFullyVaccinated());
        static::assertTrue($dcc->isValidFor(CertificateInterface::VACCINATION));
    }

    public function test_vaccine_one_dose_from_two(): void
    {
        $dcc = $this->decode('/DE/2DCode/raw/4.json');

        static::assertFalse($dcc->vaccination->isFullyVaccinated());
        static::assertFalse($dcc->vaccination->isValid());
        static::assertFalse($dcc->vaccination->isValidForDate(new \DateTime()));
        static::assertFalse($dcc->isValidFor(CertificateInterface::VACCINATION));
    }

    public function test_vaccine_expired(): void
    {
        $dcc = $this->decode('/CY/2DCode/raw/6.json');
        $dcc->getCurrentCertificate()->validTo = (new \DateTime('now'))->modify('- 2 hours');

        static::assertTrue($dcc->vaccination->isFullyVaccinated());
        static::assertFalse($dcc->isValidForDate(new \DateTime('now'), CertificateInterface::VACCINATION));
    }

    public function test_recovery_valid(): void
    {
        $dcc = $this->decode('/PL/1.3.0/2DCode/raw/10.json');

        $dcc->recovery->validFrom = new \DateTime('today');
        $dcc->recovery->validTo = (new \DateTime('now'))->add(new \DateInterval('P1D'));

        static::assertTrue($dcc->recovery->isValidForDate(new \DateTime('now')));
        static::assertTrue($dcc->isValidFor(CertificateInterface::RECOVERY));
        static::assertTrue($dcc->isValidFor(CertificateInterface::VACCINATION | CertificateInterface::RECOVERY));
        static::assertFalse($dcc->isValidFor(CertificateInterface::VACCINATION));
    }

    public function test_recovery_last_expiration_day(): void
    {
        $dcc = $this->decode('/PL/1.3.0/2DCode/raw/10.json');

        $dcc->recovery->validFrom = (new \DateTime('now'))->modify('-2 hours');
        $dcc->recovery->validTo = (new \DateTime('now'))->modify('+2 hours');

        static::assertTrue($dcc->recovery->isValidForDate(new \DateTime('now')));
        static::assertTrue($dcc->isValidFor(CertificateInterface::RECOVERY));
        static::assertTrue($dcc->isValidFor(CertificateInterface::VACCINATION | CertificateInterface::RECOVERY));
        static::assertFalse($dcc->isValidFor(CertificateInterface::VACCINATION));
    }

    public function test_recovery_expired(): void
    {
        $dcc = $this->decode('/PL/1.3.0/2DCode/raw/10.json');

        static::assertFalse($dcc->recovery->isValidForDate(new \DateTime('2010-10-10')));
    }

    public function test_naat_negative(): void
    {
        $dcc = $this->decode('/DK/2DCode/raw/2.json');

        static::assertTrue($dcc->test->isNAATTest());
        static::assertTrue($dcc->test->isNegative());
        static::assertTrue($dcc->isValidFor(CertificateInterface::PCR_TEST));
        static::assertFalse($dcc->isValidFor(CertificateInterface::RAPID_TEST));

        $dcc->test->sampleCollectionDate = (new DateTime())->modify('-30 Minutes');
        static::assertTrue($dcc->isValidForDate(new \DateTime(), CertificateInterface::PCR_TEST));
    }

    public function test_naat_positive(): void
    {
        $dcc = $this->decode('/FR/2DCode/raw/DCC_Test_0007.json');

        static::assertTrue($dcc->test->isNAATTest());
        static::assertFalse($dcc->test->isNegative());
        static::assertFalse($dcc->isValidFor(CertificateInterface::PCR_TEST));

        static::assertFalse($dcc->isValidForDate(new \DateTime(), CertificateInterface::PCR_TEST));
    }

    public function test_naat_positive2(): void
    {
        $dcc = $this->decode('/FR/2DCode/raw/DCC_Test_0008.json');

        static::assertTrue($dcc->test->isNAATTest());
        static::assertFalse($dcc->test->isNegative());
        static::assertFalse($dcc->isValidFor(CertificateInterface::PCR_TEST));
        static::assertFalse($dcc->isValidFor(CertificateInterface::PCR_TEST | CertificateInterface::VACCINATION | CertificateInterface::RECOVERY | CertificateInterface::RAPID_TEST));
    }

    public function test_rat_negative_and_valid(): void
    {
        $dcc = $this->decode('/HU/2DCode/raw/3.json');

        $dcc->test->testResult = (new \DateTime('now'))->modify('+2 hours');

        static::assertTrue($dcc->test->isRapidTest());
        static::assertFalse($dcc->test->isNegative());
        static::assertFalse($dcc->isValidFor(CertificateInterface::RAPID_TEST));
        static::assertFalse($dcc->isValidFor(CertificateInterface::PCR_TEST | CertificateInterface::VACCINATION | CertificateInterface::RECOVERY | CertificateInterface::RAPID_TEST));
    }

    public function test_rat_positive(): void
    {
        $dcc = $this->decode('/FR/2DCode/raw/DCC_Test_0012.json');

        static::assertTrue($dcc->test->isRapidTest());
        static::assertFalse($dcc->test->isNegative());
        static::assertFalse($dcc->isValidFor(CertificateInterface::RAPID_TEST));
        static::assertFalse($dcc->isValidFor(CertificateInterface::PCR_TEST | CertificateInterface::VACCINATION | CertificateInterface::RECOVERY | CertificateInterface::RAPID_TEST));
    }

    public function test_rat_positive2(): void
    {
        $dcc = $this->decode('/FR/2DCode/raw/DCC_Test_0013.json');

        static::assertTrue($dcc->test->isRapidTest());
        static::assertFalse($dcc->test->isNegative());
        static::assertFalse($dcc->isValidFor(CertificateInterface::RAPID_TEST));
        static::assertFalse($dcc->isValidFor(CertificateInterface::PCR_TEST | CertificateInterface::VACCINATION | CertificateInterface::RECOVERY | CertificateInterface::RAPID_TEST));
    }
}
