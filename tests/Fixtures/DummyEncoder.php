<?php

declare(strict_types=1);

namespace GreenTurtle\Middleware\Tests\Fixtures;

use GreenTurtle\Middleware\Encoder\ContentEncoder;

final class DummyEncoder implements ContentEncoder
{
    public function __construct(
        public readonly string $encoding = 'dummy',
        public readonly string $encodesTo = 'encoded content',
    ) {
    }

    public function supports(string $encoding): bool
    {
        return $encoding === $this->encoding;
    }

    public function encode(string $content): string
    {
        return $this->encodesTo;
    }
}
