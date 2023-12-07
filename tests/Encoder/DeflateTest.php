<?php

declare(strict_types=1);

namespace GreenTurtle\Middleware\Tests\Encoder;

use Generator;
use GreenTurtle\Middleware\Encoder\Deflate;
use GreenTurtle\Middleware\Exception\FailedToEncode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Deflate::class)]
class DeflateTest extends TestCase
{
    #[Test]
    public function itProvidesDeflateEncoding(): void
    {
        self::assertTrue((new Deflate())->supports('deflate'));
    }

    public static function provideStringsToEncode(): Generator
    {
        yield 'empty string' => [''];
        yield 'single word' => ['expected'];
        yield 'multiple words' => ['this is the expected result'];
    }

    #[Test, DataProvider('provideStringsToEncode')]
    public function itEncodesStringToDeflate(string $stringToEncode): void
    {
        $expected = gzdeflate($stringToEncode);

        $actual = (new Deflate())->encode($stringToEncode);

        self::assertSame($expected, $actual);
    }
}
