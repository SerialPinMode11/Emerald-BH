<?php

namespace App\Services;

use App\Models\RentalAgreement;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RentPaymentScheduleService
{
    /**
     * Ensure one rent transaction exists per billing month for the agreement term.
     */
    public function sync(RentalAgreement $agreement): void
    {
        if (! $agreement->start_date || ! $agreement->end_date) {
            return;
        }

        $cursor = $agreement->start_date->copy()->startOfMonth();
        $end = $agreement->end_date->copy()->startOfMonth();
        $maxMonths = 120;

        while ($cursor <= $end && $maxMonths-- > 0) {
            Transaction::query()->firstOrCreate(
                [
                    'rental_agreement_id' => $agreement->id,
                    'type' => 'rent',
                    'billing_month' => $cursor->toDateString(),
                ],
                [
                    'amount' => $agreement->total_rent,
                    'status' => 'pending',
                    'payment_method' => 'bank_transfer',
                ],
            );

            $cursor = $cursor->copy()->addMonth();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function build(RentalAgreement $agreement): array
    {
        $this->sync($agreement);

        $transactions = $agreement->transactions()
            ->where('type', 'rent')
            ->orderBy('billing_month')
            ->get();

        if (! $agreement->start_date || ! $agreement->end_date) {
            return $transactions->map(fn (Transaction $t) => $this->formatRow($t))->all();
        }

        $byMonth = $transactions->keyBy(
            fn (Transaction $t) => $t->billing_month?->format('Y-m') ?? $t->created_at->format('Y-m'),
        );

        $schedule = [];
        $cursor = $agreement->start_date->copy()->startOfMonth();
        $end = $agreement->end_date->copy()->startOfMonth();
        $today = now()->startOfMonth();
        $maxMonths = 120;

        while ($cursor <= $end && $maxMonths-- > 0) {
            $key = $cursor->format('Y-m');
            $transaction = $byMonth->get($key);

            $status = $transaction?->status ?? 'pending';
            $isPaid = in_array($status, ['completed', 'paid'], true);
            $isOverdue = ! $isPaid && $cursor->lt($today);

            $schedule[] = [
                'id' => $transaction?->id,
                'period' => $cursor->format('F Y'),
                'billing_month' => $cursor->toDateString(),
                'amount' => (float) ($transaction?->amount ?? $agreement->total_rent),
                'status' => $status,
                'status_label' => $isPaid ? 'Paid' : ($isOverdue ? 'Overdue' : 'Pending'),
                'paid_at' => $transaction?->paid_at?->format('M d, Y'),
                'is_overdue' => $isOverdue,
                'can_mark_paid' => ! $isPaid && in_array($status, ['pending', 'disputed', 'failed'], true),
            ];

            $cursor = $cursor->copy()->addMonth();
        }

        return $schedule;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRow(Transaction $transaction): array
    {
        $isPaid = in_array($transaction->status, ['completed', 'paid'], true);

        return [
            'id' => $transaction->id,
            'period' => $transaction->billing_month?->format('F Y') ?? $transaction->created_at->format('F Y'),
            'billing_month' => $transaction->billing_month?->toDateString(),
            'amount' => (float) $transaction->amount,
            'status' => $transaction->status,
            'status_label' => $isPaid ? 'Paid' : 'Pending',
            'paid_at' => $transaction->paid_at?->format('M d, Y'),
            'is_overdue' => false,
            'can_mark_paid' => ! $isPaid,
        ];
    }

    /**
     * @return array{total_due: float, total_paid: float, months_paid: int, months_total: int, months_overdue: int}
     */
    public function summary(Collection|array $schedule): array
    {
        $rows = collect($schedule);

        return [
            'total_due' => (float) $rows->sum('amount'),
            'total_paid' => (float) $rows->whereIn('status', ['completed', 'paid'])->sum('amount'),
            'months_paid' => $rows->whereIn('status', ['completed', 'paid'])->count(),
            'months_total' => $rows->count(),
            'months_overdue' => $rows->where('is_overdue', true)->count(),
        ];
    }
}
