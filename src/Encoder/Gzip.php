<?php

declare(strict_types=1);

namespace GreenTurtle\Middleware\Encoder;

use GreenTurtle\Middleware\Exception\FailedToEncode;

final class Gzip implements ContentEncoder
{
    private const SUPPORTED_ENCODINGS = [
        'gzip',
        'x-gzip',
    ];

    public function supports(string $encoding): bool
    {
        return in_array(strtolower($encoding), self::SUPPORTED_ENCODINGS);
    }

    public function encode(string $content): string
    {
        $encodedContent = gzencode($content);

        if (!$encodedContent) {
            throw FailedToEncode::errorOccured();
        }

        return $encodedContent;
    }
}
