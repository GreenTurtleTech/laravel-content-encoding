<?php

declare(strict_types=1);

namespace GreenTurtle\Middleware\Encoder;

interface ContentEncoder
{
    public function supports(string $encoding): bool;

    public function encode(string $content): string;
}
