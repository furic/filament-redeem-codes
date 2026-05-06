<?php

namespace Furic\FilamentRedeemCodes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedeemCodeHistory extends Model
{
    use HasFactory;

    protected $fillable = ['redeem_code_id', 'ip', 'agent'];

    public function getTable(): string
    {
        return config('filament-redeem-codes.table_names.histories', 'redeem_code_histories');
    }

    public function redeemCode(): BelongsTo
    {
        return $this->belongsTo(RedeemCode::class);
    }
}
