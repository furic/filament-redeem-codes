# filament-redeem-codes Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Modern Filament v5 plugin for batch-issued, multi-reward redeem codes — the niche the original `furic/redeem-codes` filled, now repositioned for Filament panel users (games, event rewards, player compensation).

**Architecture:** Plugin registers a `RedeemCampaignResource` into the host app's Filament panel. Each campaign batches N codes that share M rewards (the unique data shape inherited from the v1 design). A separate Laravel service provider also exposes a rate-limited `/api/redeem/{code}` endpoint for client apps (Unity, mobile, etc.). Reward types are an interface — host apps bind their own enum.

**Tech Stack:** PHP 8.2+, Laravel 11/12, Filament v5, Pest 3, Spatie LaravelPackageTools (skeleton), Orchestra Testbench (host app for tests).

---

## Decisions locked in

| Decision | Choice | Why |
|---|---|---|
| Filament version | v5 only (`^5.0`) | Current stable; v3/v4 ecosystem already covered by other plugins. New plugins should target current. |
| Laravel | `^11.0 \|\| ^12.0` | Drops everything pre-11. Filament v5 requires this anyway. |
| PHP | `^8.2` | Filament v5 requirement. |
| Reward types | `RewardType` interface, host binds an enum | No more hardcoded `1=Coins, 2=Gems` magic numbers. |
| Code alphabet | Configurable via config; default `23456789ABCDEFGHJKLMNPQRSTUVWXYZ` | Preserves original's unambiguous charset, lets host override. |
| Code length | Configurable, default 12 | Same as v1. |
| Per-user redemption | Still client-responsibility for **reusable** codes; **single-use** codes self-track via `redeemed_at` timestamp. | Same scope as v1 — no auth assumptions. |
| Rate limiting | Built-in on `/api/redeem/{code}` via `throttle` middleware, configurable | New: v1 had none, this is a brute-force vulnerability. |
| Naming | `Event` → `RedeemCampaign`, drops the v1 ambiguity with Laravel events. | Clarity; "campaign" is the correct domain term. |
| Schema fix | `start_at`/`end_at` everywhere (v1 had inconsistent `started_at` in fillable) | Just fix it. |
| `redeemed` boolean | Replaced with nullable `redeemed_at` timestamp | More info; strict null-checks instead of cast gymnastics. |

## File structure

```
filament-redeem-codes/
├── composer.json                                      # NEW
├── README.md                                          # NEW
├── LICENSE                                            # NEW (MIT)
├── phpunit.xml                                        # NEW
├── pest.config.php                                    # NEW
├── .gitignore                                         # NEW
├── config/
│   └── filament-redeem-codes.php                      # NEW: alphabet, length, route prefix, throttle, reward type enum binding
├── database/
│   └── migrations/
│       ├── create_redeem_campaigns_table.php.stub     # NEW (was Event)
│       ├── create_redeem_codes_table.php.stub         # NEW
│       ├── create_redeem_code_rewards_table.php.stub  # NEW
│       └── create_redeem_code_histories_table.php.stub # NEW
├── routes/
│   └── api.php                                        # NEW
├── src/
│   ├── FilamentRedeemCodesServiceProvider.php         # NEW: package boot via Spatie tools
│   ├── FilamentRedeemCodesPlugin.php                  # NEW: Filament plugin entry point
│   ├── Contracts/
│   │   └── RewardType.php                             # NEW: interface host binds enum to
│   ├── Models/
│   │   ├── RedeemCampaign.php                         # NEW
│   │   ├── RedeemCode.php                             # NEW
│   │   ├── RedeemCodeReward.php                       # NEW
│   │   └── RedeemCodeHistory.php                      # NEW
│   ├── Actions/
│   │   ├── GenerateCodes.php                          # NEW: alphabet + bulk insert
│   │   └── RedeemCodeAction.php                       # NEW: validate + record + return rewards
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── RedeemController.php                   # NEW: thin wrapper around RedeemCodeAction
│   │   └── Resources/
│   │       └── RedeemCodeResource.php                 # NEW: API JSON shape
│   ├── Exceptions/
│   │   ├── CodeNotFound.php                           # NEW
│   │   ├── CodeAlreadyRedeemed.php                    # NEW
│   │   ├── CampaignNotStarted.php                     # NEW
│   │   └── CampaignExpired.php                        # NEW
│   └── Filament/
│       └── Resources/
│           └── RedeemCampaigns/
│               ├── RedeemCampaignResource.php         # NEW: Filament v5 Resource
│               ├── Pages/
│               │   ├── ListRedeemCampaigns.php
│               │   ├── CreateRedeemCampaign.php
│               │   └── EditRedeemCampaign.php
│               └── Schemas/
│                   ├── RedeemCampaignForm.php          # NEW: form schema with rewards repeater + GenerateCodes action
│                   └── RedeemCampaignTable.php         # NEW: table schema
└── tests/
    ├── Pest.php
    ├── TestCase.php                                   # extends Orchestra Testbench
    ├── Fixtures/
    │   └── TestRewardType.php                         # enum used by tests
    └── Feature/
        ├── GenerateCodesTest.php
        ├── RedeemCodeApiTest.php
        ├── RateLimitTest.php
        └── ReusableCodeTest.php
```

---

## Task 1: Package skeleton + composer.json

**Files:**
- Create: `composer.json`
- Create: `.gitignore`
- Create: `LICENSE`
- Create: `phpunit.xml`

- [ ] **Step 1: Write composer.json**

```json
{
    "name": "furic/filament-redeem-codes",
    "description": "Filament v5 plugin for batch-issued, multi-reward redeem codes — for games, event rewards and player compensation.",
    "keywords": ["filament", "filamentphp", "laravel", "redeem code", "promo code", "voucher", "game", "reward"],
    "homepage": "https://github.com/furic/filament-redeem-codes",
    "license": "MIT",
    "authors": [{"name": "Richard Fu", "email": "fur@richardfu.net"}],
    "require": {
        "php": "^8.2",
        "filament/filament": "^5.0",
        "illuminate/contracts": "^11.0|^12.0",
        "spatie/laravel-package-tools": "^1.16"
    },
    "require-dev": {
        "orchestra/testbench": "^9.0|^10.0",
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin-laravel": "^3.0"
    },
    "autoload": {
        "psr-4": {"Furic\\FilamentRedeemCodes\\": "src/"}
    },
    "autoload-dev": {
        "psr-4": {"Furic\\FilamentRedeemCodes\\Tests\\": "tests/"}
    },
    "scripts": {
        "test": "vendor/bin/pest",
        "test-coverage": "vendor/bin/pest --coverage"
    },
    "config": {
        "sort-packages": true,
        "allow-plugins": {"pestphp/pest-plugin": true}
    },
    "extra": {
        "laravel": {
            "providers": ["Furic\\FilamentRedeemCodes\\FilamentRedeemCodesServiceProvider"]
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

- [ ] **Step 2: Write .gitignore**

```
/vendor
/node_modules
composer.lock
.phpunit.cache
.phpunit.result.cache
.DS_Store
/build
/coverage
.idea
.vscode
```

- [ ] **Step 3: Write LICENSE (MIT)** — standard MIT text, copyright Richard Fu 2026.

- [ ] **Step 4: Write phpunit.xml**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include><directory>src</directory></include>
    </source>
    <php>
        <env name="DB_CONNECTION" value="testing"/>
        <env name="APP_KEY" value="base64:2fl+Ktvkfl+Fuz4Qp/A75G2RTiWVA/ZoKZvp6fiiM10="/>
    </php>
</phpunit>
```

---

## Task 2: Config

**Files:**
- Create: `config/filament-redeem-codes.php`

- [ ] **Step 1: Write config**

```php
<?php

return [
    'code' => [
        'length' => 12,
        'alphabet' => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ',
    ],
    'reward_type' => null,                 // host binds e.g. App\Enums\RewardType::class
    'api' => [
        'enabled' => true,
        'prefix' => 'api',
        'middleware' => ['api', 'throttle:redeem-codes'],
        'rate_limit' => '10,1',            // 10 attempts per 1 minute per IP
    ],
    'table_names' => [
        'campaigns' => 'redeem_campaigns',
        'codes' => 'redeem_codes',
        'rewards' => 'redeem_code_rewards',
        'histories' => 'redeem_code_histories',
    ],
];
```

---

## Task 3: Migrations

**Files:**
- Create: `database/migrations/create_redeem_campaigns_table.php.stub`
- Create: `database/migrations/create_redeem_codes_table.php.stub`
- Create: `database/migrations/create_redeem_code_rewards_table.php.stub`
- Create: `database/migrations/create_redeem_code_histories_table.php.stub`

All four use anonymous-class style. Schema highlights:

- `redeem_campaigns`: id, name, description nullable, start_at nullable, end_at nullable, timestamps
- `redeem_codes`: id, campaign_id FK, code (string, unique, indexed), reusable bool default false, redeemed_at timestamp nullable, timestamps. **Replaces v1's boolean `redeemed`.**
- `redeem_code_rewards`: id, campaign_id FK, type (string — was numeric in v1), amount unsigned int default 1, item_id unsigned int nullable, payload JSON nullable, timestamps.
- `redeem_code_histories`: id, redeem_code_id FK, ip nullable string(45) (IPv6 ready, was 15), agent text nullable, timestamps.

(Full migration code in implementation — see Task 8 for skeleton.)

---

## Task 4: Models

**Files:**
- Create: `src/Models/RedeemCampaign.php`
- Create: `src/Models/RedeemCode.php`
- Create: `src/Models/RedeemCodeReward.php`
- Create: `src/Models/RedeemCodeHistory.php`

Key choices:
- `RedeemCode::$casts` includes `redeemed_at => datetime`. Add `isRedeemed()` helper, no boolean cast.
- `RedeemCampaign::isActive()` checks `start_at`/`end_at` against `now()`.
- `RedeemCodeReward::$casts` includes `payload => array`, `type => config('filament-redeem-codes.reward_type')` if bound.
- All models use `getTable()` from config so host can rename.

---

## Task 5: Contracts + Exceptions

**Files:**
- Create: `src/Contracts/RewardType.php`
- Create: `src/Exceptions/CodeNotFound.php`
- Create: `src/Exceptions/CodeAlreadyRedeemed.php`
- Create: `src/Exceptions/CampaignNotStarted.php`
- Create: `src/Exceptions/CampaignExpired.php`

`RewardType` is a marker interface — host binds a backed enum. Each exception extends a base `RedeemException` with `toResponse()` for clean API errors.

---

## Task 6: GenerateCodes action (the core unique value)

**Files:**
- Create: `src/Actions/GenerateCodes.php`
- Create: `tests/Feature/GenerateCodesTest.php`

- [ ] **Step 1: Write the failing test**

```php
it('generates the requested number of unique codes for a campaign', function () {
    $campaign = RedeemCampaign::create(['name' => 'Easter 2026']);
    $codes = (new GenerateCodes)->execute($campaign, count: 50);

    expect($codes)->toHaveCount(50)
        ->and($codes->pluck('code')->unique())->toHaveCount(50)
        ->and($codes->first()->code)->toHaveLength(12);
});

it('respects the configured prefix and pads to length', function () {
    $campaign = RedeemCampaign::create(['name' => 'Test']);
    $codes = (new GenerateCodes)->execute($campaign, count: 5, prefix: 'XMAS');
    expect($codes->first()->code)->toStartWith('XMAS')->toHaveLength(12);
});

it('forces count=1 when reusable is true', function () {
    $campaign = RedeemCampaign::create(['name' => 'Test']);
    $codes = (new GenerateCodes)->execute($campaign, count: 99, reusable: true);
    expect($codes)->toHaveCount(1)->and($codes->first()->reusable)->toBeTrue();
});

it('only uses unambiguous characters', function () {
    $campaign = RedeemCampaign::create(['name' => 'Test']);
    $codes = (new GenerateCodes)->execute($campaign, count: 100);
    foreach ($codes as $code) {
        expect($code->code)->not->toMatch('/[01OIL]/');
    }
});
```

- [ ] **Step 2: Run tests, expect FAIL** (`GenerateCodes` undefined)

- [ ] **Step 3: Write `GenerateCodes` action**

```php
<?php

namespace Furic\FilamentRedeemCodes\Actions;

use Furic\FilamentRedeemCodes\Models\RedeemCampaign;
use Furic\FilamentRedeemCodes\Models\RedeemCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GenerateCodes
{
    public function execute(
        RedeemCampaign $campaign,
        int $count = 1,
        ?string $prefix = null,
        bool $reusable = false,
    ): Collection {
        if ($reusable) {
            $count = 1;
        }

        $length = config('filament-redeem-codes.code.length', 12);
        $alphabet = config('filament-redeem-codes.code.alphabet');
        $prefix = $prefix ? strtoupper($prefix) : '';

        if (strlen($prefix) >= $length) {
            throw new \InvalidArgumentException("Prefix must be shorter than configured code length of {$length}.");
        }

        $codes = collect();
        $attempts = 0;
        $maxAttempts = $count * 10;

        while ($codes->count() < $count) {
            if (++$attempts > $maxAttempts) {
                throw new \RuntimeException("Could not generate {$count} unique codes after {$maxAttempts} attempts; widen the alphabet or shorten the prefix.");
            }

            $random = '';
            $randomLength = $length - strlen($prefix);
            for ($i = 0; $i < $randomLength; $i++) {
                $random .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $candidate = $prefix . $random;

            if ($codes->contains('code', $candidate)) {
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
```

- [ ] **Step 4: Run tests, expect PASS**

- [ ] **Step 5: Commit** — `feat: add GenerateCodes action with unambiguous-alphabet generator`

---

## Task 7: RedeemCodeAction (the validate-and-redeem flow)

**Files:**
- Create: `src/Actions/RedeemCodeAction.php`
- Create: `tests/Feature/RedeemCodeApiTest.php`

Behavior:
1. Find code by string or throw `CodeNotFound`.
2. If `redeemed_at` is set AND not reusable → throw `CodeAlreadyRedeemed`.
3. If campaign has `start_at` in future → `CampaignNotStarted`.
4. If campaign has `end_at` in past → `CampaignExpired`.
5. Mark redeemed (skip for reusable).
6. Record history (ip, agent, redeem_code_id).
7. Return RedeemCode with rewards loaded.

Tests cover: happy path, expired, not started, already redeemed, reusable allows multiple, history is recorded, unknown code 404.

---

## Task 8: API route + controller + rate limit

**Files:**
- Create: `routes/api.php`
- Create: `src/Http/Controllers/RedeemController.php`
- Create: `src/Http/Resources/RedeemCodeResource.php`
- Create: `tests/Feature/RateLimitTest.php`

`RedeemController` calls `RedeemCodeAction`, wraps result in `RedeemCodeResource`, returns 200/4xx. Service provider registers the `redeem-codes` rate limiter from config.

---

## Task 9: Service provider via Spatie tools

**Files:**
- Create: `src/FilamentRedeemCodesServiceProvider.php`

Uses `Spatie\LaravelPackageTools\PackageServiceProvider` for clean migration/config/route registration. Also registers the `redeem-codes` named rate limiter.

---

## Task 10: Filament Plugin + Resource

**Files:**
- Create: `src/FilamentRedeemCodesPlugin.php`
- Create: `src/Filament/Resources/RedeemCampaigns/RedeemCampaignResource.php`
- Create: `src/Filament/Resources/RedeemCampaigns/Pages/{List,Create,Edit}RedeemCampaign.php`
- Create: `src/Filament/Resources/RedeemCampaigns/Schemas/RedeemCampaignForm.php`
- Create: `src/Filament/Resources/RedeemCampaigns/Schemas/RedeemCampaignTable.php`

Form has:
- name, description, start_at, end_at
- Repeater for rewards (type select bound to host enum, amount, item_id, payload)
- Header action **Generate codes** prompting for `count`, `prefix`, `reusable`, dispatching `GenerateCodes`

Table shows: name, code count, redeemed count, active state, start/end. Rows expand to show codes.

Plugin class: `FilamentRedeemCodesPlugin::make()` registered into a panel via `$panel->plugin(FilamentRedeemCodesPlugin::make())`.

---

## Task 11: Tests + green run

- [ ] Run `composer install`
- [ ] Run `vendor/bin/pest`
- [ ] All tests green; commit.

---

## Task 12: README + initial commit

`README.md` covers: install, panel registration snippet, host-binding the `RewardType` enum, API contract, rate-limit config, migration from v1.

---

## Out of scope (explicit)

- Web UI outside Filament (the v1 standalone Bootstrap console)
- Per-user redemption tracking for **reusable** codes (still client-side)
- Spatie/laravel-permission integration (use filament-shield in host app)
- Code redemption webhook events (Laravel events fired only — host decides)
- Multi-tenancy (out of scope for v1; can be added later)
