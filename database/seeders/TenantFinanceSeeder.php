<?php

namespace Database\Seeders;

use App\Enums\ExpenseCategory;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\TenantExpense;
use App\Models\TenantFinanceProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantFinanceSeeder extends Seeder
{
    /**
     * Seed branch finance profiles, invoices, payments, and operating expenses.
     */
    public function run(): void
    {
        if (! $this->ensureFinanceTables()) {
            return;
        }

        $organization = Organization::query()->first();
        $manager = User::query()->where('worker_role', 'Manager')->orderBy('id')->first();

        $budgets = [
            'TUMO TIRANA' => ['budget' => 180_000, 'reserve' => 22_000],
            'ICT ECOSYSTEM' => ['budget' => 240_000, 'reserve' => 30_000],
            'ARTS' => ['budget' => 150_000, 'reserve' => 18_000],
        ];

        Tenant::query()->orderBy('id')->each(function (Tenant $tenant) use ($budgets, $organization, $manager): void {
            if (Invoice::query()->where('tenant_id', $tenant->id)->exists()) {
                return;
            }

            $config = $budgets[$tenant->title] ?? ['budget' => 120_000, 'reserve' => 15_000];
            $slug = Str::slug($tenant->title);

            TenantFinanceProfile::query()->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'annual_budget' => $config['budget'],
                    'operating_reserve' => $config['reserve'],
                    'currency' => 'EUR',
                    'notes' => 'Branch operating budget for '.$tenant->title.'.',
                ],
            );

            $invoices = [
                [
                    'reference' => strtoupper($slug).'-INV-001',
                    'title' => 'Corporate showcase booking',
                    'amount' => 4200,
                    'paid' => 4200,
                    'status' => InvoiceStatus::Paid,
                    'issued' => now()->subDays(45),
                    'due' => now()->subDays(15),
                ],
                [
                    'reference' => strtoupper($slug).'-INV-002',
                    'title' => 'Weekend workshop series',
                    'amount' => 2850,
                    'paid' => 1425,
                    'status' => InvoiceStatus::Partial,
                    'issued' => now()->subDays(20),
                    'due' => now()->addDays(10),
                ],
                [
                    'reference' => strtoupper($slug).'-INV-003',
                    'title' => 'Community open day',
                    'amount' => 1600,
                    'paid' => 0,
                    'status' => InvoiceStatus::Sent,
                    'issued' => now()->subDays(7),
                    'due' => now()->addDays(21),
                ],
                [
                    'reference' => strtoupper($slug).'-INV-004',
                    'title' => 'Partner exhibition rental',
                    'amount' => 5400,
                    'paid' => 0,
                    'status' => InvoiceStatus::Overdue,
                    'issued' => now()->subDays(40),
                    'due' => now()->subDays(5),
                ],
            ];

            foreach ($invoices as $data) {
                $invoice = Invoice::query()->create([
                    'tenant_id' => $tenant->id,
                    'organization_id' => $organization?->id,
                    'reference' => $data['reference'],
                    'title' => $data['title'],
                    'amount' => $data['amount'],
                    'amount_paid' => $data['paid'],
                    'status' => $data['status']->value,
                    'issued_at' => $data['issued'],
                    'due_at' => $data['due'],
                ]);

                if ($data['paid'] > 0) {
                    Payment::query()->create([
                        'invoice_id' => $invoice->id,
                        'amount' => $data['paid'],
                        'method' => PaymentMethod::BankTransfer,
                        'paid_at' => $data['issued']->copy()->addDays(5),
                        'recorded_by' => $manager?->id,
                        'notes' => 'Seeded demo payment.',
                    ]);
                }
            }

            $expenses = [
                [ExpenseCategory::Staffing, 'Freelance workshop facilitators', 980, 12],
                [ExpenseCategory::Utilities, 'Electricity and HVAC allocation', 640, 25],
                [ExpenseCategory::Maintenance, 'AV equipment servicing', 420, 40],
                [ExpenseCategory::Marketing, 'Branch event promotion', 310, 8],
            ];

            foreach ($expenses as [$category, $title, $amount, $daysAgo]) {
                TenantExpense::query()->create([
                    'tenant_id' => $tenant->id,
                    'category' => $category,
                    'title' => $title,
                    'amount' => $amount,
                    'incurred_at' => now()->subDays($daysAgo)->toDateString(),
                    'recorded_by' => $manager?->id,
                ]);
            }
        });

        $this->command?->info('Seeded tenant finance profiles, invoices, payments, and expenses.');
    }

    private function ensureFinanceTables(): bool
    {
        if (Schema::hasTable('tenant_finance_profiles')) {
            return true;
        }

        $this->command?->warn('Finance tables missing — running pending migrations...');
        $this->command?->call('migrate', ['--force' => true]);

        if (! Schema::hasTable('tenant_finance_profiles')) {
            $this->command?->error('Could not create finance tables. Run: php artisan migrate --force');

            return false;
        }

        return true;
    }
}
