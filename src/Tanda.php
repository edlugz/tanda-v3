<?php

declare(strict_types=1);

namespace EdLugz\Tanda;

use EdLugz\Tanda\Exceptions\TandaException;
use EdLugz\Tanda\Helpers\TandaHelper;
use EdLugz\Tanda\Requests\{
    B2B, B2C, C2B, P2P, Status
};

final class Tanda
{

    /**
     * @throws TandaException
     */
    public function p2p(string $resultUrl): P2P
    {
        return new P2P($resultUrl);
    }

    /**
     * @throws TandaException
     */
    public function c2b(string $resultUrl): C2B
    {
        return new C2B($resultUrl);
    }

    /**
     * @throws TandaException
     */
    public function b2c(string $resultUrl): B2C
    {
        return new B2C($resultUrl);
    }

    /**
     * @throws TandaException
     */
    public function b2b(string $resultUrl): B2B
    {
        return new B2B($resultUrl);
    }

    public function status(): Status
    {
        return new Status();
    }

    public function helper(): TandaHelper
    {
        return new TandaHelper();
    }
}
