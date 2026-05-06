<?php

use Furic\FilamentRedeemCodes\Actions\GenerateCodes;
use Furic\FilamentRedeemCodes\Models\RedeemCampaign;
use Furic\FilamentRedeemCodes\Models\RedeemCodeReward;
use Furic\FilamentRedeemCodes\Models\RedeemRewardType;

beforeEach(function () {
    config()->set('filament-redeem-codes.reward_type', RedeemRewardType::class);

    RedeemRewardType::create(['key' => 'coins', 'label' => 'Coins', 'icon' => 'heroicon-o-currency-dollar', 'sort_order' => 1]);
    RedeemRewardType::create(['key' => 'gems', 'label' => 'Gems', 'icon' => 'heroicon-o-sparkles', 'sort_order' => 2]);
});

it('persists reward type as the key string', function () {
    $campaign = RedeemCampaign::create(['name' => 'Demo']);
    RedeemCodeReward::create(['campaign_id' => $campaign->id, 'type' => 'coins', 'amount' => 500]);

    $reward = RedeemCodeReward::first();

    expect($reward->type)->toBe('coins');
});

it('resolves type_label via the bound model', function () {
    $campaign = RedeemCampaign::create(['name' => 'Demo']);
    RedeemCodeReward::create(['campaign_id' => $campaign->id, 'type' => 'gems', 'amount' => 25]);

    $reward = RedeemCodeReward::first();

    expect($reward->type_label)->toBe('Gems');
});

it('returns null type_label when the key has no matching row', function () {
    $campaign = RedeemCampaign::create(['name' => 'Demo']);
    RedeemCodeReward::create(['campaign_id' => $campaign->id, 'type' => 'unknown', 'amount' => 1]);

    expect(RedeemCodeReward::first()->type_label)->toBeNull();
});

it('exposes the resolved label in the redemption API response', function () {
    $campaign = RedeemCampaign::create(['name' => 'Demo']);
    RedeemCodeReward::create(['campaign_id' => $campaign->id, 'type' => 'coins', 'amount' => 500]);
    $code = (new GenerateCodes)->execute($campaign, count: 1)->first();

    $this->getJson("/api/redeem/{$code->code}")
        ->assertOk()
        ->assertJsonPath('data.rewards.0.type', 'coins')
        ->assertJsonPath('data.rewards.0.label', 'Coins');
});

it('omits the label key when no binding is configured', function () {
    config()->set('filament-redeem-codes.reward_type', null);

    $campaign = RedeemCampaign::create(['name' => 'Demo']);
    RedeemCodeReward::create(['campaign_id' => $campaign->id, 'type' => 'free_text_type', 'amount' => 1]);
    $code = (new GenerateCodes)->execute($campaign, count: 1)->first();

    $this->getJson("/api/redeem/{$code->code}")
        ->assertOk()
        ->assertJsonPath('data.rewards.0.type', 'free_text_type')
        ->assertJsonMissingPath('data.rewards.0.label');
});

it('finds reward types by key', function () {
    expect(RedeemRewardType::findByKey('coins')?->label)->toBe('Coins')
        ->and(RedeemRewardType::findByKey('nonexistent'))->toBeNull();
});

it('enforces unique keys', function () {
    expect(fn () => RedeemRewardType::create(['key' => 'coins', 'label' => 'Duplicate']))
        ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});
