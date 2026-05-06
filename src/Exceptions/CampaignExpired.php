<?php

namespace Furic\FilamentRedeemCodes\Exceptions;

class CampaignExpired extends RedeemException
{
    public function __construct()
    {
        parent::__construct('This code has expired.');
    }

    protected function statusCode(): int
    {
        return 410;
    }

    protected function errorCode(): string
    {
        return 'campaign_expired';
    }
}
