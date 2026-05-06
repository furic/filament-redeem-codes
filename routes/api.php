<?php

use Furic\FilamentRedeemCodes\Http\Controllers\RedeemController;
use Illuminate\Support\Facades\Route;

Route::get('redeem/{code}', [RedeemController::class, 'redeem'])
    ->name('redeem-codes.redeem');
