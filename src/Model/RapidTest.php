<?php

declare(strict_types=1);

namespace Grambas\Model;

class RapidTest extends AbstractTest implements CertificateInterface
{
    public const ID = 8;

    /** @var string|null */
    public $testDeviceIdentifier;

    public function __construct(array $t)
    {
        parent::__construct($t);

        $this->testDeviceIdentifier =  $t[0]['ma'] ?? null;
    }

    public function getId(): int
    {
        return CertificateInterface::PCR_TEST;
    }
}
