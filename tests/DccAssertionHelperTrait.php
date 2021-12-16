<?php

declare(strict_types=1);

namespace Grambas\Test;

use Grambas\DccVerifier;
use Grambas\Model\AbstractTest;
use Grambas\Model\CertificateInterface;
use Grambas\Model\DCC;
use Grambas\Model\DSC;
use Grambas\Model\Recovery;
use Grambas\Model\Vaccination;
use Grambas\Repository\GermanyTrustListRepository;

trait DccAssertionHelperTrait
{
    public static function assertDcc(array $expected, DCC $dcc): void
    {
        static::assertEquals($expected['ver'], $dcc->version);
        static::assertEquals($expected['dob'], $dcc->dateOfBirth);
        static::assertEquals($expected['nam']['gn'] ?? null, $dcc->firstName);
        static::assertEquals($expected['nam']['gnt'] ?? null, $dcc->standardisedFirstName);
        static::assertEquals($expected['nam']['fn']  ?? null, $dcc->lastName);
        static::assertEquals($expected['nam']['fnt'], $dcc->standardisedLastName);

        $current =  $dcc->getCurrentCertificate();
        switch ($current->getId()) {
            case CertificateInterface::VACCINATION:
                static::assertVaccine($expected['v']['0'], $current);
                break;
            case CertificateInterface::RECOVERY:
                static::assertRecovery($expected['r']['0'], $current);
                break;
            case CertificateInterface::RAPID_TEST || CertificateInterface::PCR_TEST:
                static::assertTest($expected['t']['0'], $current);
                break;
            default:
                static::assertTrue(false, 'type could not be parsed');
        }

        //todo date objects
    }

    public static function assertVaccine(array $v, CertificateInterface $vaccination): void
    {
        static::assertInstanceOf(Vaccination::class, $vaccination);

        static::assertEquals($v['tg'], $vaccination->diseaseAgentTargeted);
        static::assertEquals($v['vp'], $vaccination->vaccineProphylaxis);
        static::assertEquals($v['mp'], $vaccination->medicalProduct);
        static::assertEquals($v['ma'], $vaccination->marketingAuthorisationHolder);
        static::assertEquals($v['dn'], $vaccination->doseNumber);
        static::assertEquals($v['sd'], $vaccination->totalNumberOfDosesRequired);
        static::assertEquals($v['dt'], $vaccination->receiveDate->format('Y-m-d'));
        static::assertEquals($v['co'], $vaccination->country);
        static::assertEquals($v['is'], $vaccination->issuer);
        static::assertEquals($v['ci'], $vaccination->uniqueIdentifier);
    }

    public static function assertRecovery(array $r, CertificateInterface $recovery): void
    {
        static::assertInstanceOf(Recovery::class, $recovery);

        static::assertEquals($r['df'], $recovery->validFrom->format('Y-m-d'));
        static::assertEquals($r['du'], $recovery->validTo->format('Y-m-d'));
        static::assertEquals($r['tg'], $recovery->diseaseAgentTargeted);
        static::assertEquals($r['fr'], $recovery->positiveResultDate);
        static::assertEquals($r['co'], $recovery->country);
        static::assertEquals($r['is'], $recovery->issuer);
        static::assertEquals($r['ci'], $recovery->uniqueIdentifier);
    }

    public static function assertTest(array $t, CertificateInterface $test): void
    {
        static::assertInstanceOf(AbstractTest::class, $test);

        static::assertEquals($t['tg'], $test->diseaseAgentTargeted);
        static::assertEquals($t['tt'], $test->type);
        static::assertEquals(new \DateTime($t['sc']), $test->sampleCollectionDate);
        static::assertEquals($t['tr'], $test->testResult);
        static::assertEquals($t['co'], $test->country);
        static::assertEquals($t['is'], $test->issuer);
        static::assertEquals($t['ci'], $test->uniqueIdentifier);
        static::assertEquals($t['tc'], $test->testingCenter);

        if ($test->isRapidTest()) {
            static::assertEquals($t['ma'] ?? null, $test->testDeviceIdentifier);
        }

        if ($test->isNAATTest()) {
            static::assertEquals($t['nm'] ?? null, $test->name);
        }
    }

    public function decode(string $file): DCC
    {
        return (new DccVerifier($this->getJsonData($file)['PREFIX']))->decode();
    }

    public function assertVerification(array $data): void
    {
        if (isset($data['TESTCTX']['CERTIFICATE'])) {
            $repository = static::createMock(GermanyTrustListRepository::class);
            $repository->method('getByKid')->willReturn(new DSC($data['TESTCTX']['CERTIFICATE'], ''));
            $verifier = new DccVerifier($data['PREFIX'], $repository);

            static::assertTrue($verifier->verify());
        } else {
            $test = '';
        }
    }

    public function getDecodedDccAndExpected(string $file): array
    {
        $data =  $this->getJsonData($file);

        $verifier = new DccVerifier($data['PREFIX']);

        return [$data, $verifier->decode()];
    }

    private function getJsonData(string $file): array
    {
        return json_decode(
            file_get_contents(dirname(__DIR__) . '/tests/test-data/dgc-testdata' . $file),
            true
        );
    }
}
