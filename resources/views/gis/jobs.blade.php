@extends('layouts.app')

@section('content')
<div x-data="{ jobModal: false, completeModal: false, selectedJobId: null }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Field Operations & Installation Work Orders</h1>
            <p class="text-xs text-slate-400 mt-0.5">Technician dispatch, fiber drop measurements, and optical power evidence verification.</p>
        </div>
        <button @click="jobModal = true" class="px-4 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-cyan-600/20 flex items-center space-x-2 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Dispatch Work Order</span>
        </button>
    </div>

    <!-- Jobs Table -->
    <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-mono uppercase tracking-wider">
                        <th class="p-4">Job Number</th>
                        <th class="p-4">Subscriber</th>
                        <th class="p-4">Assigned Tech</th>
                        <th class="p-4">Scheduled Date</th>
                        <th class="p-4">Fiber Cable</th>
                        <th class="p-4">Verified RX</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse($jobs as $j)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="p-4 font-bold text-cyan-400">{{ $j->job_number }}</td>
                        <td class="p-4 font-sans">
                            <div class="font-bold text-white text-sm">{{ $j->client?->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $j->client?->phone }} • {{ $j->client?->address }}</div>
                        </td>
                        <td class="p-4 font-sans text-slate-200">
                            {{ $j->technician?->user?->name ?? 'Unassigned' }}
                            <div class="text-[10px] text-slate-500 font-mono">{{ $j->technician?->staff_id_number }}</div>
                        </td>
                        <td class="p-4 text-slate-400">{{ $j->scheduled_at->format('d M Y H:i') }}</td>
                        <td class="p-4 text-slate-300">{{ $j->fiber_cable_meters ? $j->fiber_cable_meters . ' meters' : 'Pending' }}</td>
                        <td class="p-4">
                            @if($j->optical_power_rx_dbm)
                                <span class="text-emerald-400 font-bold">{{ $j->optical_power_rx_dbm }} dBm</span>
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($j->status === 'completed')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">COMPLETED</span>
                            @elseif($j->status === 'in_progress')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">IN PROGRESS</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">SCHEDULED</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            @if($j->status !== 'completed')
                            <button @click="selectedJobId = {{ $j->id }}; completeModal = true" class="px-3 py-1.5 text-[11px] font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition">
                                Complete Job
                            </button>
                            @else
                            <span class="text-slate-500 text-[11px]">Verified</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="p-8 text-center text-slate-500 font-sans">No field work orders created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jobs->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $jobs->links() }}
        </div>
        @endif
    </div>

    <!-- Dispatch Work Order Modal -->
    <div x-show="jobModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="jobModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Dispatch Installation Work Order</h3>
                <button @click="jobModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('gis.jobs.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Subscriber *</label>
                    <select name="client_id" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        @foreach($pendingClients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->account_number }}) - {{ $c->address }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Assign Technician</label>
                        <select name="technician_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            @foreach($technicians as $t)
                            <option value="{{ $t->id }}">{{ $t->user?->name }} ({{ $t->staff_id_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Uplink FAT Box</label>
                        <select name="fiber_box_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            @foreach($fatBoxes as $fb)
                            <option value="{{ $fb->id }}">{{ $fb->name }} ({{ $fb->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Scheduled Date & Time *</label>
                        <input type="datetime-local" name="scheduled_at" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Estimated Fiber Meters</label>
                        <input type="number" name="fiber_cable_meters" value="75" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Route & Conduit Instructions</label>
                    <textarea name="technician_notes" rows="2" placeholder="e.g. Route through rear conduit from pole 2..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="jobModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-cyan-600/30">
                        Dispatch Work Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Complete Job & Optical Power Modal -->
    <div x-show="completeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="completeModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Complete Job & Verify Optical Power</h3>
                <button @click="completeModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form :action="'{{ url('gis/jobs') }}/' + selectedJobId + '/complete'" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Verified Optical Power Reading (dBm) *</label>
                    <input type="number" step="0.01" name="optical_power_rx_dbm" value="-19.85" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono text-sm">
                    <p class="text-[10px] text-slate-500 mt-1">Must be between -15.0 and -27.0 dBm for green certification.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Installed ONT Serial</label>
                        <input type="text" name="installed_ont_serial" placeholder="HWTC19A84021" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Installed CPE Serial</label>
                        <input type="text" name="installed_cpe_serial" placeholder="ARCHERC6-4819" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Technician Sign-off Notes</label>
                    <textarea name="technician_notes" rows="2" placeholder="Splice verified, light power steady, customer Wi-Fi configured." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="completeModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30">
                        Certify & Complete
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
