<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\Network\NetworkActivationOrchestrator;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReconcileAndActivateRetriesCommand extends Command
{
    protected $signature = 'smartoh:reconcile-and-activate {--tenant= : Optional tenant ID} {--force : Force immediate retry of all pending/dead-letter jobs}';

    protected $description = 'Recover and execute queued/retrying network activation jobs and enforce subscriber expiry policies across tenants.';

    public function handle(NetworkActivationOrchestrator $orchestrator): int
    {
        $tenantId = $this->option('tenant') ? (int) $this->option('tenant') : null;
        $force = (bool) $this->option('force');

        // 1. Retry pending/retrying network activation jobs
        $res = $orchestrator->retryPendingJobs($tenantId, $force);
        $this->info("Activation Queue Processed: {$res['processed']} | Succeeded: {$res['succeeded']} | Still Retrying: {$res['still_retrying']}");

        // 2. Check expired subscriber cycles and mark expired
        $expiredQuery = Client::withoutGlobalScopes()
            ->where('status', 'active')
            ->whereNotNull('current_cycle_end')
            ->where('current_cycle_end', '<', Carbon::now());

        if ($tenantId) {
            $expiredQuery->where('tenant_id', $tenantId);
        }

        $expiredClients = $expiredQuery->get();
        foreach ($expiredClients as $client) {
            $client->update([
                'status'            => 'expired',
                'connection_status' => 'restricted',
            ]);
        }

        if ($expiredClients->count() > 0) {
            $this->info("Transitioned {$expiredClients->count()} expired subscriber(s) to restricted status.");
        }

        return self::SUCCESS;
    }
}
