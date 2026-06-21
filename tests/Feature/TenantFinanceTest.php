<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TenantFinanceSeeder;
use Database\Seeders\TenantManagerSeeder;
use Database\Seeders\TenantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(TenantSeeder::class);
    $this->seed(TenantManagerSeeder::class);
    $this->seed(TenantFinanceSeeder::class);
});

test('tenant managers can load branch finance dashboard', function (): void {
    $manager = User::factory()->tenantManager()->create();
    $manager->syncRoles(RoleName::Operations);

    $this->actingAs($manager)
        ->getJson(route('operations.finance.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'profile',
                'summary' => [
                    'collected_revenue',
                    'outstanding',
                    'pending_quotes',
                    'expenses_ytd',
                    'annual_budget',
                    'budget_remaining',
                    'operating_reserve',
                    'net_position',
                ],
                'revenue_by_category',
                'invoices',
                'expenses',
                'recent_payments',
            ],
        ]);
});

test('tenant managers only see finance for their branch', function (): void {
    $tumo = Tenant::query()->where('title', 'TUMO TIRANA')->firstOrFail();
    $manager = User::factory()->tenantManager()->create(['tenant_id' => $tumo->id]);
    $manager->syncRoles(RoleName::Operations);

    $response = $this->actingAs($manager)
        ->getJson(route('operations.finance.index'))
        ->assertOk();

    $invoiceIds = collect($response->json('data.invoices'))->pluck('id');

    expect($invoiceIds)->not->toBeEmpty();
    expect(
        Invoice::query()->whereIn('id', $invoiceIds)->where('tenant_id', $tumo->id)->count(),
    )->toBe($invoiceIds->count());
});

test('tenant managers can record invoice payments', function (): void {
    $manager = User::factory()->tenantManager()->create();
    $manager->syncRoles(RoleName::Operations);

    $invoice = Invoice::query()
        ->where('tenant_id', $manager->tenant_id)
        ->where('status', 'sent')
        ->firstOrFail();

    $this->actingAs($manager)
        ->postJson(route('operations.finance.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 500,
            'method' => 'bank_transfer',
        ])
        ->assertCreated();

    $invoice->refresh();

    expect((float) $invoice->amount_paid)->toBe(500.0);
    expect($invoice->status->value)->toBe('partial');
    expect(Payment::query()->where('invoice_id', $invoice->id)->count())->toBe(1);
});

test('non managers cannot access finance endpoints', function (): void {
    $worker = User::factory()->operational()->create();
    $worker->syncRoles(RoleName::Operations);

    $this->actingAs($worker)
        ->getJson(route('operations.finance.index'))
        ->assertForbidden();

    $this->actingAs($worker)
        ->get(route('operations.finance.expenses.export'))
        ->assertRedirect(route('operations.home'));
});

test('tenant managers can export branch expenses as a spreadsheet csv', function (): void {
    $manager = User::factory()->tenantManager()->create();
    $manager->syncRoles(RoleName::Operations);

    $response = $this->actingAs($manager)
        ->get(route('operations.finance.expenses.export'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    expect($content)->toContain('Expense ID')
        ->and($content)->toContain('Category Label')
        ->and($content)->toContain('Total');
});

test('team dashboard includes branch workforce stats', function (): void {
    $manager = User::factory()->tenantManager()->create();
    $manager->syncRoles(RoleName::Operations);

    $this->actingAs($manager)
        ->getJson(route('operations.team.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'workers',
                'assignable_roles',
                'stats' => [
                    'total_workers',
                    'active_tasks',
                    'tasks_due_this_week',
                    'roles',
                ],
            ],
        ]);
});
