<?php

namespace Furic\FilamentRedeemCodes\Contracts;

/**
 * Marker interface — host apps bind a backed enum implementing this to
 * config('filament-redeem-codes.reward_type') so the package can cast
 * RedeemCodeReward::$type to a strongly-typed value.
 */
interface RewardType
{
}
