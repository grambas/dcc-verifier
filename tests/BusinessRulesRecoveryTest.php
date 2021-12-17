<?php

declare(strict_types=1);

namespace Grambas\Test;

use DateTime;
use Grambas\DateValidator;
use Grambas\Model\CertificateInterface;
use Grambas\Model\DCC;
use Grambas\Model\Recovery;
use PHPUnit\Framework\TestCase;

class BusinessRulesRecoveryTest extends TestCase
{
    use DccAssertionHelperTrait;

    public function recoveryDatesDataProvider(): iterable
    {
        yield 'valid' => ['2000-01-01 00:00:00', '2000-06-01 00:00:00', '2000-05-15 15:00:00', true];
        yield 'valid, same es valid from' => ['2000-01-01 00:00:00', '2000-06-01 00:00:00', '2000-01-01 00:00:00', true];
        yield 'valid, same es valid until' => ['2000-01-01 00:00:00', '2000-06-01 00:00:00', '2000-06-01 00:00:00', true];
        yield 'expired ' => ['2000-01-01 00:00:00', '2000-06-01 00:00:00', '2000-07-01 15:00:00', false];
        yield 'not yet started ' => ['2000-01-01 00:00:00', '2000-06-01 00:00:00', '1999-12-15 15:00:00', false];
    }

    /**
     * @dataProvider recoveryDatesDataProvider
     */
    public function test_recovery_date_validity(
        string $validFrom,
        string $validTo,
        string $checkDate,
        bool   $expected
    ): void {
        $recovery = static::createMock(Recovery::class);
        $recovery->method('getValidFrom')->willReturn(
            DateTime::createFromFormat('Y-m-d H:i:s', $validFrom)
        );
        $recovery->method('getValidTo')->willReturn(
            DateTime::createFromFormat('Y-m-d H:i:s', $validTo)
        );

        $dcc = static::createMock(DCC::class);
        $dcc->method('getCurrentCertificate')->willReturn($recovery);

        $validator = new DateValidator($dcc);

        $cert = $dcc->getCurrentCertificate();
        static::assertInstanceOf(Recovery::class, $cert);

        $result = $validator->isValidForDate(DateTime::createFromFormat('Y-m-d H:i:s', $checkDate));

        static::assertSame($expected, $result);
    }

    public function test_recovery_valid(): void
    {
        $dcc = $this->decode('/PL/1.3.0/2DCode/raw/10.json');

        $dcc->recovery->validFrom = new DateTime('today');
        $dcc->recovery->validTo = (new DateTime('now'))->add(new \DateInterval('P1D'));

        static::assertTrue($dcc->isValidFor(CertificateInterface::RECOVERY));
        static::assertTrue($dcc->isValidFor(CertificateInterface::VACCINATION | CertificateInterface::RECOVERY));
        static::assertFalse($dcc->isValidFor(CertificateInterface::VACCINATION));
    }

    public function test_recovery_last_expiration_day(): void
    {
        $dcc = $this->decode('/PL/1.3.0/2DCode/raw/10.json');

        $dcc->recovery->validFrom = (new DateTime('now'))->modify('-2 hours');
        $dcc->recovery->validTo = (new DateTime('now'))->modify('+2 hours');

        static::assertTrue($dcc->isValidFor(CertificateInterface::RECOVERY));
        static::assertTrue($dcc->isValidFor(CertificateInterface::VACCINATION | CertificateInterface::RECOVERY));
        static::assertFalse($dcc->isValidFor(CertificateInterface::VACCINATION));
    }
}
