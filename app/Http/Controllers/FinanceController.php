<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\FinanceTransaction;
use App\Models\InterSiteTransfer;
use App\Models\Site;
use App\Services\Accounting\FinanceService;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(FinanceService $finance)
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : null;
        $sites = Site::all();

        $accounts = ChartOfAccount::orderBy('code')->get();
        $transactions = FinanceTransaction::with(['debitAccount', 'creditAccount'])->latest('transaction_date')->paginate(15);
        $transfers = InterSiteTransfer::with(['fromSite', 'toSite'])->latest()->get();

        $pnl = $finance->getProfitAndLoss($tenantId);

        return view('finance.index', compact('accounts', 'transactions', 'transfers', 'sites', 'pnl'));
    }

    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'site_id' => 'nullable|exists:sites,id',
            'current_balance' => 'nullable|numeric',
        ]);

        ChartOfAccount::create($validated);
        return back()->with('success', "Account {$validated['code']} - {$validated['name']} registered.");
    }
}
