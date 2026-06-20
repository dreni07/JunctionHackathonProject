<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorkforceSeeder extends Seeder
{
    private const USERS_PER_TENANT = 50;

    /** @var list<string> */
    private const FIRST_NAMES = [
        'Arben', 'Besa', 'Drita', 'Endrit', 'Flutura', 'Gentian', 'Hana', 'Ilir',
        'Jeta', 'Kled', 'Lira', 'Marsel', 'Nora', 'Olta', 'Petrit', 'Rina',
        'Skender', 'Teuta', 'Valon', 'Yllka', 'Admir', 'Blerta', 'Ceni', 'Dorina',
        'Erion', 'Fiona', 'Gzim', 'Helena', 'Ismail', 'Jonida', 'Kejsi', 'Luan',
    ];

    /** @var list<string> */
    private const LAST_NAMES = [
        'Hoxha', 'Krasniqi', 'Berisha', 'Shala', 'Gjoni', 'Leka', 'Dervishi',
        'Curri', 'Bardhi', 'Kola', 'Zeqiri', 'Mara', 'Prifti', 'Ndoja', 'Bajrami',
        'Selimi', 'Morina', 'Rexhepi', 'Thaçi', 'Veseli', 'Gashi', 'Hasani', 'Jashari',
    ];

    /**
     * Seed {@see self::USERS_PER_TENANT} operational workers for every tenant.
     * Professions are picked at random from each tenant's role catalog, every
     * account is email-verified, and every password is "password".
     */
    public function run(): void
    {
        $tenants = Tenant::query()->orderBy('id')->get();

        if ($tenants->isEmpty()) {
            $this->call(TenantSeeder::class);
            $tenants = Tenant::query()->orderBy('id')->get();
        }

        $this->clearPreviousBulkWorkforce();

        $seeded = 0;

        foreach ($tenants as $tenant) {
            /** @var list<string> $roles */
            $roles = array_values($tenant->roles ?? []);

            if ($roles === []) {
                continue;
            }

            for ($index = 1; $index <= self::USERS_PER_TENANT; $index++) {
                $user = User::create([
                    'name' => $this->workerName($tenant->id, $index),
                    'email' => sprintf(
                        '%s-worker-%03d@pyramid.test',
                        Str::slug($tenant->title),
                        $index,
                    ),
                    'password' => 'password',
                    'account_type' => AccountType::Operational->value,
                    'tenant_id' => $tenant->id,
                    'worker_role' => $roles[array_rand($roles)],
                    'email_verified_at' => now(),
                ]);

                $user->assignRole(RoleName::Operations);
                $seeded++;
            }
        }

        $this->command?->info(sprintf(
            'Seeded %d operational workers (%d per tenant) across %d tenants.',
            $seeded,
            self::USERS_PER_TENANT,
            $tenants->count(),
        ));
    }

    /**
     * Drop bulk workforce rows so a re-run does not collide on unique emails.
     * Keeps the one demo worker per tenant created by {@see TenantSeeder}.
     */
    private function clearPreviousBulkWorkforce(): void
    {
        User::query()
            ->where('account_type', AccountType::Operational->value)
            ->where('email', 'like', '%-worker-%@pyramid.test')
            ->delete();
    }

    /**
     * A varied display name derived from tenant and index (deterministic names,
     * random professions handled separately).
     */
    private function workerName(int $tenantId, int $index): string
    {
        $offset = ($tenantId * self::USERS_PER_TENANT) + $index;
        $first = self::FIRST_NAMES[$offset % count(self::FIRST_NAMES)];
        $last = self::LAST_NAMES[($offset * 5 + 3) % count(self::LAST_NAMES)];

        return $first.' '.$last;
    }
}
