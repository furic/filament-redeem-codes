<?php

namespace Furic\FilamentRedeemCodes\Actions;

use Furic\FilamentRedeemCodes\Models\RedeemCampaign;
use Furic\FilamentRedeemCodes\Models\RedeemCode;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

class GenerateCodes
{
    /**
     * Generate $count unique codes for $campaign and persist them.
     * If $reusable is true, $count is forced to 1 (a reusable code by definition has no per-issuance limit).
     */
    public function execute(
        RedeemCampaign $campaign,
        int $count = 1,
        ?string $prefix = null,
        bool $reusable = false,
    ): Collection {
        if ($reusable) {
            $count = 1;
        }

        if ($count < 1) {
            throw new InvalidArgumentException('Count must be at least 1.');
        }

        $length = (int) config('filament-redeem-codes.code.length', 12);
        $alphabet = (string) config('filament-redeem-codes.code.alphabet');
        $prefix = $prefix !== null && $prefix !== '' ? strtoupper($prefix) : '';

        if (strlen($prefix) >= $length) {
            throw new InvalidArgumentException("Prefix must be shorter than the configured code length of {$length}.");
        }

        $randomLength = $length - strlen($prefix);
        $alphabetLength = strlen($alphabet);

        if ($alphabetLength < 2) {
            throw new InvalidArgumentException('Alphabet must contain at least 2 distinct characters.');
        }

        $codes = collect();
        $attempts = 0;
        $maxAttempts = max($count * 10, 50);

        while ($codes->count() < $count) {
            if (++$attempts > $maxAttempts) {
                throw new RuntimeException("Could not generate {$count} unique codes after {$maxAttempts} attempts; widen the alphabet, shorten the prefix, or increase code length.");
            }

            $random = '';
            for ($i = 0; $i < $randomLength; $i++) {
                $random .= $alphabet[random_int(0, $alphabetLength - 1)];
            }
            $candidate = $prefix . $random;

            if ($codes->contains(fn ($c) => $c->code === $candidate)) {
                continue;
            }
            if (RedeemCode::where('code', $candidate)->exists()) {
                continue;
            }

            $codes->push(RedeemCode::create([
                'campaign_id' => $campaign->id,
                'code' => $candidate,
                'reusable' => $reusable,
            ]));
        }

        return $codes;
    }
}
