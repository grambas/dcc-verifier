<?php

declare(strict_types=1);

namespace Grambas\Cbor;

use CBOR\CBORObject;
use CBOR\Tag;

final class CoseSign1Tag extends Tag
{
    public static function getTagId(): int
    {
        return 18;
    }

    public static function createFromLoadedData(int $additionalInformation, ?string $data, CBORObject $object): Tag
    {
        return new self($additionalInformation, $data, $object);
    }

    public function getNormalizedData(bool $ignoreTags = false)
    {
        return $this->getValue()->getNormalizedData($ignoreTags);
    }
}
