<?php

namespace ZirveDonusum\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Varsayılan client'a erişim (config zirve_donusum.default'a göre)
 *
 * @see \ZirveDonusum\Zirve\ZirvePortalClient
 * @see \ZirveDonusum\Mikro\MikroClient
 */
class ZirveDonusum extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zirve-donusum';
    }
}
