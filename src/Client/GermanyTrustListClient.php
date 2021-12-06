<?php

declare(strict_types=1);

namespace Grambas\Client;

use Cose\Algorithm\Signature\ECDSA\ECSignature;
use GuzzleHttp\Client;
use RuntimeException;

/**
 * https://github.com/eu-digital-green-certificates/dgc-participating-countries/issues/10
 * https://github.com/Digitaler-Impfnachweis/certification-apis/tree/master/dsc-update
 * https://raw.githubusercontent.com/Digitaler-Impfnachweis/covpass-ios/main/Certificates/PROD_RKI/CA/pubkey.pem
 */
class GermanyTrustListClient
{
    public const GERMANY_TRUST_LIST_PROD_ENDPOINT = 'https://de.dscg.ubirch.com/trustList/DSC';
    public const GERMANY_TRUST_LIST_DEV_ENDPOINT = 'https://de.test.dscg.ubirch.com/trustList/DSC';

    public const ENV_PROD = 'prod';
    public const ENV_DEMO = 'demo';

    public const AVAILABLE_ENVS = [
        self::ENV_DEMO,
        self::ENV_PROD,
    ];

    public const SKIP = 0;
    public const UPDATED = 1;

    /** @var Client */
    private $client;

    /** @var string */
    protected $url;

    /** @var string */
    protected $trustListFile;

    /** @var string */
    protected $pemFile;

    public function __construct(string $dir, bool $demo)
    {
        $this->url = $demo ? self::GERMANY_TRUST_LIST_DEV_ENDPOINT : self::GERMANY_TRUST_LIST_PROD_ENDPOINT;
        $env = $demo ? self::ENV_DEMO : self::ENV_PROD;

        $this->trustListFile = sprintf('%s/%s-%s-dsc-list.json', $dir, 'de', $env);
        $this->pemFile = sprintf('%s/%s-%s-dsc-list-signing-key.pem', $dir, 'de', $env);
    }

    public function update(): int
    {
        $content = $this->getTrustList();

        try {
            $currentContent = file_get_contents($this->trustListFile);
        } catch (\Exception $exception) {
            $currentContent = null;
        }

        if ($currentContent === $content) {
            return self::SKIP;
        }

        $result = file_put_contents(
            $this->trustListFile,
            $content
        );

        if (false === $result) {
            throw new RuntimeException('New trust list could not be saved!');
        }

        return self::UPDATED;
    }

    private function getTrustList(): string
    {
        $response = $this->getClient()->request('GET', $this->url);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('');
        }

        $content = (string) $response->getBody();
        if (empty($content)) {
            throw new RuntimeException('Trust list body not valid!');
        }

        /** On the first line the base64 encoded signature of the contents of from the second line  */
        [$signature, $content] = explode(PHP_EOL, $content);

        if (!is_string($signature) || !is_string($content)) {
            throw new RuntimeException('Trust list data not valid!');
        }

        $this->verify($signature, $content);

        return $content;
    }

    private function verify(string $signature, string $content): void
    {
        $pem = file_get_contents($this->pemFile);

        if (empty($pem)) {
            throw new RuntimeException('Pem file could not be located');
        }

        $decodedSignature =  base64_decode($signature);

        $derSignature = ECSignature::toAsn1($decodedSignature, 64);
        $isValid = 1 === openssl_verify($content, $derSignature, $pem, 'sha256');

        if (!$isValid) {
            throw new RuntimeException('Signature validation failed: ' . openssl_error_string());
        }
    }

    public function getClient(): Client
    {
        if (null !== $this->client) {
            return $this->client;
        }

        return $this->client = new Client();
    }

    public function getTrustListFileFullPath(): string
    {
        return $this->trustListFile;
    }
}
