<?php

namespace Furic\FilamentRedeemCodes\Exceptions;

class CampaignNotStarted extends RedeemException
{
    public function __construct()
    {
        parent::__construct('This code is not active yet.');
    }

    protected function statusCode(): int
    {
        return 422;
    }

    protected function errorCode(): string
    {
        return 'campaign_not_started';
    }
}
