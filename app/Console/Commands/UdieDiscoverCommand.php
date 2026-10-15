<?php

namespace App\Console\Commands;

use App\Jobs\RunDeviceDiscoveryJob;
use App\Models\DiscoveryJob;
use App\Models\Tenant;
use Illuminate\Console\Command;

class UdieDiscoverCommand extends Command
{
    protected $signature = 'udie:discover {--tenant= : Tenant ID to run discovery for} {--source=all : Discovery source collector}';
    protected $description = 'Trigger Smartoh Universal Device Intelligence & Discovery Engine (UDIE) sweep';

    public function handle(): int
    {
        $tenantId = $this->option('tenant') ?: (Tenant::first()?->id ?? 1);
        $source = $this->option('source') ?: 'all';

        $this->info("Initiating UDIE Discovery sweep for Tenant #{$tenantId} (Source: {$source})...");

        $job = DiscoveryJob::create([
            'tenant_id' => $tenantId,
            'source' => $source,
            'target_scope' => 'All enrolled MikroTik, OLT, and Network Segments',
            'status' => 'pending',
        ]);

        RunDeviceDiscoveryJob::dispatchSync($job->id);

        $job->refresh();
        $this->info("UDIE Discovery complete. Discovered: {$job->devices_discovered_count}, Identified: {$job->devices_identified_count}");

        return Command::SUCCESS;
    }
}
