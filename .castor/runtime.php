<?php

declare(strict_types=1);

enum Runtime: string
{
    case Host = 'host';
    case Ci = 'ci';
}

enum App: string
{
    case Backend = 'api';
    case Frontend = 'frontend';
    case Ml = 'ml';

    public function directory(): string
    {
        return \dirname(__DIR__) . '/apps/' . $this->value;
    }
}
