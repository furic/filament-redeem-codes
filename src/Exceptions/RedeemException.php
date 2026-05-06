<?php

namespace Furic\FilamentRedeemCodes\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

abstract class RedeemException extends Exception
{
    abstract protected function statusCode(): int;

    abstract protected function errorCode(): string;

    public function render(): JsonResponse
    {
        return new JsonResponse([
            'error' => $this->errorCode(),
            'message' => $this->getMessage(),
        ], $this->statusCode());
    }
}
