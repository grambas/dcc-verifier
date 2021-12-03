<?php

declare(strict_types=1);

namespace Grambas;

use CBOR\ByteStringObject;
use CBOR\Decoder;
use CBOR\ListObject;
use CBOR\MapObject;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\StringStream;
use CBOR\Tag\TagObjectManager;
use CBOR\TextStringObject;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\RSA\PS256;
use Cose\Key\Ec2Key;
use Cose\Key\Key;
use Cose\Key\RsaKey;
use Exception;
use Grambas\Cbor\CoseSign1Tag;
use Grambas\Exception\CBORDecodeException;
use Grambas\Exception\QRCodeDecodeException;
use Grambas\Model\DCC;
use Grambas\Repository\TrustListRepositoryInterface;
use InvalidArgumentException;
use Mhauri\Base45;
use function Safe\zlib_decode;
use function Safe\openssl_pkey_get_public;
use function Safe\openssl_x509_read;

class DccVerifier
{
    # Norwegian COVID-19 certificates seem to be based on the European Health Certificate but just with an 'NO1:' prefix.
    # https://harrisonsand.com/posts/covid-certificates/
    public const PREFIX_TO_REMOVE = [
        'HC1:',
        'NO1:',
    ] ;

    // https://github.com/ehn-dcc-development/hcert-spec/blob/main/hcert_spec.md#332-signature-algorithm
    public const SUPPORTED_ALGO = [
        ES256::ID,
        PS256::ID,
    ];

    /** @var Decoder */
    private $cborDecoder;

    /** @var ListObject */
    private $cose;

    /** @var string */
    private $raw;


    /** @var TrustListRepositoryInterface|null */
    private $trustListRepository;

    public function __construct(string $raw, ?TrustListRepositoryInterface $trustListRepository = null)
    {
        $this->trustListRepository = $trustListRepository;

        $tagObjectManager = new TagObjectManager();
        $tagObjectManager->add(CoseSign1Tag::class);
        $this->cborDecoder = new Decoder($tagObjectManager, new OtherObjectManager());

        $this->raw = $raw;
    }

    /**
     * @throws CBORDecodeException
     */
    public function getDCC(): DCC
    {
        $payloadInBytes = self::getQRCodePayload();

        if (!$payloadInBytes instanceof ByteStringObject) {
            throw new InvalidArgumentException('Not a valid certificate. The payload is not a byte string.');
        }

        $stream = new StringStream($payloadInBytes->getValue());
        $cbor = $this->cborDecoder->decode($stream);

        try {
            return new DCC($cbor->getNormalizedData());
        } catch (\Exception $exception) {
            throw new CBORDecodeException('invalid payload');
        }
    }

    public function verify(): bool
    {
        if (null === $this->trustListRepository) {
            throw new \RuntimeException('DCC can not be verified without TrustListRepository.');
        }

        if (null === $this->cose) {
            $this->decode();
        }

        $kid = $this->getKid();
        $dsc = $this->trustListRepository->getByKid($kid);

        if (substr($dsc->getRawData(), 0, 27) !== "-----BEGIN CERTIFICATE-----") {
            $certData = "-----BEGIN CERTIFICATE-----\n" . $dsc->getRawData() . "\n-----END CERTIFICATE-----";
        } else {
            $certData = $dsc->getRawData();
        }

        $cert = openssl_x509_read($certData);
        $publicKey = openssl_pkey_get_public($cert);
        $publicKeyData = openssl_pkey_get_details($publicKey);

        if (false === $publicKeyData) {
            throw new \RuntimeException('invalid public key');
        }

        $algoId = $this->getAlgorithm();
        if (ES256::ID === $algoId) {
            $encryptAlgo = new ES256();
            $key = Key::createFromData([ // ECDSA
                Key::TYPE => Key::TYPE_EC2,
                Key::KID => $dsc->getKid(),
                Ec2Key::DATA_CURVE => Ec2Key::CURVE_P256,
                Ec2Key::DATA_X => $publicKeyData['ec']['x'],
                Ec2Key::DATA_Y => $publicKeyData['ec']['y'],
            ]);
        } elseif (PS256::ID === $algoId) {
            // RSASSA-PSS
            $encryptAlgo = new PS256();
            $key = Key::createFromData([
                Key::TYPE => Key::TYPE_RSA,
                Key::KID => $dsc->getKid(),
                RsaKey::DATA_E => $publicKeyData['rsa']['e'],
                RsaKey::DATA_N => $publicKeyData['rsa']['n'],
            ]);
        } else {
            throw new \RuntimeException('Bad encryption algorithm');
        }

        return $encryptAlgo->verify(
            $this->getDataToVerify(),
            $key,
            $this->getSignature()->getNormalizedData()
        );
    }

    /**
     * key id is made from
     */
    public function getKid(): string
    {
        $unprotectedHeader = self::getUnprotectedHeader();
        $protectedHeader = self::getProtectedHeader();
        $headerStream = new StringStream($protectedHeader->getValue());

        $protectedHeader = $this->cborDecoder->decode($headerStream);

        // The index 4 refers to the 'kid' (key ID) parameter (see https://www.iana.org/assignments/cose/cose.xhtml)
        return base64_encode(($unprotectedHeader->getNormalizedData() + $protectedHeader->getNormalizedData())[4]);
    }

    /**
     *  Build string which represents data which need to be verified.
     */
    public function getDataToVerify(): string
    {
        $structure = new ListObject();
        $structure->add(new TextStringObject('Signature1'));
        $structure->add($this->getProtectedHeader());
        $structure->add(new ByteStringObject(''));
        $structure->add($this->getQRCodePayload());

        return (string) $structure;
    }

    /**
     * @throws CBORDecodeException|QRCodeDecodeException
     */
    public function decode(): DCC
    {
        $decoded = $this->decode45();
        $decompressed = $this->decompressQrCode($decoded);
        $cborStream = new StringStream($decompressed);

        $cbor = $this->cborDecoder->decode($cborStream);

        if (!$cbor instanceof CoseSign1Tag) {
            throw new CBORDecodeException('Not a valid certificate. Not a CoseSign1 type.');
        }

        $this->cose = $cbor->getValue();
        if (!$this->cose instanceof ListObject) {
            throw new CBORDecodeException('Not a valid certificate. No list.');
        }

        if (4 !== $this->cose->count()) {
            throw new CBORDecodeException('Not a valid certificate. The list size is not correct.');
        }

        return $this->getDCC();
    }

    private function getAlgorithm(): int
    {
        $firstItem = $this->getProtectedHeader();
        $stream = new StringStream($firstItem->getValue());
        $data = $this->cborDecoder->decode($stream)->getNormalizedData();

        if (!in_array($data[1], self::SUPPORTED_ALGO)) {
            throw new \RuntimeException('Certificate algorithm with identifier ' . $data[1] . ' not supported');
        }

        return (int) $data[1];
    }

    /**
     * By definition protected header is in first CBOR element (CBOR encoded byte string)
     */
    private function getProtectedHeader(): ByteStringObject
    {
        return $this->cose->get(0);
    }

    /**
     * By definition unprotected header is in second CBOR element
     */
    private function getUnprotectedHeader(): MapObject
    {
        return $this->cose->get(1);
    }

    /**
     * By definition DCC payload is in third CBOR element
     */
    private function getQRCodePayload(): ByteStringObject
    {
        return $this->cose->get(2);
    }

    /**
     * By definition DCC signature is in fourth CBOR element
     */
    private function getSignature(): ByteStringObject
    {
        return $this->cose->get(3);
    }

    /**
     * Remove prefix from qr code payload which is not part of encrypted payload
     *
     * @throws QRCodeDecodeException
     */
    private function decode45(): string
    {
        $qrContent = null;
        foreach (self::PREFIX_TO_REMOVE as $prefix) {
            if (substr($this->raw, 0, strlen($prefix)) === $prefix) {
                $qrContent = substr($this->raw, strlen($prefix));

                break;
            }
        }

        try {
            return (new Base45())->decode($qrContent ?? $this->raw);
        } catch (Exception $e) {
            throw new QRCodeDecodeException('QRCode could not be decoded with base45');
        }
    }

    /**
     * @throws QRCodeDecodeException
     */
    private function decompressQrCode(string $data): string
    {
        try {
            $result = zlib_decode($data);
        } catch (\Exception $exception) {
            throw new QRCodeDecodeException('QRCode could not be decompressed with zlib');
        }

        return $result;
    }
}
