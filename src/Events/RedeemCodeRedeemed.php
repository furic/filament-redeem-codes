<?php

namespace Furic\FilamentRedeemCodes\Events;

use Furic\FilamentRedeemCodes\Models\RedeemCode;
use Illuminate\Foundation\Events\Dispatchable;

class RedeemCodeRedeemed
{
    use Dispatchable;

    public function __construct(public readonly RedeemCode $code)
    {
    }
}
