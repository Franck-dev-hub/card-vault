<?php

declare(strict_types=1);

namespace App\Service\Licence\Pokemon;

use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;
use TCGdex\TCGdex;

final class TcgdexFactory
{
    public static function create(): TCGdex
    {
        $psr17Factory = new Psr17Factory();
        TCGdex::$requestFactory = $psr17Factory;
        TCGdex::$responseFactory = $psr17Factory;
        TCGdex::$client = new Psr18Client();

        return new TCGdex('en');
    }
}
