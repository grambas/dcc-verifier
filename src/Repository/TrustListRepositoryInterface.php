<?php

declare(strict_types=1);

namespace Grambas\Repository;

use Grambas\Model\DSC;

interface TrustListRepositoryInterface
{
    public function getByKid(string $kid): DSC;
}
