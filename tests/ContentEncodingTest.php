<?php

declare(strict_types=1);

namespace GreenTurtle\Middleware\Tests;

use Closure;
use Generator;
use GreenTurtle\Middleware\ContentEncoding;
use GreenTurtle\Middleware\Encoder\ContentEncoder;
use GreenTurtle\Middleware\Tests\Fixtures\DummyEncoder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentEncoding::class)]
class ContentEncodingTest extends TestCase
{
    /** @param array<string,string> $headers */
    private static function request(array $headers): Request
    {
        $request = new Request();
        foreach ($headers as $key => $value) {
            $request->headers->set($key, $value);
        }
        return $request;
    }

    /** @param array<string, string> $headers */
    private function next(string $content, array $headers): Closure
    {
        return fn(Request $request) => new Response(
            content: $content,
            headers: $headers,
        );
    }

    #[Test]
    public function itWontEncodeEmptyContent(): void
    {
        $expected = new Response(content: '', headers: []);

        $sut = new ContentEncoding(false, ['#.*#'], [new DummyEncoder()]);

        $actual = $sut->handle(
            $this->request([
                'Accept-Encoding' => 'dummy',
                'Content-Type' => 'text/html',
            ]),
            $this->next('', []),
        );

        self::assertEqualsWithDelta($expected, $actual, 2);
    }

    #[Test]
    public function itWontEncodeEncodedContent(): void
    {
        $expectedContent = 'plain text content';
        $expectedHeaders = ['Content-Encoding' => 'gzip'];
        $expected = new Response(
            content: $expectedContent,
            headers: $expectedHeaders
        );

        $sut = new ContentEncoding(false, ['#.*#'], [new DummyEncoder()]);

        $actual = $sut->handle(
            $this->request([
                'Accept-Encoding' => 'dummy',
                'Content-Type' => 'text/html',
            ]),
            $this->next($expectedContent, $expectedHeaders),
        );

        self::assertEqualsWithDelta($expected, $actual, 2);
    }

    public static function provideEncodingsToAccept(): Generator
    {
        yield 'gzip accepted and supported' => [
            new Response(
                content: 'encoded content',
                headers: ['Content-Encoding' => 'gzip']
            ),
            self::request([
                'Accept-Encoding' => 'gzip',
                'Content-Type' => 'text/html',
            ]),
            new DummyEncoder('gzip', 'encoded content')
        ];

        yield 'gzip accepted but not supported' => [
            new Response(
                content: 'plain text',
                headers: [],
            ),
            self::request([
                'Accept-Encoding' => 'gzip',
                'Content-Type' => 'text/html',
            ]),
            new DummyEncoder('deflate', 'encoded content')
        ];
    }

    #[Test, DataProvider('provideEncodingsToAccept')]
    #[TestDox('It will perform accepted encodings if they are supported')]
    public function itMayEncodeAcceptedEncodings(
        Response $expected,
        Request $request,
        ContentEncoder $encoder
    ): void {
        $sut = new ContentEncoding(false, ['#.*#'], [$encoder]);

        $actual = $sut->handle($request, $this->next('plain text', []));

        self::assertEqualsWithDelta($expected, $actual, 2);
    }

    public static function provideUnknownTypesToEncode(): Generator
    {
        yield 'encode unknown types, gzip accepted and supported' => [
            new Response(
                content: 'encoded content',
                headers: ['Content-Encoding' => 'gzip']
            ),
            true,
            self::request(['Accept-Encoding' => 'gzip']),
            new DummyEncoder('gzip', 'encoded content')
        ];

        yield 'encode unknown types, gzip accepted but not supported' => [
            new Response(content: 'plain text'),
            true,
            self::request(['Accept-Encoding' => 'gzip']),
            new DummyEncoder('deflate', 'encoded content')
        ];

        yield 'encode unknown types, does not accept encoding' => [
            new Response(content: 'plain text'),
            true,
            self::request([]),
            new DummyEncoder('deflate', 'encoded content')
        ];

        yield 'do not encode unknown types, gzip accepted and supported' => [
            new Response(content: 'plain text'),
            false,
            self::request(['Accept-Encoding' => 'gzip']),
            new DummyEncoder('gzip', 'encoded content')
        ];
    }

    #[Test, DataProvider('provideUnknownTypesToEncode')]
    #[TestDox('$encodeUnknownTypes decides whether to encode unknown types')]
    public function itMayEncodeUnknownContentTypes(
        Response $expected,
        bool $encodeUnknownTypes,
        Request $request,
        ContentEncoder $encoder,
    ): void {
        $sut = new ContentEncoding($encodeUnknownTypes, ['#.*#'], [$encoder]);

        $actual = $sut->handle($request, $this->next('plain text', []));

        self::assertEqualsWithDelta($expected, $actual, 2);
    }

    public static function provideContentTypesToAllow(): Generator
    {
        yield 'text/html content that is the one type allowed' => [
            new Response(
                content: 'encoded content',
                headers: ['Content-Encoding' => 'gzip']
            ),
            'text/html',
            ['#^text\/html$#'],
        ];

        yield 'text/html content that is one of many types allowed' => [
            new Response(
                content: 'encoded content',
                headers: ['Content-Encoding' => 'gzip']
            ),
            'text/html',
            [
                '#^(text\/.*)(;.*)?$#',
                '#^(image\/svg\\+xml)(;.*)?$#',
                '#^(application\/json)(;.*)?$#',
            ],
        ];

        yield 'text/html content and anything is allowed' => [
            new Response(
                content: 'encoded content',
                headers: ['Content-Encoding' => 'gzip']
            ),
            'text/html',
            ['#.*#'],
        ];

        yield 'text/html content that is not allowed' => [
            new Response('plain text'),
            'text/html',
            [],
        ];
    }

    /** @param string[] $allowedTypes */
    #[Test, DataProvider('provideContentTypesToAllow')]
    #[TestDox('$allowedTypes determines what content types will be encoded')]
    public function itOnlyEncodesAllowedTypes(
        Response $expected,
        string $contentType,
        array $allowedTypes
    ): void {
        $request = self::request([
            'Content-Type' => $contentType,
            'Accept-Encoding' => 'gzip',
        ]);

        $sut = new ContentEncoding(
            false,
            $allowedTypes,
            [new DummyEncoder('gzip', 'encoded content')]
        );

        $actual = $sut->handle($request, $this->next('plain text', []));

        self::assertEqualsWithDelta($expected, $actual, 2);
    }

    public static function provideEncodersToPrioritise(): Generator
    {
        yield 'two useable encoders' => [
            [
                new DummyEncoder('gzip', 'encoded by first useable encoder'),
                new DummyEncoder('gzip', 'encoded by second useable encoder'),
            ]
        ];

        yield 'first encoder is useable' => [
            [
                new DummyEncoder('gzip', 'encoded by first useable encoder'),
                new DummyEncoder('deflate', 'encoded by unusable encoder'),
            ]
        ];

        yield 'second encoder is useable' => [
            [
                new DummyEncoder('deflate', 'encoded by unusable encoder'),
                new DummyEncoder('gzip', 'encoded by first useable encoder'),
            ]
        ];

        yield 'second and third encoder are useable' => [
            [
                new DummyEncoder('deflate', 'encoded by unusable encoder'),
                new DummyEncoder('gzip', 'encoded by first useable encoder'),
                new DummyEncoder('gzip', 'encoded by second useable encoder'),
            ]
        ];
    }

    /** @param ContentEncoder[] $encoders */
    #[Test, DataProvider('provideEncodersToPrioritise')]
    #[TestDox('$encoders determines what encodings are supported')]
    public function itPrioritisesEncodersByOrderGiven(array $encoders,): void
    {
        $expected = new Response(
            content: 'encoded by first useable encoder',
            headers: ['Content-Encoding' => 'gzip']
        );

        $request = self::request(['Accept-Encoding' => 'gzip']);

        $sut = new ContentEncoding(true, ['#.*#'], $encoders);

        $actual = $sut->handle($request, $this->next('plain text', []));

        self::assertEqualsWithDelta($expected, $actual, 2);
    }
}
