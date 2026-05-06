<?php

namespace Furic\FilamentRedeemCodes\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Furic\FilamentRedeemCodes\Models\RedeemCode
 */
class RedeemCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'reusable' => $this->isReusable(),
            'redeemed_at' => $this->redeemed_at?->toIso8601String(),
            'campaign' => $this->whenLoaded('campaign', fn () => [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
                'description' => $this->campaign->description,
            ]),
            'rewards' => $this->rewards->map(fn ($reward) => array_filter([
                'type' => $reward->type instanceof \BackedEnum ? $reward->type->value : $reward->type,
                'label' => $reward->type_label,
                'amount' => $reward->amount,
                'item_id' => $reward->item_id,
                'payload' => $reward->payload,
            ], fn ($value) => $value !== null))->values(),
        ];
    }
}
