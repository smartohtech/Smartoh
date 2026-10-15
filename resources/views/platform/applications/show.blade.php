@extends('layouts.platform')

@section('title', 'Application Details: ' . $application->company_name)

@section('content')
<div x-data="{ approveModal: false, rejectModal: false, infoModal: false }" class="space-y-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5">
                <a href="{{ route('platform.applications.index') }}" class="text-xs text-slate-400 hover:text-white">&larr; Back to Queue</a>
                <span class="text-slate-600">•</span>
                <span class="text-xs font-mono text-cyan-400 font-bold">{{ $application->application_number }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight mt-1">{{ $application->company_name }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Submitted on {{ $application->created_at->format('F d, Y \a\t H:i:s T') }}</p>
        </div>

        <div class="flex items-center space-x-2.5 flex-wrap gap-y-2">
            @if($application->isPending() || $application->isInfoRequested())
                <button @click="infoModal = true" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold rounded-xl transition">
                    Request Extra Info
                </button>
                <button @click="rejectModal = true" class="px-3.5 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-semibold rounded-xl transition">
                    Reject Application
                </button>
                <button @click="approveModal = true" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Approve &amp; Start 7-Day Trial</span>
                </button>
            @elseif($application->isApproved())
                <span class="px-3 py-1.5 rounded-xl text-xs font-bold font-mono bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Approved &amp; Active</span>
                </span>
                @if($application->provisioned_tenant_id)
                <a href="{{ route('platform.tenants.show', $application->provisioned_tenant_id) }}" class="px-3.5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs rounded-xl transition">
                    Open Tenant Workspace &rarr;
                </a>
                @endif
            @else
                <span class="px-3 py-1.5 rounded-xl text-xs font-bold font-mono bg-rose-500/15 text-rose-300 border border-rose-500/30">
                    Application Rejected
                </span>
            @endif
        </div>
    </div>

    <!-- Application Status Alert Banner (if rejection or info request notes exist) -->
    @if($application->isInfoRequested() && $application->info_request_notes)
    <div class="p-4 bg-blue-500/10 border border-blue-500/30 rounded-2xl space-y-1 text-xs">
        <div class="font-bold text-blue-400 uppercase tracking-wider text-[10px]">Awaiting Additional Information from Applicant:</div>
        <p class="text-slate-300">{{ $application->info_request_notes }}</p>
    </div>
    @endif

    @if($application->isRejected() && $application->rejection_reason)
    <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl space-y-1 text-xs">
        <div class="font-bold text-rose-400 uppercase tracking-wider text-[10px]">Rejection Reason Recorded:</div>
        <p class="text-slate-300">{{ $application->rejection_reason }}</p>
    </div>
    @endif

    <!-- 2 Columns: Application Metadata vs Requested Plan Terms -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Business Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-3">ISP Business &amp; Administrator Profile</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 uppercase tracking-wider text-[10px] font-mono block">Company / Brand Name</span>
                        <span class="font-bold text-white text-sm mt-0.5 block">{{ $application->company_name }}</span>
                    </div>

                    <div>
                        <span class="text-slate-500 uppercase tracking-wider text-[10px] font-mono block">Requested Subdomain</span>
                        <span class="font-mono text-cyan-400 font-bold block mt-0.5">{{ $application->subdomain }}.smartoh.co.ke</span>
                    </div>

                    <div>
                        <span class="text-slate-500 uppercase tracking-wider text-[10px] font-mono block">Designated Admin Contact</span>
                        <span class="font-medium text-slate-200 block mt-0.5">{{ $application->contact_name }}</span>
                    </div>

                    <div>
                        <span class="text-slate-500 uppercase tracking-wider text-[10px] font-mono block">Contact Email</span>
                        <span class="font-mono text-slate-200 block mt-0.5">{{ $application->email }}</span>
                    </div>

                    <div>
                        <span class="text-slate-500 uppercase tracking-wider text-[10px] font-mono block">Contact Telephone</span>
                        <span class="font-mono text-slate-200 block mt-0.5">{{ $application->phone }}</span>
                    </div>

                    <div>
                        <span class="text-slate-500 uppercase tracking-wider text-[10px] font-mono block">Country / Operating Region</span>
                        <span class="text-slate-200 block mt-0.5">{{ $application->country }}</span>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="text-slate-500 uppercase tracking-wider text-[10px] font-mono block">Physical / Operating Address</span>
                        <span class="text-slate-300 block mt-0.5">{{ $application->address ?? 'Not specified' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-3">Network Architecture &amp; Hardware Inventory</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono">
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase block">Distribution Topology</span>
                        <span class="text-cyan-400 font-bold text-sm block mt-1">{{ $application->network_type }}</span>
                    </div>

                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase block">Active Subscribers</span>
                        <span class="text-white font-bold text-sm block mt-1">{{ number_format($application->current_subscribers) }}</span>
                    </div>

                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800">
                        <span class="text-[10px] text-slate-500 uppercase block">Core Gateway Hardware</span>
                        <span class="text-emerald-400 font-bold text-sm block mt-1">{{ $application->hardware_type ?? 'MikroTik CCR / CHR' }}</span>
                    </div>
                </div>
            </div>

            <!-- Reviewer Decision History -->
            @if($application->reviewed_at)
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-2 text-xs font-mono">
                <div class="text-[10px] text-slate-500 uppercase font-bold">Review Decision Log:</div>
                <div class="text-slate-300">
                    Reviewed by <strong class="text-white">{{ $application->reviewer->name ?? 'Administrator' }}</strong> 
                    on {{ $application->reviewed_at->format('M d, Y H:i:s') }}
                </div>
            </div>
            @endif

        </div>

        <!-- Right Col: Requested SaaS Plan & Pricing Terms -->
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-cyan-500/30 rounded-2xl p-6 shadow-xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white">Selected SaaS Tier</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 uppercase">
                        7-Day Free Trial
                    </span>
                </div>

                <div>
                    <div class="text-2xl font-extrabold text-white">{{ $application->saasPlan->name ?? 'Custom Tier' }}</div>
                    <div class="text-xs text-slate-400 mt-1 font-mono">
                        {{ $application->billing_interval === 'annually' ? 'Annual Billing (2 Months Discounted)' : 'Monthly Flexible Subscription' }}
                    </div>
                </div>

                <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-2">
                    <div class="flex items-baseline justify-between">
                        <span class="text-xs text-slate-400">Regular List Price:</span>
                        <span class="text-lg font-bold font-mono text-white">
                            {{ $application->saasPlan->currency ?? 'KES' }} 
                            {{ number_format($application->billing_interval === 'annually' ? $application->saasPlan->annual_price : $application->saasPlan->monthly_price) }}
                        </span>
                    </div>
                    @if($application->negotiated_price)
                    <div class="flex items-baseline justify-between text-xs pt-1 border-t border-slate-800">
                        <span class="text-amber-400">Special Negotiated Price:</span>
                        <span class="font-bold font-mono text-emerald-400">
                            {{ $application->saasPlan->currency ?? 'KES' }} {{ number_format($application->negotiated_price) }}
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Plan Features Matrix -->
                @if($version = $application->saasPlanVersion ?? $application->saasPlan?->currentVersion)
                <div class="space-y-2 text-xs">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">Included Tier Entitlements:</div>
                    <ul class="space-y-1.5 text-slate-300 text-[11px] font-mono">
                        <li class="flex items-center justify-between">
                            <span>Max Client Accounts:</span>
                            <strong class="text-white">{{ number_format($version->features['max_subscribers'] ?? 0) }}</strong>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Managed Routers:</span>
                            <strong class="text-white">{{ $version->features['max_routers'] ?? 0 }}</strong>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Admin/Technician Staff:</span>
                            <strong class="text-white">{{ $version->features['max_staff'] ?? 0 }}</strong>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Branches / Sites:</span>
                            <strong class="text-white">{{ $version->features['max_sites'] ?? 1 }}</strong>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>API Access:</span>
                            <strong class="{{ !empty($version->features['has_api_access']) ? 'text-emerald-400' : 'text-slate-500' }}">
                                {{ !empty($version->features['has_api_access']) ? 'Enabled' : 'Disabled' }}
                            </strong>
                        </li>
                    </ul>
                </div>
                @endif
            </div>
        </div>

    </div>

    <!-- MODAL 1: Approve Application & Provision Tenant -->
    <div x-show="approveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="approveModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    <span>Approve Application &amp; Provision Tenant</span>
                </h3>
                <button @click="approveModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
                Approving this application will execute an <strong>idempotent provisioning transaction</strong>:
                creating tenant workspace <code class="text-cyan-400">{{ $application->subdomain }}</code>, configuring default admin <code class="text-white">{{ $application->email }}</code>, and starting a <strong>7-day free trial</strong> on the {{ $application->saasPlan->name ?? 'selected' }} tier.
            </p>

            <form method="POST" action="{{ route('platform.applications.approve', $application) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Negotiated / Approved Price (Optional Override)</label>
                    <input type="number" step="0.01" name="negotiated_price" placeholder="Leave empty for standard list price" value="{{ $application->negotiated_price }}" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono text-xs">
                    <p class="text-[11px] text-slate-500 mt-1">Specify custom rate if pre-arranged with this ISP.</p>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Approval Notes (Logged to Audit Trail)</label>
                    <textarea name="approval_notes" rows="2" placeholder="e.g. Verified business registration and BGP ASN details..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="approveModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30">
                        Confirm &amp; Provision Tenant
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Reject Application -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="rejectModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white text-rose-400">Reject ISP Application</h3>
                <button @click="rejectModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('platform.applications.reject', $application) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Rejection Reason * (Required)</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="State exact legal or technical reason for audit trail..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl shadow-lg shadow-rose-600/30">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Request Additional Info -->
    <div x-show="infoModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="infoModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Request Further Information</h3>
                <button @click="infoModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('platform.applications.request-info', $application) }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Information / Documents Required *</label>
                    <textarea name="info_request_notes" rows="4" required placeholder="e.g. Please provide your Communications Authority license number or verify requested subdomain..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="infoModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-cyan-600/30">
                        Dispatch Inquiry
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
