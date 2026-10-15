<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\IspApplication;
use App\Models\SaasInvoice;
use App\Models\SaasPayment;
use App\Models\SaasPlan;
use App\Models\SaasSubscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlatformDashboardController extends Controller
{
    public function index()
    {
        // 1. Tenant & Application Counts
        $totalIsps = Tenant::count();
        $pendingApplications = IspApplication::where('status', 'pending_approval')->count();
        $infoRequestedCount = IspApplication::where('status', 'info_requested')->count();
        $approvedIsps = Tenant::where('status', 'active')->count();
        $suspendedIsps = Tenant::where('status', 'suspended')->count();
        $rejectedApplications = IspApplication::where('status', 'rejected')->count();

        // 2. Subscription States
        $activeTrials = SaasSubscription::whereIn('status', [
            SaasSubscription::STATUS_TRIAL_ACTIVE,
            SaasSubscription::STATUS_TRIAL_EXPIRING,
        ])->count();

        $paidSubscriptions = SaasSubscription::where('status', SaasSubscription::STATUS_ACTIVE_PAID)->count();

        $expiredTrials = SaasSubscription::whereIn('status', [
            SaasSubscription::STATUS_TRIAL_EXPIRED,
            SaasSubscription::STATUS_PAYMENT_PENDING,
        ])->count();

        $gracePeriodCount = SaasSubscription::where('status', SaasSubscription::STATUS_GRACE_PERIOD)->count();

        $upcomingRenewals = SaasSubscription::where('status', SaasSubscription::STATUS_ACTIVE_PAID)
            ->where('current_period_end', '<=', Carbon::now()->addDays(7))
            ->where('current_period_end', '>=', Carbon::now())
            ->count();

        // 3. Financial Telemetry
        $totalRevenue = SaasPayment::where('status', 'completed')->sum('amount');
        $successfulPaymentsCount = SaasPayment::where('status', 'completed')->count();
        $pendingInvoicesSum = SaasInvoice::whereIn('status', ['pending', 'overdue'])->sum('total');
        $pendingInvoicesCount = SaasInvoice::whereIn('status', ['pending', 'overdue'])->count();

        // 4. Monthly Recurring Revenue (MRR) Estimate
        $mrr = SaasSubscription::where('status', SaasSubscription::STATUS_ACTIVE_PAID)->get()->sum(function ($sub) {
            return $sub->billing_interval === 'annually' ? ($sub->amount / 12) : $sub->amount;
        });

        // 5. Recent Activity & Applications
        $recentApplications = IspApplication::with('saasPlan')->latest()->take(6)->get();
        $recentSubscriptions = SaasSubscription::with(['tenant', 'plan'])->latest()->take(6)->get();
        $recentAuditLogs = AuditLog::with('user')->latest()->take(8)->get();
        $plans = SaasPlan::withCount('subscriptions')->get();

        return view('platform.dashboard', compact(
            'totalIsps',
            'pendingApplications',
            'infoRequestedCount',
            'approvedIsps',
            'suspendedIsps',
            'rejectedApplications',
            'activeTrials',
            'paidSubscriptions',
            'expiredTrials',
            'gracePeriodCount',
            'upcomingRenewals',
            'totalRevenue',
            'successfulPaymentsCount',
            'pendingInvoicesSum',
            'pendingInvoicesCount',
            'mrr',
            'recentApplications',
            'recentSubscriptions',
            'recentAuditLogs',
            'plans'
        ));
    }
}
