<?php

use Furic\FilamentRedeemCodes\Actions\GenerateCodes;
use Furic\FilamentRedeemCodes\Models\RedeemCampaign;

it('rate-limits the redeem endpoint per IP', function () {
    $campaign = RedeemCampaign::create(['name' => 'RL']);
    $code = (new GenerateCodes)->execute($campaign, count: 1, reusable: true)->first();

    // Config in TestCase sets api.rate_limit to "5,1" — 5 requests per minute.
    for ($i = 0; $i < 5; $i++) {
        $this->getJson("/api/redeem/{$code->code}")->assertOk();
    }

    $this->getJson("/api/redeem/{$code->code}")->assertStatus(429);
});
