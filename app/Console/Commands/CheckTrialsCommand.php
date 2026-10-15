<?php

namespace App\Console\Commands;

use App\Services\SaaS\SubscriptionLifecycleService;
use Illuminate\Console\Command;

class CheckTrialsCommand extends Command
{
    protected $signature = 'saas:check-trials';
    protected $description = 'Scan and process SaaS subscription trials, send day 5/6 reminders, transition expired trials and grace periods';

    public function handle(SubscriptionLifecycleService $lifecycleService): int
    {
        $this->info('Starting SaaS trial and subscription lifecycle verification...');

        $results = $lifecycleService->checkAndTransitionTrials();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Day 5 Reminders Dispatched', $results['reminders_day_5']],
                ['Day 6 Urgent Reminders Dispatched', $results['reminders_day_6']],
                ['Entered 48h Grace Period', $results['grace_period']],
                ['Fully Expired & Restricted', $results['expired']],
            ]
        );

        $this->info('Trial lifecycle scan completed.');
        return self::SUCCESS;
    }
}
