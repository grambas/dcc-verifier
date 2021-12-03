<?php

declare(strict_types=1);

namespace Grambas\Test;

use Grambas\Model\DSC;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function test_dsc_ok(): void
    {
        $dsc = new DSC('test raw data', 'test kid');

        static::assertInstanceOf(DSC::class, $dsc);
        static::assertEquals('test raw data', $dsc->getRawData());
        static::assertEquals('test kid', $dsc->getKid());
    }
}
