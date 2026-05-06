<?php

use Furic\FilamentRedeemCodes\Actions\GenerateCodes;
use Furic\FilamentRedeemCodes\Events\RedeemCodeRedeemed;
use Furic\FilamentRedeemCodes\Models\RedeemCampaign;
use Furic\FilamentRedeemCodes\Models\RedeemCode;
use Furic\FilamentRedeemCodes\Models\RedeemCodeHistory;
use Furic\FilamentRedeemCodes\Models\RedeemCodeReward;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->campaign = RedeemCampaign::create(['name' => 'Launch Bonus']);
    RedeemCodeReward::create([
        'campaign_id' => $this->campaign->id,
        'type' => 'coins',
        'amount' => 500,
    ]);
    $this->code = (new GenerateCodes)->execute($this->campaign, count: 1)->first();
});

it('redeems a valid code and returns rewards', function () {
    Event::fake();

    $response = $this->getJson("/api/redeem/{$this->code->code}");

    $response->assertOk()
        ->assertJsonPath('data.code', $this->code->code)
        ->assertJsonPath('data.reusable', false)
        ->assertJsonPath('data.rewards.0.type', 'coins')
        ->assertJsonPath('data.rewards.0.amount', 500);

    expect($this->code->fresh()->isRedeemed())->toBeTrue();
    Event::assertDispatched(RedeemCodeRedeemed::class);
});

it('records a redeem history row with ip and user agent', function () {
    $this->getJson("/api/redeem/{$this->code->code}", [
        'User-Agent' => 'PestTest/1.0',
    ])->assertOk();

    $history = RedeemCodeHistory::first();

    expect($history)->not->toBeNull()
        ->and($history->redeem_code_id)->toBe($this->code->id)
        ->and($history->agent)->toBe('PestTest/1.0')
        ->and($history->ip)->not->toBeNull();
});

it('rejects an unknown code with 404', function () {
    $this->getJson('/api/redeem/NOPE12345678')
        ->assertStatus(404)
        ->assertJsonPath('error', 'code_not_found');
});

it('rejects a code that has already been redeemed', function () {
    $this->getJson("/api/redeem/{$this->code->code}")->assertOk();

    $this->getJson("/api/redeem/{$this->code->code}")
        ->assertStatus(409)
        ->assertJsonPath('error', 'code_already_redeemed');
});

it('rejects a code whose campaign has not started', function () {
    $this->campaign->update(['start_at' => now()->addDay()]);

    $this->getJson("/api/redeem/{$this->code->code}")
        ->assertStatus(422)
        ->assertJsonPath('error', 'campaign_not_started');

    expect($this->code->fresh()->isRedeemed())->toBeFalse();
});

it('rejects a code whose campaign has expired', function () {
    $this->campaign->update(['end_at' => now()->subDay()]);

    $this->getJson("/api/redeem/{$this->code->code}")
        ->assertStatus(410)
        ->assertJsonPath('error', 'campaign_expired');
});

it('lets a reusable code be redeemed multiple times', function () {
    $reusable = (new GenerateCodes)->execute($this->campaign, count: 1, reusable: true)->first();

    for ($i = 0; $i < 3; $i++) {
        $this->getJson("/api/redeem/{$reusable->code}")->assertOk();
    }

    expect(RedeemCodeHistory::where('redeem_code_id', $reusable->id)->count())->toBe(3)
        ->and($reusable->fresh()->isRedeemed())->toBeFalse();
});
