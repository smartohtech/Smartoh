<?php

namespace App\Services\Network;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\MikroTikDevice;
use App\Models\NetworkActivationJob;
use App\Models\NOCIncident;
use App\Models\Payment;
use App\Models\SystemNotification;
use App\Services\MikroTik\MikroTikService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NetworkActivationOrchestrator
{
    public function __construct(
        protected MikroTikService $mikrotik
    ) {}

    /**
     * Immediately enqueue and attempt network activation for a subscriber after verified payment.
     * Never imposes an artificial waiting period.
     */
    public function enqueueAndExecuteActivation(Client $client, ?Payment $payment = null, string $action = 'activate'): NetworkActivationJob
    {
        // Resolve tenant router (without global scope issues in background/webhook contexts)
        $router = null;
        if ($client->mikrotik_device_id) {
            $router = MikroTikDevice::withoutGlobalScopes()
                ->where('tenant_id', $client->tenant_id)
                ->find($client->mikrotik_device_id);
        }

        if (!$router && $client->site_id) {
            $router = MikroTikDevice::withoutGlobalScopes()
                ->where('tenant_id', $client->tenant_id)
                ->where('site_id', $client->site_id)
                ->first();
        }

        if (!$router) {
            $router = MikroTikDevice::withoutGlobalScopes()
                ->where('tenant_id', $client->tenant_id)
                ->first();
        }

        $job = NetworkActivationJob::withoutGlobalScopes()->create([
            'tenant_id'          => $client->tenant_id,
            'client_id'          => $client->id,
            'payment_id'         => $payment?->id,
            'mikrotik_device_id' => $router?->id,
            'service_type'       => $client->service_type ?: 'pppoe',
            'action'             => $action,
            'status'             => NetworkActivationJob::STATUS_QUEUED,
            'attempts'           => 0,
            'max_attempts'       => 5,
            'command_payload'    => [
                'account_number' => $client->account_number,
                'service_type'   => $client->service_type ?: 'pppoe',
                'plan_id'        => $client->billing_plan_id,
                'action'         => $action,
            ],
        ]);

        if ($payment) {
            $payment->update(['activation_status' => 'queued']);
        }
        $client->update(['connection_status' => 'activation_queued']);

        // Execute immediately without any artificial delay
        return $this->attemptJobExecution($job);
    }

    /**
     * Execute or retry a NetworkActivationJob with exponential backoff and dead-letter NOC alerting.
     */
    public function attemptJobExecution(NetworkActivationJob $job): NetworkActivationJob
    {
        $client = Client::withoutGlobalScopes()->find($job->client_id);
        $payment = $job->payment_id ? Payment::withoutGlobalScopes()->find($job->payment_id) : null;
        $router = $job->mikrotik_device_id
            ? MikroTikDevice::withoutGlobalScopes()->find($job->mikrotik_device_id)
            : MikroTikDevice::withoutGlobalScopes()->where('tenant_id', $job->tenant_id)->first();

        $job->increment('attempts');
        $attempt = $job->attempts;

        // If no router is configured for the tenant yet, provision a default logical NAS or mark applied
        if (!$router) {
            $router = MikroTikDevice::withoutGlobalScopes()->create([
                'tenant_id'              => $job->tenant_id,
                'name'                   => 'Core-NAS-Router-01',
                'ip_address'             => '10.0.0.1',
                'api_port'               => 8728,
                'api_username'           => 'admin',
                'api_password_encrypted' => encrypt('admin'),
                'model'                  => 'CCR2004-16G-2S+',
                'routeros_version'       => 'v7.16.2',
                'is_online'              => true,
            ]);
            $job->update(['mikrotik_device_id' => $router->id]);
        }

        if ($job->action === 'restrict') {
            $result = $this->mikrotik->restrictSubscriberService($router, $client, 'payment_reversed_or_overdue');
            if ($result['success']) {
                $job->update([
                    'status'          => NetworkActivationJob::STATUS_APPLIED,
                    'executed_at'     => Carbon::now(),
                    'router_response' => $result,
                    'last_error'      => null,
                ]);
                $client->update([
                    'status'                => 'suspended',
                    'connection_status'     => 'restricted',
                    'service_suspended_at'  => Carbon::now(),
                ]);
                return $job->fresh();
            }
        }

        // Standard activation
        $result = $this->mikrotik->activateSubscriberService($router, $client, $client->billingPlan);

        if ($result['success']) {
            $newStatus = !empty($result['verified'])
                ? NetworkActivationJob::STATUS_CONNECTED_VERIFIED
                : NetworkActivationJob::STATUS_APPLIED;

            $job->update([
                'status'             => $newStatus,
                'executed_at'        => Carbon::now(),
                'verified_online_at' => !empty($result['verified']) ? Carbon::now() : null,
                'router_response'    => $result,
                'last_error'         => null,
            ]);

            if ($payment) {
                $payment->update([
                    'activation_status' => !empty($result['verified']) ? 'connected_verified' : 'applied',
                ]);
            }

            $client->update([
                'status'                => 'active',
                'connection_status'     => !empty($result['verified']) ? 'online' : 'activation_applied',
                'service_suspended_at'  => null,
                'last_activated_at'     => Carbon::now(),
            ]);

            // Send notification record
            SystemNotification::withoutGlobalScopes()->create([
                'tenant_id'  => $client->tenant_id,
                'title'      => "Service Activated: {$client->account_number}",
                'message'    => "Subscriber {$client->name} ({$client->service_type}) activated on {$router->name} with rate {$result['rate_limit']}.",
                'type'       => 'success',
                'action_url' => "/clients/{$client->id}",
            ]);

            AuditLog::create([
                'tenant_id'      => $client->tenant_id,
                'event'          => 'network.service_activated',
                'auditable_type' => Client::class,
                'auditable_id'   => $client->id,
                'new_values'     => [
                    'job_id'       => $job->id,
                    'payment_id'   => $payment?->id,
                    'service_type' => $client->service_type,
                    'router'       => $router->name,
                    'status'       => $newStatus,
                ],
                'ip_address'     => request()?->ip() ?? '127.0.0.1',
            ]);

            return $job->fresh();
        }

        // Router or network was unavailable -> preserve payment, schedule exponential backoff or dead-letter
        $errorMsg = $result['error'] ?? 'Router API timeout / unreachable';
        $backoffSeconds = (int) min(3600, pow(2, $attempt) * 15); // 30s, 60s, 120s, 240s, 480s

        if ($attempt >= $job->max_attempts) {
            $job->update([
                'status'        => NetworkActivationJob::STATUS_FAILED_DEAD_LETTER,
                'last_error'    => $errorMsg,
                'next_retry_at' => null,
                'noc_alerted'   => true,
            ]);

            if ($payment) {
                $payment->update(['activation_status' => 'failed']);
            }
            $client->update(['connection_status' => 'activation_retrying']);

            // Raise NOC incident for dead-letter activation failure
            NOCIncident::withoutGlobalScopes()->create([
                'tenant_id'          => $client->tenant_id,
                'incident_number'    => 'INC-ACT-' . strtoupper(Str::random(5)),
                'title'              => "Activation Retries Exhausted for {$client->account_number}",
                'affected_component' => $router->name . " ({$router->ip_address})",
                'severity'           => 'critical',
                'status'             => 'open',
                'description'        => "Verified payment ({$payment?->transaction_reference}) received for {$client->name}, but router {$router->name} failed after {$attempt} activation attempts: {$errorMsg}",
                'started_at'         => Carbon::now(),
            ]);

            AuditLog::create([
                'tenant_id'      => $client->tenant_id,
                'event'          => 'network.activation_dead_letter',
                'auditable_type' => NetworkActivationJob::class,
                'auditable_id'   => $job->id,
                'new_values'     => ['attempts' => $attempt, 'error' => $errorMsg],
                'ip_address'     => request()?->ip() ?? '127.0.0.1',
            ]);
        } else {
            $job->update([
                'status'        => NetworkActivationJob::STATUS_RETRYING,
                'last_error'    => $errorMsg,
                'next_retry_at' => Carbon::now()->addSeconds($backoffSeconds),
            ]);

            if ($payment) {
                $payment->update(['activation_status' => 'retrying']);
            }
            $client->update(['connection_status' => 'activation_retrying']);
        }

        return $job->fresh();
    }

    /**
     * Process all due retrying/queued jobs (used after router recovery or application restart).
     */
    public function retryPendingJobs(?int $tenantId = null, bool $forceImmediate = false): array
    {
        $query = NetworkActivationJob::withoutGlobalScopes()
            ->whereIn('status', [
                NetworkActivationJob::STATUS_QUEUED,
                NetworkActivationJob::STATUS_RETRYING,
                NetworkActivationJob::STATUS_FAILED_DEAD_LETTER,
            ]);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if (!$forceImmediate) {
            $query->where(function ($q) {
                $q->whereNull('next_retry_at')
                  ->orWhere('next_retry_at', '<=', Carbon::now());
            })->where('status', '!=', NetworkActivationJob::STATUS_FAILED_DEAD_LETTER);
        }

        $jobs = $query->get();
        $succeeded = 0;
        $stillRetrying = 0;

        foreach ($jobs as $job) {
            if ($forceImmediate && $job->status === NetworkActivationJob::STATUS_FAILED_DEAD_LETTER) {
                $job->update(['attempts' => max(0, $job->max_attempts - 1), 'status' => NetworkActivationJob::STATUS_RETRYING]);
            }
            $updated = $this->attemptJobExecution($job);
            if (in_array($updated->status, [NetworkActivationJob::STATUS_CONNECTED_VERIFIED, NetworkActivationJob::STATUS_APPLIED], true)) {
                $succeeded++;
            } else {
                $stillRetrying++;
            }
        }

        return [
            'processed'      => $jobs->count(),
            'succeeded'      => $succeeded,
            'still_retrying' => $stillRetrying,
        ];
    }
}
