<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SaasInvoice;
use App\Models\SaasPayment;
use App\Services\SaaS\SubscriptionLifecycleService;
use Illuminate\Http\Request;

class PlatformInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = SaasInvoice::with(['tenant', 'subscription.plan'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function ($t) use ($search) {
                      $t->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(15)->withQueryString();
        $totalPaid = SaasInvoice::where('status', 'paid')->sum('total');
        $totalPending = SaasInvoice::whereIn('status', ['pending', 'overdue'])->sum('total');

        return view('platform.invoices.index', compact('invoices', 'totalPaid', 'totalPending'));
    }

    public function show(SaasInvoice $invoice)
    {
        $invoice->load(['tenant', 'subscription.plan', 'items', 'payments.recordedBy']);

        return view('platform.invoices.show', compact('invoice'));
    }

    public function recordPayment(Request $request, SaasInvoice $invoice, SubscriptionLifecycleService $lifecycle)
    {
        $validated = $request->validate([
            'amount'            => 'required|numeric|min:1',
            'payment_reference' => 'required|string|max:100|unique:saas_payments,payment_reference',
            'provider'          => 'required|in:mpesa,bank_transfer,stripe,manual,cash',
            'notes'             => 'nullable|string|max:500',
        ]);

        $subscription = $invoice->subscription;
        if (!$subscription) {
            return back()->with('error', 'Cannot record payment: This invoice is not attached to an active subscription.');
        }

        $lifecycle->recordPaymentAndActivate(
            $subscription,
            (float)$validated['amount'],
            $validated['payment_reference'],
            $validated['provider'],
            $request->user(),
            $validated['notes']
        );

        return back()->with('success', "Payment of {$invoice->currency} " . number_format($validated['amount'], 2) . " verified and recorded! Subscription for {$invoice->tenant->name} has been upgraded to ACTIVE_PAID.");
    }
}
