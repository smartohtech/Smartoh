<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->enum('provider', ['mpesa_daraja', 'stripe', 'pesapal', 'flutterwave', 'paystack', 'bank_manual']);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sandbox')->default(true);
            $table->text('credentials_encrypted'); // JSON of consumer_key, consumer_secret, shortcode, passkey, etc.
            $table->string('webhook_secret')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('billing_plan_id')->nullable()->constrained('billing_plans')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'cancelled', 'overdue'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('payment_gateway_id')->nullable()->constrained('payment_gateways')->nullOnDelete();
            $table->string('transaction_reference')->unique();
            $table->string('external_transaction_id')->nullable()->index(); // e.g. M-Pesa Receipt Number "QGH678912"
            $table->enum('payment_method', [
                'mpesa_stk', 'mpesa_c2b', 'mpesa_till', 'mpesa_paybill',
                'stripe_card', 'pesapal', 'flutterwave', 'paystack',
                'bank_transfer', 'cash', 'voucher', 'wallet_debit'
            ]);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('KES');
            $table->string('phone_number')->nullable();
            $table->string('payer_name')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('pending');
            $table->string('idempotency_key')->nullable()->unique();
            $table->text('raw_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('billing_plan_id')->nullable()->constrained('billing_plans')->nullOnDelete();
            $table->string('code')->unique();
            $table->enum('type', ['hotspot', 'pppoe'])->default('hotspot');
            $table->decimal('price', 10, 2);
            $table->integer('data_limit_mb')->nullable();
            $table->integer('duration_minutes')->default(1440); // 24 hours
            $table->enum('status', ['active', 'used', 'expired'])->default('active');
            $table->foreignId('used_by_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reminder_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->boolean('send_sms')->default(true);
            $table->boolean('send_email')->default(true);
            $table->boolean('send_whatsapp')->default(false);
            $table->integer('days_before_expiry')->default(3);
            $table->text('reminder_template')->nullable();
            $table->text('expiry_template')->nullable();
            $table->timestamps();
        });

        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->enum('channel', ['sms', 'email', 'whatsapp']);
            $table->string('recipient');
            $table->text('message');
            $table->enum('status', ['sent', 'delivered', 'failed'])->default('sent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
        Schema::dropIfExists('reminder_settings');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payment_gateways');
    }
};
