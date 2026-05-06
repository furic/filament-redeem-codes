<?php

namespace Furic\FilamentRedeemCodes\Exceptions;

class CodeNotFound extends RedeemException
{
    public function __construct(string $code)
    {
        parent::__construct("The code `{$code}` does not exist.");
    }

    protected function statusCode(): int
    {
        return 404;
    }

    protected function errorCode(): string
    {
        return 'code_not_found';
    }
}
