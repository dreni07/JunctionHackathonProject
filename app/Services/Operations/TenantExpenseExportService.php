<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\TenantExpense;
use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantExpenseExportService
{
    public function __construct(private readonly TenantFinanceService $finance) {}

    public function download(User $manager): StreamedResponse
    {
        $context = $this->finance->exportContext($manager);
        $tenant = $context['tenant'];
        $currency = $context['currency'];
        $tenantId = (int) $tenant->id;

        $filename = sprintf(
            'expenses-%s-%s.csv',
            Str::slug($tenant->title),
            now()->format('Y-m-d'),
        );

        return response()->streamDownload(
            function () use ($tenantId, $tenant, $currency): void {
                $handle = fopen('php://output', 'w');

                if ($handle === false) {
                    return;
                }

                // Excel-friendly UTF-8 CSV.
                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv($handle, [
                    'Expense ID',
                    'Branch',
                    'Category',
                    'Category Label',
                    'Title',
                    'Amount',
                    'Currency',
                    'Incurred Date',
                    'Notes',
                    'Recorded By',
                    'Recorded By Email',
                    'Recorded At',
                ]);

                $total = 0.0;

                TenantExpense::query()
                    ->where('tenant_id', $tenantId)
                    ->with('recorder:id,name,email')
                    ->orderByDesc('incurred_at')
                    ->orderByDesc('created_at')
                    ->cursor()
                    ->each(function (TenantExpense $expense) use ($handle, $tenant, $currency, &$total): void {
                        $amount = (float) $expense->amount;
                        $total += $amount;

                        fputcsv($handle, [
                            $expense->id,
                            $tenant->title,
                            $expense->category->value,
                            $expense->category->label(),
                            $expense->title,
                            number_format($amount, 2, '.', ''),
                            $currency,
                            $expense->incurred_at?->toDateString(),
                            $expense->notes ?? '',
                            $expense->recorder?->name ?? '',
                            $expense->recorder?->email ?? '',
                            $expense->created_at?->toDateTimeString(),
                        ]);
                    });

                fputcsv($handle, [
                    '',
                    '',
                    '',
                    '',
                    'Total',
                    number_format($total, 2, '.', ''),
                    $currency,
                    '',
                    '',
                    '',
                    '',
                    '',
                ]);

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ],
        );
    }
}
