<?php

namespace Furic\FilamentRedeemCodes\Tests;

use Furic\FilamentRedeemCodes\FilamentRedeemCodesServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Furic\\FilamentRedeemCodes\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            FilamentRedeemCodesServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('filament-redeem-codes.api.enabled', true);
        $app['config']->set('filament-redeem-codes.api.rate_limit', '5,1');
    }

    protected function defineDatabaseMigrations(): void
    {
        $stubs = glob(__DIR__ . '/../database/migrations/*.php.stub');
        sort($stubs);

        foreach ($stubs as $stubPath) {
            $migration = include $stubPath;

            if ($migration instanceof Migration) {
                $migration->up();
            }
        }
    }
}
