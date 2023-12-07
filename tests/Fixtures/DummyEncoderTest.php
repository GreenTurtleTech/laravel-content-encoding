<?php

declare(strict_types=1);

namespace GreenTurtle\Middleware\Tests\Fixtures;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DummyEncoder::class)]
class DummyEncoderTest extends TestCase
{
    #[Test]
    public function itSupportsWhatYouGiveIt(): void
    {
        $supportedEncoding = 'gzip';

        $sut = new DummyEncoder($supportedEncoding);

        self::assertTrue($sut->supports($supportedEncoding));
    }

    #[Test]
    public function itEncodesToWhatYouGiveIt(): void
    {
        $encodesTo = 'this is what it encodes to';

        $sut = new DummyEncoder(encodesTo: $encodesTo);

        self::assertSame($encodesTo, $sut->encode(''));
    }
}
