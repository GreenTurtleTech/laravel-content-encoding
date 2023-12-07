<?php

declare(strict_types=1);

namespace GreenTurtle\Middleware;

use Illuminate\Contracts\Container\ContextualBindingBuilder;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    private const CONFIG_NAME = 'content-encoding';
    private const FILE_NAME = self::CONFIG_NAME . '.php';
    private const CONFIG_PATH = __DIR__ . '/../config/' . self::FILE_NAME;

    public function boot(): void
    {
        $this->publishes(
            [self::CONFIG_PATH => config_path(self::FILE_NAME)],
            [self::CONFIG_NAME]
        );
    }

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, self::CONFIG_NAME);

        $this->needs('$encodeUnknownType')
            ->giveConfig(self::CONFIG_NAME . '.encode_unknown_type');

        $this->needs('$allowedTypes')
            ->giveConfig(self::CONFIG_NAME . '.allowed_types');


        $this->needs('$encoders')
            ->give(array_map(
                fn($encoder) => $this->app->make($encoder),
                config(self::CONFIG_NAME . '.encoders')
            ));
    }

    private function needs(string $argument): ContextualBindingBuilder
    {
        return $this->app->when(ContentEncoding::class)->needs($argument);
    }
}
