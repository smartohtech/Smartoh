<?php

namespace App\Services\Billing;

use App\Models\BillingPlan;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingEngine
{
    /**
     * Generate recurring monthly invoice for client
     */
    public function generateInvoice(Client $client): Invoice
    {
        $plan = $client->billingPlan;
        $price = $plan ? $plan->price : 0.00;
        $site = $client->site;
        $prefix = $site?->invoice_prefix ?: 'INV';
        $nextNum = $site?->next_invoice_number ?: 1001;

        $invoiceNumber = sprintf('%s-%d', $prefix, $nextNum);
        if ($site) {
            $site->increment('next_invoice_number');
        }

        $tax = round($price * 0.16, 2); // 16% VAT
        $total = $price + $tax;

        $invoice = Invoice::create([
            'tenant_id' => $client->tenant_id,
            'site_id' => $client->site_id,
            'client_id' => $client->id,
            'billing_plan_id' => $plan?->id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
            'subtotal' => $price,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'paid_amount' => 0.00,
            'status' => 'unpaid',
            'notes' => "Monthly Internet Service Subscription - {$plan?->name}",
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => "Internet Access ({$plan?->formatted_speed}) - Period: " . now()->format('M Y'),
            'quantity' => 1,
            'unit_price' => $price,
            'total' => $price,
        ]);

        return $invoice;
    }

    /**
     * Calculate mid-cycle plan proration
     */
    public function calculateProration(BillingPlan $oldPlan, BillingPlan $newPlan, Client $client): array
    {
        $cycleStart = $client->current_cycle_start ?: now()->startOfMonth();
        $cycleEnd = $client->current_cycle_end ?: now()->endOfMonth();
        $totalDays = max(1, $cycleStart->diffInDays($cycleEnd));
        $remainingDays = max(0, now()->diffInDays($cycleEnd, false));

        $dailyOldRate = $oldPlan->price / $totalDays;
        $dailyNewRate = $newPlan->price / $totalDays;

        $unusedCredit = round($dailyOldRate * $remainingDays, 2);
        $newCharge = round($dailyNewRate * $remainingDays, 2);
        $proratedDifference = round($newCharge - $unusedCredit, 2);

        return [
            'total_days' => $totalDays,
            'remaining_days' => $remainingDays,
            'unused_credit' => $unusedCredit,
            'new_charge' => $newCharge,
            'net_payable' => max(0, $proratedDifference),
            'is_upgrade' => $newPlan->price > $oldPlan->price,
        ];
    }

    /**
     * Apply voucher to subscriber account
     */
    public function redeemVoucher(Client $client, string $code): array
    {
        return DB::transaction(function () use ($client, $code) {
            $voucher = Voucher::where('code', trim($code))->where('status', 'active')->lockForUpdate()->first();

            if (!$voucher) {
                return ['success' => false, 'message' => 'Invalid or already redeemed voucher code'];
            }

            $voucher->update([
                'status' => 'used',
                'used_by_client_id' => $client->id,
                'used_at' => now(),
            ]);

            // Add value to client or extend duration
            $validityDays = max(1, (int) round($voucher->duration_minutes / 1440));
            $client->update([
                'status' => 'active',
                'current_cycle_start' => now(),
                'current_cycle_end' => now()->addDays($validityDays),
                'wallet_balance' => $client->wallet_balance + $voucher->price,
            ]);

            Payment::create([
                'tenant_id' => $client->tenant_id,
                'site_id' => $client->site_id,
                'client_id' => $client->id,
                'transaction_reference' => 'VOUCHER-' . $voucher->code,
                'payment_method' => 'voucher',
                'amount' => $voucher->price,
                'currency' => 'KES',
                'status' => 'completed',
                'payer_name' => $client->name,
                'paid_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "Voucher redeemed successfully! Service active for {$validityDays} days.",
                'amount' => $voucher->price,
            ];
        });
    }
}
