<?php

namespace Furic\FilamentRedeemCodes\Http\Controllers;

use Furic\FilamentRedeemCodes\Actions\RedeemCodeAction;
use Furic\FilamentRedeemCodes\Http\Resources\RedeemCodeResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RedeemController extends Controller
{
    public function redeem(Request $request, string $code, RedeemCodeAction $action): RedeemCodeResource
    {
        $redeemCode = $action->execute(
            code: $code,
            ip: $request->ip(),
            agent: substr((string) $request->userAgent(), 0, 1024),
        );

        return new RedeemCodeResource($redeemCode);
    }
}
