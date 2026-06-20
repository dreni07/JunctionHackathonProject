<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Enums\AccountType;
use App\Enums\ExpenseCategory;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\ProposalStatus;
use App\Enums\TaskState;
use App\Models\FinalProposal;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\QuotationLineItem;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\TenantExpense;
use App\Models\TenantFinanceProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class TenantFinanceService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(User $manager): array
    {
        $this->assertManager($manager);

        $tenantId = (int) $manager->tenant_id;
        $profile = $this->profileForTenant($tenantId);

        return [
            'profile' => $this->serializeProfile($profile),
            'summary' => $this->buildSummary($tenantId, $profile),
            'revenue_by_category' => $this->revenueByCategory($tenantId),
            'invoices' => $this->listInvoices($tenantId),
            'expenses' => $this->listExpenses($tenantId),
            'recent_payments' => $this->recentPayments($tenantId),
        ];
    }

    /**
     * @param  array{annual_budget: float|int|string, operating_reserve?: float|int|string|null, notes?: string|null}  $data
     */
    public function updateProfile(User $manager, array $data): TenantFinanceProfile
    {
        $this->assertManager($manager);

        $profile = $this->profileForTenant((int) $manager->tenant_id);
        $profile->update([
            'annual_budget' => $data['annual_budget'],
            'operating_reserve' => $data['operating_reserve'] ?? $profile->operating_reserve,
            'notes' => $data['notes'] ?? $profile->notes,
        ]);

        return $profile->refresh();
    }

    /**
     * @param  array{invoice_id: string, amount: float|int|string, method: string, paid_at?: string|null, notes?: string|null}  $data
     */
    public function recordPayment(User $manager, array $data): Payment
    {
        $this->assertManager($manager);

        $invoice = Invoice::query()
            ->where('tenant_id', $manager->tenant_id)
            ->findOrFail($data['invoice_id']);

        if (in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Void], true)) {
            throw new RuntimeException('This invoice cannot accept further payments.');
        }

        $amount = round((float) $data['amount'], 2);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        if ($amount > $invoice->balanceDue() + 0.001) {
            throw new InvalidArgumentException('Payment exceeds the outstanding balance.');
        }

        return DB::transaction(function () use ($manager, $invoice, $data, $amount): Payment {
            $payment = Payment::query()->create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => PaymentMethod::from((string) $data['method']),
                'paid_at' => isset($data['paid_at']) ? Carbon::parse((string) $data['paid_at']) : now(),
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $manager->id,
            ]);

            $invoice->amount_paid = round((float) $invoice->amount_paid + $amount, 2);
            $invoice->status = $invoice->balanceDue() <= 0.001
                ? InvoiceStatus::Paid
                : InvoiceStatus::Partial;
            $invoice->save();

            return $payment->load('invoice:id,reference,title');
        });
    }

    /**
     * @param  array{category: string, title: string, amount: float|int|string, incurred_at: string, notes?: string|null}  $data
     */
    public function recordExpense(User $manager, array $data): TenantExpense
    {
        $this->assertManager($manager);

        return TenantExpense::query()->create([
            'tenant_id' => $manager->tenant_id,
            'category' => ExpenseCategory::from((string) $data['category']),
            'title' => $data['title'],
            'amount' => round((float) $data['amount'], 2),
            'incurred_at' => Carbon::parse((string) $data['incurred_at'])->toDateString(),
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $manager->id,
        ]);
    }

    /**
     * @return array{tenant: Tenant, currency: string}
     */
    public function exportContext(User $manager): array
    {
        $this->assertManager($manager);

        $tenantId = (int) $manager->tenant_id;
        $tenant = Tenant::query()->findOrFail($tenantId);
        $profile = $this->profileForTenant($tenantId);

        return [
            'tenant' => $tenant,
            'currency' => $profile->currency,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeProfile(TenantFinanceProfile $profile): array
    {
        return [
            'annual_budget' => (float) $profile->annual_budget,
            'operating_reserve' => (float) $profile->operating_reserve,
            'currency' => $profile->currency,
            'notes' => $profile->notes,
        ];
    }

    /**
     * @return array<string, float|int>
     */
    private function buildSummary(int $tenantId, TenantFinanceProfile $profile): array
    {
        $collectedRevenue = (float) Payment::query()
            ->whereHas('invoice', fn ($query) => $query->where('tenant_id', $tenantId))
            ->sum('amount');

        $outstanding = (float) Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [
                InvoiceStatus::Sent->value,
                InvoiceStatus::Partial->value,
                InvoiceStatus::Overdue->value,
            ])
            ->selectRaw('SUM(amount - amount_paid) as balance')
            ->value('balance');

        $pendingQuotes = (float) FinalProposal::query()
            ->whereIn('status', [ProposalStatus::Draft->value, ProposalStatus::Sent->value])
            ->whereHas('proposedSpace', fn ($query) => $query->where('tenant_id', $tenantId))
            ->sum('proposed_price');

        $expensesYtd = (float) TenantExpense::query()
            ->where('tenant_id', $tenantId)
            ->whereYear('incurred_at', now()->year)
            ->sum('amount');

        $annualBudget = (float) $profile->annual_budget;
        $netPosition = $collectedRevenue - $expensesYtd;

        return [
            'collected_revenue' => round($collectedRevenue, 2),
            'outstanding' => round($outstanding, 2),
            'pending_quotes' => round($pendingQuotes, 2),
            'expenses_ytd' => round($expensesYtd, 2),
            'annual_budget' => round($annualBudget, 2),
            'budget_remaining' => round(max(0, $annualBudget - $expensesYtd), 2),
            'operating_reserve' => round((float) $profile->operating_reserve, 2),
            'net_position' => round($netPosition, 2),
        ];
    }

    /**
     * @return list<array{category: string, label: string, total: float}>
     */
    private function revenueByCategory(int $tenantId): array
    {
        $rows = QuotationLineItem::query()
            ->select('quotation_line_items.category', DB::raw('SUM(quotation_line_items.total) as total'))
            ->join('final_proposals', 'final_proposals.id', '=', 'quotation_line_items.final_proposal_id')
            ->join('spaces', 'spaces.id', '=', 'final_proposals.proposed_space_id')
            ->where('spaces.tenant_id', $tenantId)
            ->whereIn('final_proposals.status', [
                ProposalStatus::Sent->value,
                ProposalStatus::Accepted->value,
            ])
            ->groupBy('quotation_line_items.category')
            ->get();

        return $rows->map(fn ($row): array => [
            'category' => (string) $row->category,
            'label' => ucfirst((string) $row->category),
            'total' => round((float) $row->total, 2),
        ])->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function listInvoices(int $tenantId): array
    {
        return Invoice::query()
            ->where('tenant_id', $tenantId)
            ->with('organization:id,name')
            ->orderByDesc('issued_at')
            ->limit(25)
            ->get()
            ->map(fn (Invoice $invoice): array => $this->serializeInvoice($invoice))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function listExpenses(int $tenantId): array
    {
        return TenantExpense::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('incurred_at')
            ->limit(20)
            ->get()
            ->map(fn (TenantExpense $expense): array => $this->serializeExpense($expense))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentPayments(int $tenantId): array
    {
        return Payment::query()
            ->whereHas('invoice', fn ($query) => $query->where('tenant_id', $tenantId))
            ->with('invoice:id,reference,title')
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get()
            ->map(fn (Payment $payment): array => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'method' => $payment->method->value,
                'method_label' => $payment->method->label(),
                'paid_at' => $payment->paid_at?->toIso8601String(),
                'invoice' => [
                    'reference' => $payment->invoice?->reference,
                    'title' => $payment->invoice?->title,
                ],
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeInvoice(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'reference' => $invoice->reference,
            'title' => $invoice->title,
            'amount' => (float) $invoice->amount,
            'amount_paid' => (float) $invoice->amount_paid,
            'balance_due' => round($invoice->balanceDue(), 2),
            'status' => $invoice->status->value,
            'status_label' => $invoice->status->label(),
            'issued_at' => $invoice->issued_at?->toIso8601String(),
            'due_at' => $invoice->due_at?->toIso8601String(),
            'organization' => $invoice->organization?->only(['id', 'name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeExpense(TenantExpense $expense): array
    {
        return [
            'id' => $expense->id,
            'category' => $expense->category->value,
            'category_label' => $expense->category->label(),
            'title' => $expense->title,
            'amount' => (float) $expense->amount,
            'incurred_at' => $expense->incurred_at?->toDateString(),
            'notes' => $expense->notes,
        ];
    }

    private function profileForTenant(int $tenantId): TenantFinanceProfile
    {
        return TenantFinanceProfile::query()->firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'annual_budget' => 120_000,
                'operating_reserve' => 15_000,
                'currency' => 'EUR',
            ],
        );
    }

    /**
     * Team stats for the manager dashboard.
     *
     * @return array<string, mixed>
     */
    public function teamStats(User $manager): array
    {
        $this->assertManager($manager);

        $tenantId = (int) $manager->tenant_id;

        $workers = User::query()
            ->where('tenant_id', $tenantId)
            ->where('account_type', AccountType::Operational->value)
            ->get(['id', 'worker_role']);

        $roleBreakdown = $workers
            ->groupBy(fn (User $worker): string => (string) ($worker->worker_role ?? 'Unassigned'))
            ->map(fn (Collection $group): int => $group->count())
            ->sortDesc()
            ->all();

        $activeTasks = Task::query()
            ->whereIn('user_id', $workers->pluck('id'))
            ->whereNotIn('state', [TaskState::Finished->value, TaskState::Cancelled->value])
            ->count();

        $tasksDueThisWeek = Task::query()
            ->whereIn('user_id', $workers->pluck('id'))
            ->whereBetween('due_at', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
            ->count();

        return [
            'total_workers' => $workers->count(),
            'active_tasks' => $activeTasks,
            'tasks_due_this_week' => $tasksDueThisWeek,
            'roles' => $roleBreakdown,
        ];
    }

    private function assertManager(User $manager): void
    {
        if (! $manager->isTenantManager()) {
            throw new InvalidArgumentException('Only tenant managers can access branch finance.');
        }
    }
}
