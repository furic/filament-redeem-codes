<?php

namespace Furic\FilamentRedeemCodes;

use Furic\FilamentRedeemCodes\Exceptions\RedeemException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentRedeemCodesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-redeem-codes')
            ->hasConfigFile('filament-redeem-codes')
            ->hasMigrations([
                'create_redeem_campaigns_table',
                'create_redeem_codes_table',
                'create_redeem_code_rewards_table',
                'create_redeem_code_histories_table',
                'create_redeem_reward_types_table',
            ]);
    }

    public function packageBooted(): void
    {
        $this->registerRateLimiter();
        $this->registerApiRoutes();
        $this->registerExceptionRendering();
    }

    protected function registerRateLimiter(): void
    {
        RateLimiter::for('redeem-codes', function (Request $request) {
            [$attempts, $minutes] = $this->parseRateLimit(
                (string) config('filament-redeem-codes.api.rate_limit', '10,1')
            );

            return Limit::perMinutes($minutes, $attempts)->by($request->ip());
        });
    }

    protected function registerApiRoutes(): void
    {
        if (! config('filament-redeem-codes.api.enabled', true)) {
            return;
        }

        Route::prefix(config('filament-redeem-codes.api.prefix', 'api'))
            ->middleware(config('filament-redeem-codes.api.middleware', ['api']))
            ->group(__DIR__ . '/../routes/api.php');
    }

    protected function registerExceptionRendering(): void
    {
        $exceptionHandler = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class);

        if (method_exists($exceptionHandler, 'renderable')) {
            $exceptionHandler->renderable(function (RedeemException $e, Request $request) {
                if ($request->expectsJson() || $request->is(config('filament-redeem-codes.api.prefix', 'api') . '/*')) {
                    return $e->render();
                }

                return null;
            });
        }
    }

    /**
     * @return array{0: int, 1: int} [attempts, minutes]
     */
    protected function parseRateLimit(string $value): array
    {
        $parts = array_map('intval', explode(',', $value, 2));

        return [
            max(1, $parts[0] ?? 10),
            max(1, $parts[1] ?? 1),
        ];
    }
}
