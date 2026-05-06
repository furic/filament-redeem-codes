<?php

namespace Furic\FilamentRedeemCodes\Exceptions;

class CodeAlreadyRedeemed extends RedeemException
{
    public function __construct()
    {
        parent::__construct('This code has already been redeemed.');
    }

    protected function statusCode(): int
    {
        return 409;
    }

    protected function errorCode(): string
    {
        return 'code_already_redeemed';
    }
}
