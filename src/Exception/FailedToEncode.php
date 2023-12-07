<?php

declare(strict_types=1);

namespace GreenTurtle\Middleware\Exception;

use DomainException;

final class FailedToEncode extends DomainException
{
    private const ENCODING_ERROR = 0;

    public static function errorOccured(): self
    {
        return new self('Failed to encode content', self::ENCODING_ERROR);
    }
}
