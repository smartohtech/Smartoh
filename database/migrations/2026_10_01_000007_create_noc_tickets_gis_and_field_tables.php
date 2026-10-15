<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technicians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('staff_id_number')->unique();
            $table->string('phone');
            $table->string('specialization')->default('Fiber & Wireless Technician');
            $table->boolean('is_available')->default(true);
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });

        Schema::create('noc_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('technicians')->nullOnDelete();
            $table->string('incident_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('severity', ['info', 'warning', 'critical', 'fatal'])->default('warning');
            $table->enum('status', ['open', 'investigating', 'identified', 'monitoring', 'resolved'])->default('open');
            $table->string('affected_component'); // e.g. "Main Gateway 10.10.0.1", "OLT 1 PON Port 3", "Upstream Fiber Cut"
            $table->integer('affected_clients_count')->default(0);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->enum('category', ['no_internet', 'slow_speeds', 'billing', 'relocation', 'router_replacement', 'other'])->default('no_internet');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'waiting_client', 'resolved', 'closed'])->default('open');
            $table->timestamp('sla_due_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_client_reply')->default(false);
            $table->text('message');
            $table->string('attachment_url')->nullable();
            $table->timestamps();
        });

        Schema::create('fiber_infrastructure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->enum('type', ['pop', 'fat_box', 'closure', 'pole', 'fiber_route']);
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('route_coordinates_json')->nullable(); // For lines/cables
            $table->integer('capacity_cores')->default(16);
            $table->integer('used_cores')->default(0);
            $table->enum('status', ['operational', 'maintenance', 'damaged'])->default('operational');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('installation_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('technicians')->nullOnDelete();
            $table->foreignId('fiber_box_id')->nullable()->constrained('fiber_infrastructure')->nullOnDelete();
            $table->string('job_number')->unique();
            $table->timestamp('scheduled_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('fiber_cable_meters')->default(0);
            $table->decimal('optical_power_rx_dbm', 5, 2)->nullable();
            $table->string('installed_cpe_serial')->nullable();
            $table->string('installed_ont_serial')->nullable();
            $table->json('photo_attachments_json')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('technician_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('category'); // e.g. "Routers", "ONTs", "Drop Cable", "Connectors"
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('min_threshold_alert')->default(5);
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->string('supplier_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('installation_jobs');
        Schema::dropIfExists('fiber_infrastructure');
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('noc_incidents');
        Schema::dropIfExists('technicians');
    }
};
