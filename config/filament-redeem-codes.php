<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Code generation
    |--------------------------------------------------------------------------
    |
    | length    Total length of generated codes including any prefix.
    | alphabet  Character pool. Default omits 0/1/O/I/L for OCR/print clarity.
    |
    */
    'code' => [
        'length' => 12,
        'alphabet' => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reward type binding
    |--------------------------------------------------------------------------
    |
    | Bind your host app's reward enum here. Must implement
    | Furic\FilamentRedeemCodes\Contracts\RewardType (a marker interface)
    | and be a backed enum. When null, types are stored/returned as raw strings.
    |
    | Example: 'reward_type' => App\Enums\RewardType::class,
    |
    */
    'reward_type' => null,

    /*
    |--------------------------------------------------------------------------
    | Public redemption API
    |--------------------------------------------------------------------------
    */
    'api' => [
        'enabled' => true,
        'prefix' => 'api',
        'middleware' => ['api', 'throttle:redeem-codes'],

        // Format: "<attempts>,<minutes>" — applied per request IP.
        'rate_limit' => '10,1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament panel
    |--------------------------------------------------------------------------
    */
    'filament' => [
        'navigation_group' => 'Redeem Codes',
        'navigation_icon' => 'heroicon-o-ticket',
    ],

    /*
    |--------------------------------------------------------------------------
    | Table names
    |--------------------------------------------------------------------------
    */
    'table_names' => [
        'campaigns' => 'redeem_campaigns',
        'codes' => 'redeem_codes',
        'rewards' => 'redeem_code_rewards',
        'histories' => 'redeem_code_histories',
    ],
];
