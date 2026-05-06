<?php

use Furic\FilamentRedeemCodes\Actions\GenerateCodes;
use Furic\FilamentRedeemCodes\Models\RedeemCampaign;
use Furic\FilamentRedeemCodes\Models\RedeemCode;

it('generates the requested number of unique codes for a campaign', function () {
    $campaign = RedeemCampaign::create(['name' => 'Easter 2026']);

    $codes = (new GenerateCodes)->execute($campaign, count: 50);

    expect($codes)->toHaveCount(50)
        ->and($codes->pluck('code')->unique())->toHaveCount(50)
        ->and($codes->first()->code)->toHaveLength(12);

    expect(RedeemCode::count())->toBe(50);
});

it('respects the configured prefix and pads to total length', function () {
    $campaign = RedeemCampaign::create(['name' => 'Test']);

    $codes = (new GenerateCodes)->execute($campaign, count: 5, prefix: 'XMAS');

    expect($codes->first()->code)->toStartWith('XMAS')
        ->and($codes->first()->code)->toHaveLength(12);
});

it('uppercases lowercase prefix input', function () {
    $campaign = RedeemCampaign::create(['name' => 'Test']);

    $codes = (new GenerateCodes)->execute($campaign, count: 1, prefix: 'fall');

    expect($codes->first()->code)->toStartWith('FALL');
});

it('forces count to 1 when reusable is true', function () {
    $campaign = RedeemCampaign::create(['name' => 'Test']);

    $codes = (new GenerateCodes)->execute($campaign, count: 99, reusable: true);

    expect($codes)->toHaveCount(1)
        ->and($codes->first()->reusable)->toBeTrue();
});

it('only uses unambiguous alphabet characters', function () {
    $campaign = RedeemCampaign::create(['name' => 'Test']);

    $codes = (new GenerateCodes)->execute($campaign, count: 100);

    foreach ($codes as $code) {
        expect($code->code)->not->toMatch('/[01OI]/');
    }
});

it('throws when prefix is at least as long as configured code length', function () {
    config()->set('filament-redeem-codes.code.length', 8);
    $campaign = RedeemCampaign::create(['name' => 'Test']);

    expect(fn () => (new GenerateCodes)->execute($campaign, count: 1, prefix: 'TOOLONG_'))
        ->toThrow(InvalidArgumentException::class);
});

it('throws when count is below 1', function () {
    $campaign = RedeemCampaign::create(['name' => 'Test']);

    expect(fn () => (new GenerateCodes)->execute($campaign, count: 0))
        ->toThrow(InvalidArgumentException::class);
});
