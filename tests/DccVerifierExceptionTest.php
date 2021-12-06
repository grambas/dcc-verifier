<?php

declare(strict_types=1);

namespace Grambas\Test;

use Grambas\DccVerifier;
use Grambas\Exception\DccVerifierException;
use Grambas\Model\DCC;
use PHPUnit\Framework\TestCase;

class DccVerifierExceptionTest extends TestCase
{
    use DccAssertionHelperTrait;

    public function test_base45_decode_error(): void
    {
        $this->expectException(DccVerifierException::class);
        $this->expectExceptionMessage('QRCode could not be decoded with base45');

        $verifier = new DccVerifier('test');
        $verifier->decode();
    }

    public function test_zlib_decompress_error(): void
    {
        $base45Encoded = '7WE QE'; // => test
        $this->expectException(DccVerifierException::class);
        $this->expectExceptionMessage('QRCode could not be decompressed with zlib');

        $verifier = new DccVerifier($base45Encoded);
        $verifier->decode();
    }

    public function test_zlib_compression_broken(): void
    {
        $this->expectException(DccVerifierException::class);
        $this->getDecodedDccAndExpected('/common/2DCode/raw/Z1.json');
    }

    public function test_zlib_not_compressed(): void
    {
        $this->expectException(DccVerifierException::class);
        $this->getDecodedDccAndExpected('/common/2DCode/raw/Z2.json');
    }

    public function test_decode_exception(): void
    {
        $this->expectException(DccVerifierException::class);
        $this->getDecodedDccAndExpected('/common/2DCode/raw/B1.json');
    }

    public function test_invalid_cbor_payload(): void
    {
        $this->expectException(DccVerifierException::class);
        $this->getDecodedDccAndExpected('/common/2DCode/raw/CBO1.json');
    }

    public function test_invalid_cbor_payload2(): void
    {
        $this->expectException(DccVerifierException::class);
        $this->getDecodedDccAndExpected('/common/2DCode/raw/CBO2.json');
    }

    public function test_no_valid_certificate_subject(): void
    {
        // X value is invalid
        $json = '{"X":[{"ci":"urn:uvci:01:HR:MZ0000000314","co":"HR","df":"2020-12-15","du":"2021-06-13","fr":"2020-12-01","is":"Ministry of Health","tg":"840539006"}],"dob":"2000-01-01","nam":{"fn":"FERNÁNDEZ","gn":"RAMÓN","fnt":"FERNANDEZ","gnt":"RAMON"},"ver":"1.0.0"}';
        $data[4] = 1638555991;
        $data[6] = 1638555991;
        $data[-260][1] = json_decode($json, true);

        static::expectException(DccVerifierException::class);
        static::expectExceptionMessage('No subject parsed');

        new DCC($data);
    }

    public function test_verify_without_repository(): void
    {
        $this->expectException(DccVerifierException::class);
        $this->expectExceptionMessage('DCC can not be verified without TrustListRepository.');

        (new DccVerifier(''))->verify();
    }
}
