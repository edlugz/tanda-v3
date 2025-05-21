<?php

namespace EdLugz\Tanda\Facades;

use Illuminate\Support\Facades\Facade;

final class Tanda extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tanda';
    }
}
