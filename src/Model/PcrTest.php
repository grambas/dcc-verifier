<?php

declare(strict_types=1);

namespace Grambas\Model;

class PcrTest extends AbstractTest implements CertificateInterface
{
    /** @var string|null */
    public $name;

    public function __construct(array $t)
    {
        parent::__construct($t);

        $this->name =  $t[0]['nm'] ?? null;
    }

    public function getId(): int
    {
        return CertificateInterface::PCR_TEST;
    }
}
