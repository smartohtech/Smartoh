<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enhance payment_gateways for non-custodial multi-provider architecture
        Schema::table('payment_gateways', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_gateways', 'type')) {
                $table->string('type', 20)->default('api')->after('provider'); // api, manual, statement_import
            }
            if (!Schema::hasColumn('payment_gateways', 'shortcode')) {
                $table->string('shortcode')->nullable()->index()->after('name');
            }
            if (!Schema::hasColumn('payment_gateways', 'settlement_account_label')) {
                $table->string('settlement_account_label')->nullable()->after('shortcode');
            }
            if (!Schema::hasColumn('payment_gateways', 'settlement_mode')) {
                $table->string('settlement_mode', 40)->default('direct_merchant')->after('settlement_account_label'); // direct_merchant, provider_t_plus_1, manual_statement
            }
            if (!Schema::hasColumn('payment_gateways', 'health_status')) {
                $table->string('health_status', 20)->default('healthy')->after('is_sandbox'); // healthy, degraded, offline
            }
            if (!Schema::hasColumn('payment_gateways', 'last_tested_at')) {
                $table->timestamp('last_tested_at')->nullable()->after('health_status');
            }
            if (!Schema::hasColumn('payment_gateways', 'last_webhook_received_at')) {
                $table->timestamp('last_webhook_received_at')->nullable()->after('last_tested_at');
            }
            if (!Schema::hasColumn('payment_gateways', 'provider_limitations')) {
                $table->text('provider_limitations')->nullable()->after('webhook_secret');
            }
            if (!Schema::hasColumn('payment_gateways', 'is_non_custodial')) {
                $table->boolean('is_non_custodial')->default(true)->after('provider_limitations');
            }
        });

        // 2. Enhance payments for lifecycle states, settlement vs reconciliation vs activation, and import provenance
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'verification_status')) {
                $table->string('verification_status', 30)->default('pending_verification')->index()->after('status');
                // pending_verification, verified, unverified_manual, rejected, reversed
            }
            if (!Schema::hasColumn('payments', 'reconciliation_status')) {
                $table->string('reconciliation_status', 30)->default('unreconciled')->index()->after('verification_status');
                // unreconciled, matched, partially_allocated, overpaid_wallet_credited, exception, reversed
            }
            if (!Schema::hasColumn('payments', 'settlement_status')) {
                $table->string('settlement_status', 40)->default('pending_settlement')->index()->after('reconciliation_status');
                // direct_to_isp_merchant, pending_provider_settlement, settled_in_isp_account, held_by_provider, reversed
            }
            if (!Schema::hasColumn('payments', 'activation_status')) {
                $table->string('activation_status', 40)->default('not_started')->index()->after('settlement_status');
                // not_started, queued, retrying, applied, connected_verified, failed
            }
            if (!Schema::hasColumn('payments', 'source_channel')) {
                $table->string('source_channel', 40)->default('api_webhook')->after('payment_method');
                // api_webhook, stk_callback, status_query, statement_import, manual_cash_desk, customer_claim_unverified, voucher
            }
            if (!Schema::hasColumn('payments', 'allocated_amount')) {
                $table->decimal('allocated_amount', 12, 2)->default(0.00)->after('amount');
            }
            if (!Schema::hasColumn('payments', 'unallocated_amount')) {
                $table->decimal('unallocated_amount', 12, 2)->default(0.00)->after('allocated_amount');
            }
            if (!Schema::hasColumn('payments', 'account_reference')) {
                $table->string('account_reference')->nullable()->index()->after('phone_number');
            }
            if (!Schema::hasColumn('payments', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('payments', 'reconciled_at')) {
                $table->timestamp('reconciled_at')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('payments', 'settled_at')) {
                $table->timestamp('settled_at')->nullable()->after('reconciled_at');
            }
            if (!Schema::hasColumn('payments', 'reversed_at')) {
                $table->timestamp('reversed_at')->nullable()->after('settled_at');
            }
            if (!Schema::hasColumn('payments', 'reversal_reason')) {
                $table->text('reversal_reason')->nullable()->after('reversed_at');
            }
        });

        // 3. Enhance clients for explicit network activation & connectivity state
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'connection_status')) {
                $table->string('connection_status', 40)->default('offline')->after('status');
                // online, offline, activation_queued, activation_retrying, activation_applied, restricted
            }
            if (!Schema::hasColumn('clients', 'last_activated_at')) {
                $table->timestamp('last_activated_at')->nullable()->after('service_suspended_at');
            }
            if (!Schema::hasColumn('clients', 'mikrotik_device_id')) {
                $table->foreignId('mikrotik_device_id')->nullable()->constrained('mikrotik_devices')->nullOnDelete()->after('site_id');
            }
        });

        // 4. Enhance payment_webhook_events for tenant scoping & replay protection
        Schema::table('payment_webhook_events', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_webhook_events', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete()->after('id');
            }
            if (!Schema::hasColumn('payment_webhook_events', 'payment_gateway_id')) {
                $table->foreignId('payment_gateway_id')->nullable()->constrained('payment_gateways')->nullOnDelete()->after('tenant_id');
            }
            if (!Schema::hasColumn('payment_webhook_events', 'signature_valid')) {
                $table->boolean('signature_valid')->default(false)->after('event_type');
            }
            if (!Schema::hasColumn('payment_webhook_events', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->unique()->after('event_id');
            }
            if (!Schema::hasColumn('payment_webhook_events', 'source_ip')) {
                $table->string('source_ip', 45)->nullable()->after('signature_valid');
            }
        });

        // 5. Enhance audit_logs for tamper-evident hash chain and super admin support audit
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'previous_hash')) {
                $table->string('previous_hash', 64)->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('audit_logs', 'integrity_hash')) {
                $table->string('integrity_hash', 64)->nullable()->index()->after('previous_hash');
            }
            if (!Schema::hasColumn('audit_logs', 'support_access_reason')) {
                $table->text('support_access_reason')->nullable()->after('integrity_hash');
            }
        });

        // 6. Create payment_allocations table (for partial payments, multi-invoice allocation, overpayments)
        if (!Schema::hasTable('payment_allocations')) {
            Schema::create('payment_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('allocation_type', 40)->default('invoice_payment'); // invoice_payment, wallet_overpayment, credit_note, reversal_debit
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 7. Create network_activation_jobs table (durable queue, retries, exponential backoff, dead-letter)
        if (!Schema::hasTable('network_activation_jobs')) {
            Schema::create('network_activation_jobs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
                $table->foreignId('mikrotik_device_id')->nullable()->constrained('mikrotik_devices')->nullOnDelete();
                $table->string('service_type', 30)->default('pppoe'); // pppoe, hotspot, static, dhcp, radius
                $table->string('action', 40)->default('activate'); // activate, suspend, update_plan, disconnect_session
                $table->string('status', 40)->default('queued')->index();
                // queued, processing, retrying, applied, connected_verified, failed_dead_letter
                $table->unsignedInteger('attempts')->default(0);
                $table->unsignedInteger('max_attempts')->default(5);
                $table->timestamp('next_retry_at')->nullable()->index();
                $table->timestamp('executed_at')->nullable();
                $table->timestamp('verified_online_at')->nullable();
                $table->json('command_payload')->nullable();
                $table->json('router_response')->nullable();
                $table->text('last_error')->nullable();
                $table->boolean('noc_alerted')->default(false);
                $table->timestamps();
            });
        }

        // 8. Create payment_reconciliation_exceptions table (wrong references, unmatched, unverified claims, disputes)
        if (!Schema::hasTable('payment_reconciliation_exceptions')) {
            Schema::create('payment_reconciliation_exceptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained('payments')->cascadeOnDelete();
                $table->string('external_transaction_id')->nullable()->index();
                $table->string('submitted_reference')->nullable()->index();
                $table->string('phone_number')->nullable();
                $table->string('payer_name')->nullable();
                $table->decimal('amount', 12, 2);
                $table->string('exception_reason', 60)->index();
                // unknown_account_reference, duplicate_claim, unverified_manual_claim, amount_mismatch, late_callback, disputed_reversal
                $table->string('status', 30)->default('open')->index(); // open, resolved, rejected
                $table->foreignId('resolved_client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->foreignId('resolved_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamps();
            });
        }

        // 9. Create statement_imports table (CSV/XLSX manual statement import audit)
        if (!Schema::hasTable('statement_imports')) {
            Schema::create('statement_imports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('payment_gateway_id')->nullable()->constrained('payment_gateways')->nullOnDelete();
                $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('filename');
                $table->string('provider', 50)->default('bank_manual');
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('matched_count')->default(0);
                $table->unsignedInteger('exception_count')->default(0);
                $table->unsignedInteger('duplicate_count')->default(0);
                $table->string('status', 30)->default('completed');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('statement_imports');
        Schema::dropIfExists('payment_reconciliation_exceptions');
        Schema::dropIfExists('network_activation_jobs');
        Schema::dropIfExists('payment_allocations');
    }
};
