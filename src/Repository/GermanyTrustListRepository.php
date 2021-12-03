<?php

declare(strict_types=1);

namespace Grambas\Repository;

use Grambas\Client\GermanyTrustListClient;
use Grambas\Model\DSC;
use function Safe\file_get_contents;

class GermanyTrustListRepository implements TrustListRepositoryInterface
{
    /** @var GermanyTrustListClient */
    protected $client;

    public function __construct(string $dir, bool $demo = false)
    {
        $this->client = new GermanyTrustListClient($dir, $demo);
    }

    public function getByKid(string $kid): DSC
    {
        $countryCertificates = array_filter($this->getList(), static function (DSC $dsc) use ($kid): bool {
            return $dsc->getKid() === $kid;
        });

        if (1 !== count($countryCertificates)) {
            throw new \InvalidArgumentException('Public key not found or not dsc not unique');
        }

        return current($countryCertificates);
    }

    /**
     * @return DSC[]
     */
    public function getList(): array
    {
        $data = json_decode(file_get_contents($this->client->getTrustListFileFullPath()), true);

        $certificates = [];
        foreach ($data['certificates'] as $dsc) {
            $certificates[] = new DSC($dsc['rawData'], $dsc['kid']);
        }

        return $certificates;
    }
}
