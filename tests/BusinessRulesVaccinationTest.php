<?php

declare(strict_types=1);

namespace Grambas\Test;

use DateTime;
use Grambas\DateValidator;
use Grambas\Model\CertificateInterface;
use Grambas\Model\DCC;
use Grambas\Model\Vaccination;
use PHPUnit\Framework\TestCase;

class BusinessRulesVaccinationTest extends TestCase
{
    use DccAssertionHelperTrait;

    public function vaccineDatesDataProvider(): iterable
    {
        yield 'valid direct after 15 days' => ['2000-01-01 00:00:00', '2000-01-16 00:00:00', true];
        yield 'valid' => ['2000-01-01 00:00:00', '2000-02-15 00:00:00', true];
        yield 'expired' => ['2000-01-01 00:00:00', '2001-07-02 00:00:00', false];
        yield '1h as expired' => ['2000-01-01 00:00:00', '2001-07-01 01:00:00', false];
        yield 'not yet started' => ['2000-01-01 00:00:00', '1999-12-20 00:00:00', false];
    }

    /**
     * @dataProvider vaccineDatesDataProvider
     */
    public function test_vaccination_date_validity(string $validFrom, string $checkDate, bool $expected): void
    {
        $vaccination = static::createMock(Vaccination::class);
        $vaccination->method('getValidFrom')->willReturn(
            DateTime::createFromFormat('Y-m-d H:i:s', $validFrom)
        );
        $vaccination->method('getValidTo')->willReturn(null);

        $dcc = static::createMock(DCC::class);
        $dcc->method('getCurrentCertificate')->willReturn($vaccination);

        $validator = new DateValidator($dcc);

        $cert = $dcc->getCurrentCertificate();
        static::assertInstanceOf(Vaccination::class, $cert);

        $result = $validator->isValidForDate(DateTime::createFromFormat('Y-m-d H:i:s', $checkDate));

        static::assertSame($expected, $result);
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
        static::assertFalse($dcc->isValidFor(CertificateInterface::VACCINATION));
    }
}
