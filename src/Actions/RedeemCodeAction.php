<?php

namespace Furic\FilamentRedeemCodes\Actions;

use Furic\FilamentRedeemCodes\Events\RedeemCodeRedeemed;
use Furic\FilamentRedeemCodes\Exceptions\CampaignExpired;
use Furic\FilamentRedeemCodes\Exceptions\CampaignNotStarted;
use Furic\FilamentRedeemCodes\Exceptions\CodeAlreadyRedeemed;
use Furic\FilamentRedeemCodes\Exceptions\CodeNotFound;
use Furic\FilamentRedeemCodes\Models\RedeemCode;
use Furic\FilamentRedeemCodes\Models\RedeemCodeHistory;

class RedeemCodeAction
{
    /**
     * Validate and (if single-use) mark the code as redeemed.
     * Records a RedeemCodeHistory row on success.
     *
     * @throws CodeNotFound|CodeAlreadyRedeemed|CampaignNotStarted|CampaignExpired
     */
    public function execute(string $code, ?string $ip = null, ?string $agent = null): RedeemCode
    {
        $redeemCode = RedeemCode::findByCode($code);

        if ($redeemCode === null) {
            throw new CodeNotFound($code);
        }

        if (! $redeemCode->isReusable() && $redeemCode->isRedeemed()) {
            throw new CodeAlreadyRedeemed();
        }

        $campaign = $redeemCode->campaign;

        if ($campaign !== null) {
            $now = now();

            if ($campaign->start_at && $now->lt($campaign->start_at)) {
                throw new CampaignNotStarted();
            }

            if ($campaign->end_at && $now->gt($campaign->end_at)) {
                throw new CampaignExpired();
            }
        }

        $redeemCode->markRedeemed();

        RedeemCodeHistory::create([
            'redeem_code_id' => $redeemCode->id,
            'ip' => $ip,
            'agent' => $agent,
        ]);

        event(new RedeemCodeRedeemed($redeemCode));

        return $redeemCode->fresh(['campaign.rewards']);
    }
}
