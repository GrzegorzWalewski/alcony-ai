<?php

declare(strict_types=1);

namespace Grzojda\AlconyAi\Models;

use Symfony\Component\Dotenv\Dotenv;

class Config
{
    public function __construct(
        Dotenv $dotEnv
    )
    {
        $dotEnv->load(__DIR__.'/../.env');
    }

    public function getConfigValue($key): string
    {
        return $_ENV[$key] ?? '';
    }
}