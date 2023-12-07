<?php

declare(strict_types=1);

namespace Encoder;

use Generator;
use GreenTurtle\Middleware\Encoder\Gzip;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Gzip::class)]
class GzipTest extends TestCase
{
    #[Test]
    public function itProvidesGzipEncoding(): void
    {
        self::assertTrue((new Gzip())->supports('gzip'));
    }

    public static function provideStringsToEncode(): Generator
    {
        yield 'empty string' => [''];
        yield 'single word' => ['expected'];
        yield 'multiple words' => ['this is the expected result'];
    }

    #[Test, DataProvider('provideStringsToEncode')]
    public function itEncodesStringToGzip(string $stringToEncode): void
    {
        $expected = gzencode($stringToEncode);

        $actual = (new Gzip())->encode($stringToEncode);

        self::assertSame($expected, $actual);
    }
}
