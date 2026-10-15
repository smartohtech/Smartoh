<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\FinanceTransaction;
use App\Models\Payment;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceService
{
    /**
     * Record payment into General Ledger (Debit Cash/Bank, Credit Revenue)
     */
    public function recordPayment(Payment $payment): FinanceTransaction
    {
        $tenantId = $payment->tenant_id;
        $siteId = $payment->site_id;

        // Find or create default Cash/Bank Account and Revenue Account
        $bankAccount = ChartOfAccount::firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => '1010'],
            ['name' => 'Bank & M-Pesa Collections', 'type' => 'asset', 'site_id' => $siteId]
        );

        $revenueAccount = ChartOfAccount::firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => '4010'],
            ['name' => 'Internet Subscription Revenue', 'type' => 'revenue', 'site_id' => $siteId]
        );

        $tx = FinanceTransaction::create([
            'tenant_id' => $tenantId,
            'site_id' => $siteId,
            'transaction_number' => 'TX-' . strtoupper(Str::random(8)),
            'transaction_date' => now(),
            'debit_account_id' => $bankAccount->id,
            'credit_account_id' => $revenueAccount->id,
            'amount' => $payment->amount,
            'reference' => $payment->transaction_reference,
            'description' => "Payment from {$payment->payer_name} via {$payment->payment_method}",
            'is_posted' => true,
        ]);

        $bankAccount->increment('current_balance', $payment->amount);
        $revenueAccount->increment('current_balance', $payment->amount);

        return $tx;
    }

    /**
     * Generate Profit & Loss statement for tenant or site
     */
    public function getProfitAndLoss(?int $tenantId = null, ?int $siteId = null): array
    {
        $query = ChartOfAccount::query();
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        if ($siteId) {
            $query->where('site_id', $siteId);
        }

        $revenues = (clone $query)->where('type', 'revenue')->get();
        $expenses = (clone $query)->where('type', 'expense')->get();

        $totalRevenue = $revenues->sum('current_balance');
        $totalExpense = $expenses->sum('current_balance');
        $netProfit = $totalRevenue - $totalExpense;

        return [
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_profit' => $netProfit,
            'margin_pct' => $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0.0,
            'revenue_breakdown' => $revenues,
            'expense_breakdown' => $expenses,
        ];
    }
}
