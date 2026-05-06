# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository type

This is a **Laravel package** (`furic/filament-redeem-codes` on Packagist), not a standalone application. It is a Filament v5 plugin for batch-issued, multi-reward redeem codes — built for game player compensation and event rewards.

- Requires PHP `^8.2`, Laravel `^11|^12|^13`, Filament `^5.0`
- Uses [Spatie LaravelPackageTools](https://github.com/spatie/laravel-package-tools) as the service provider base
- Runtime exercise happens inside a host app — there is no standalone app to `serve` in this repo

## Common commands

```bash
composer test                    # run the full Pest test suite
vendor/bin/pest --filter "test name"  # run a single test
```

There is no build step. When developing locally, use a path repository in the host app's `composer.json`:
```json
{"type": "path", "url": "../filament-redeem-codes", "options": {"symlink": true}}
```

## Architecture

### Service provider
[src/FilamentRedeemCodesServiceProvider.php](src/FilamentRedeemCodesServiceProvider.php) wires up the package via Spatie's `PackageServiceProvider`. It registers:
- Config file (`config/filament-redeem-codes.php`)
- 5 migration stubs under `database/migrations/*.php.stub`
- A named rate limiter `redeem-codes` (reads from `api.rate_limit` config)
- API routes from `routes/api.php` (skipped when `api.enabled = false`)
- Exception rendering for `RedeemException` subclasses → structured JSON responses

### Plugin registration
[src/FilamentRedeemCodesPlugin.php](src/FilamentRedeemCodesPlugin.php) implements `Filament\Contracts\Plugin`. It always registers `RedeemCampaignResource`, and conditionally registers `RedeemRewardTypeResource` only when `reward_type` config is an Eloquent `Model` subclass (not an enum, not null).

### Data model
The key topology: **one `RedeemCampaign` → many `RedeemCode`s → shared `RedeemCodeReward`s** (rewards belong to the campaign, not individual codes). All codes in a batch share the same reward set.

- `RedeemCampaign` — the batch unit; has `start_at`/`end_at` for date-gating, `isActive()` helper
- `RedeemCode` — individual code; `redeemed_at` nullable timestamp (null = unredeemed); `reusable` boolean skips marking redeemed on use
- `RedeemCodeReward` — `campaign_id`, `type` (string or enum), `amount`, `item_id`, `payload`
- `RedeemCodeHistory` — audit log written on every successful redemption (IP, user-agent)
- `RedeemRewardType` — optional admin-managed reward type model (only used when bound in config)

### Reward type binding (three strategies)

Controlled by `filament-redeem-codes.reward_type` config:

1. **`null`** (default) — free-text input in the campaign form; `type` stored/returned as raw string; no `label` in API response
2. **Backed enum** implementing `Furic\FilamentRedeemCodes\Contracts\RewardType` — campaign form shows a typed `Select`; `RedeemCodeReward::$type` hydrates as the enum; `type_label` returns the case name
3. **Eloquent model** (bundled `RedeemRewardType` or custom) — `Reward Types` admin page appears; `type_label` looks up `label` column by `key`; API includes `label` alongside `type`

`RedeemCodeReward::casts()` (method, not property) conditionally casts `type` to the enum class only when an enum is configured. `getTypeLabelAttribute()` handles all three branches.

### Actions
- **`GenerateCodes`** — generates unique codes using alphabet `23456789ABCDEFGHJKLMNPQRSTUVWXYZ` (no `0/1/O/I` for OCR clarity). Forces `count=1` when `reusable=true`. Has a max-attempts guard to prevent infinite loops on near-exhausted keyspaces.
- **`RedeemCodeAction`** — validates code exists → checks redemption state → checks campaign dates → marks redeemed → records history → fires `RedeemCodeRedeemed` event.

### Filament resources
Each resource follows the pattern: `Resources/{Name}/` containing the Resource class, `Pages/` (List/Create/Edit), and `Schemas/` (Form + Table as separate schema classes). Filament v5 uses `Schema` (not `Form`) and `$schema->components([])`.

### API
Single route: `GET /api/redeem/{code}` — returns the code and its rewards on success, structured error JSON on failure. Rewards include `label` only when a binding is configured. Rate-limited via the `redeem-codes` named limiter.

### Testing
Tests use Orchestra Testbench + Pest 3. Migrations are loaded by `include`-ing each `.php.stub` file in `tests/TestCase.php::defineDatabaseMigrations()` — `loadMigrationsFrom()` won't pick up `.stub` files.

## Conventions

- Migration stubs are in `database/migrations/*.php.stub`. If you add a migration, also register its name in `FilamentRedeemCodesServiceProvider::configurePackage()` and add an `include` in `tests/TestCase.php`.
- All models use explicit FK names (`'campaign_id'`) in relations — Laravel would infer `redeem_campaign_id` from the model name, which is wrong.
- `RedeemCodeReward::casts()` is a method (required for dynamic config-based casting). Don't convert it to a property.
- The code alphabet and length are config-driven. If you change defaults, update both `config/filament-redeem-codes.php` and the `string('code', 12)` migration column.
- `RedeemRewardTypeResource` must only appear when the config binding is an Eloquent model — the guard is in `FilamentRedeemCodesPlugin::shouldRegisterRewardTypeResource()`.
