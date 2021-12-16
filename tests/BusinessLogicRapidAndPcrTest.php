<?php

declare(strict_types=1);

namespace Grambas\Test;

use DateTime;
use Grambas\DateValidator;
use Grambas\Model\CertificateInterface;
use Grambas\Model\DCC;
use Grambas\Model\PcrTest;
use Grambas\Model\RapidTest;
use Grambas\RuleSet\CertificateRuleSetInterface;
use PHPUnit\Framework\TestCase;

class BusinessLogicRapidAndPcrTest extends TestCase
{
    use DccAssertionHelperTrait;

    public function pcrDatesDataProvider(): iterable
    {
        yield 'valid same day' => ['2000-01-01 12:00:00', '2000-01-01 20:00:00', true];
        yield 'valid after 45h' => ['2000-01-01 12:00:00', '2000-01-03 10:00:00', true];
        yield '30min as expired' => ['2000-01-01 12:00:00', '2000-01-03 12:30:00', false];
        yield 'not yet started' => ['2000-01-01 12:00:00', '2000-01-01 11:00:00', false];
    }

    /**
     * @dataProvider pcrDatesDataProvider
     */
    public function test_pcr_date_validity(string $validFrom, string $checkDate, bool $expected): void
    {
        $pcr = static::createMock(PcrTest::class);
        $pcr->method('getValidFrom')->willReturn(
            DateTime::createFromFormat('Y-m-d H:i:s', $validFrom)
        );
        $pcr->method('getValidTo')->willReturn(null);

        $dcc = static::createMock(DCC::class);
        $dcc->method('getCurrentCertificate')->willReturn($pcr);

        $validator = new DateValidator($dcc);

        $cert = $dcc->getCurrentCertificate();
        static::assertInstanceOf(PcrTest::class, $cert);

        $result = $validator->isValidForDate(DateTime::createFromFormat('Y-m-d H:i:s', $checkDate));

        static::assertSame($expected, $result);
    }


    public function pcrDatesDataProviderForCustomRuleSet(): iterable
    {
        yield 'valid after wait interval' => ['2000-01-01 12:00:00', '2000-01-06 14:00:00', true];
        yield 'not valid before wait interval' => ['2000-01-01 12:00:00', '2000-01-01 13:00:00', true];
        yield '30min as expired' => ['2000-01-01 12:00:00', '2000-01-08 12:30:00', false];
        yield 'not yet started' => ['2000-01-01 12:00:00', '2000-01-01 11:00:00', false];
    }

    /**
     * @dataProvider pcrDatesDataProviderForCustomRuleSet
     */
    public function test_custom_rule_set_for_pcr(string $validFrom, string $checkDate, bool $expected): void
    {
        $pcr = static::createMock(PcrTest::class);
        $pcr->method('getValidFrom')->willReturn(
            DateTime::createFromFormat('Y-m-d H:i:s', $validFrom)
        );
        $pcr->method('getValidTo')->willReturn(null);

        $dcc = static::createMock(DCC::class);
        $dcc->method('getCurrentCertificate')->willReturn($pcr);

        $validator = new DateValidator($dcc, [new CustomPcrTestRuleSet()]);

        $cert = $dcc->getCurrentCertificate();
        static::assertInstanceOf(PcrTest::class, $cert);

        $result = $validator->isValidForDate(DateTime::createFromFormat('Y-m-d H:i:s', $checkDate));

        static::assertSame($expected, $result);
    }

    public function rapidDatesDataProvider(): iterable
    {
        yield 'valid same day' => ['2000-01-01 12:00:00', '2000-01-01 20:00:00', true];
        yield 'valid after 23h' => ['2000-01-01 12:00:00', '2000-01-02 11:00:00', true];
        yield '30min as expired' => ['2000-01-01 12:00:00', '2000-01-02 12:30:00', false];
        yield 'not yet started' => ['2000-01-01 12:00:00', '2000-01-01 11:00:00', false];
    }

    /**
     * @dataProvider rapidDatesDataProvider
     */
    public function test_rapid_date_validity(string $validFrom, string $checkDate, bool $expected): void
    {
        $rapid = static::createMock(RapidTest::class);
        $rapid->method('getValidFrom')->willReturn(
            DateTime::createFromFormat('Y-m-d H:i:s', $validFrom)
        );
        $rapid->method('getValidTo')->willReturn(null);

        $dcc = static::createMock(DCC::class);
        $dcc->method('getCurrentCertificate')->willReturn($rapid);

        $validator = new DateValidator($dcc);

        $cert = $dcc->getCurrentCertificate();
        static::assertInstanceOf(RapidTest::class, $cert);

        $result = $validator->isValidForDate(DateTime::createFromFormat('Y-m-d H:i:s', $checkDate));

        static::assertSame($expected, $result);
    }

    public function test_naat_negative(): void
    {
        $dcc = $this->decode('/DK/2DCode/raw/2.json');

        static::assertTrue($dcc->test->isNAATTest());
        static::assertTrue($dcc->test->isNegative());
        static::assertTrue($dcc->isValidFor(CertificateInterface::PCR_TEST));
        static::assertFalse($dcc->isValidFor(CertificateInterface::RAPID_TEST));
    }

    public function test_naat_positive(): void
    {
        $dcc = $this->decode('/FR/2DCode/raw/DCC_Test_0007.json');

        static::assertTrue($dcc->test->isNAATTest());
        static::assertFalse($dcc->test->isNegative());
        static::assertFalse($dcc->isValidFor(CertificateInterface::PCR_TEST));
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

        $dcc->test->testResult = (new DateTime('now'))->modify('+2 hours');

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

class CustomPcrTestRuleSet implements CertificateRuleSetInterface
{
    public static function getWaitInterval(): ?string
    {
        return 'PT1H';
    }

    public static function getValidationInterval(): ?string
    {
        return 'P7D';
    }

    public static function supports(CertificateInterface $cert): bool
    {
        return $cert instanceof PcrTest;
    }
}
