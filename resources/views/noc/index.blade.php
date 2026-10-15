@extends('layouts.app')

@section('content')
<div x-data="{ 
    incidentModal: false, 
    capacityModal: false,
    newCapacityMbps: {{ $capacityMetrics['total_capacity_mbps'] }},
    autoRefresh: true,
    pollingInterval: null,
    isRefreshing: false,
    isBursting: false,
    contentionMultiplier: 4,
    aiQuery: '', 
    aiResponse: '', 
    aiLoading: false,
    metrics: @json($capacityMetrics),
    chartInstance: null,

    init() {
        this.$nextTick(() => {
            this.initThroughputChart();
            this.startPolling();
        });
    },

    startPolling() {
        if (this.pollingInterval) clearInterval(this.pollingInterval);
        this.pollingInterval = setInterval(() => {
            if (this.autoRefresh && !this.isBursting) {
                this.pollTelemetry();
            }
        }, 3000);
    },

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    },

    toggleAutoRefresh() {
        this.autoRefresh = !this.autoRefresh;
        if (this.autoRefresh) {
            this.startPolling();
        } else {
            this.stopPolling();
        }
    },

    pollTelemetry() {
        this.isRefreshing = true;
        fetch('{{ route('noc.capacity.telemetry') }}')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.metrics) {
                    this.metrics = data.metrics;
                    this.updateChart(data.metrics);
                }
                this.isRefreshing = false;
            })
            .catch(err => {
                console.error('Error polling capacity telemetry:', err);
                this.isRefreshing = false;
            });
    },

    simulatePeakBurst() {
        this.isBursting = !this.isBursting;
        if (this.isBursting) {
            // Simulate peak evening load surge (+45% traffic pull)
            const surgeFactor = 1.48;
            this.metrics.pulled_rx_mbps = Math.round(this.metrics.pulled_rx_mbps * surgeFactor * 10) / 10;
            this.metrics.pulled_tx_mbps = Math.round(this.metrics.pulled_tx_mbps * 1.35 * 10) / 10;
            this.metrics.pulled_total_mbps = Math.round((this.metrics.pulled_rx_mbps + this.metrics.pulled_tx_mbps) * 10) / 10;
            this.metrics.pulled_rx_formatted = this.metrics.pulled_rx_mbps >= 1000 ? (this.metrics.pulled_rx_mbps / 1000).toFixed(2) + ' Gbps' : this.metrics.pulled_rx_mbps + ' Mbps';
            this.metrics.pulled_tx_formatted = this.metrics.pulled_tx_mbps >= 1000 ? (this.metrics.pulled_tx_mbps / 1000).toFixed(2) + ' Gbps' : this.metrics.pulled_tx_mbps + ' Mbps';
            this.metrics.pulled_total_formatted = this.metrics.pulled_total_mbps >= 1000 ? (this.metrics.pulled_total_mbps / 1000).toFixed(2) + ' Gbps' : this.metrics.pulled_total_mbps + ' Mbps';
            this.metrics.pulled_utilization_pct = Math.min(100, Math.round((this.metrics.pulled_rx_mbps / this.metrics.total_capacity_mbps) * 1000) / 10);
            
            // Add surge point to chart history
            const now = new Date();
            const timeStr = now.toTimeString().split(' ')[0];
            if (this.chartInstance) {
                this.chartInstance.data.labels.push(timeStr);
                this.chartInstance.data.labels.shift();
                this.chartInstance.data.datasets[0].data.push(this.metrics.pulled_rx_mbps);
                this.chartInstance.data.datasets[0].data.shift();
                this.chartInstance.data.datasets[1].data.push(this.metrics.pulled_tx_mbps);
                this.chartInstance.data.datasets[1].data.shift();
                this.chartInstance.update();
            }
        } else {
            this.pollTelemetry();
        }
    },

    calculateCustomSlots(speedMbps) {
        if (!speedMbps) return 0;
        const remaining = this.metrics.remaining_capacity_mbps || 0;
        return Math.floor((remaining * this.contentionMultiplier) / speedMbps);
    },

    initThroughputChart() {
        const ctx = document.getElementById('nocLiveThroughputChart')?.getContext('2d');
        if (!ctx) return;
        
        const history = this.metrics.live_history || [];
        const labels = history.map(h => h.time);
        const rxData = history.map(h => h.rx);
        const txData = history.map(h => h.tx);

        this.chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Download Pulled (Rx)',
                        data: rxData,
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.18)',
                        fill: true,
                        tension: 0.38,
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#06b6d4',
                    },
                    {
                        label: 'Upload Pulled (Tx)',
                        data: txData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.08)',
                        fill: true,
                        tension: 0.38,
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#3b82f6',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 300 },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(51, 65, 85, 0.25)' },
                        ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' } }
                    },
                    y: {
                        grid: { color: 'rgba(51, 65, 85, 0.35)' },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 10, family: 'monospace' },
                            callback: function(v) {
                                return v >= 1000 ? (v / 1000).toFixed(1) + 'G' : v + 'M';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            color: '#cbd5e1',
                            font: { size: 11 },
                            boxWidth: 12,
                            usePointStyle: true,
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': ' + ctx.parsed.y + ' Mbps';
                            }
                        }
                    }
                }
            }
        });
    },

    updateChart(metrics) {
        if (!this.chartInstance) return;
        const history = metrics.live_history || [];
        if (history.length === 0) return;

        this.chartInstance.data.labels = history.map(h => h.time);
        this.chartInstance.data.datasets[0].data = history.map(h => h.rx);
        this.chartInstance.data.datasets[1].data = history.map(h => h.tx);
        this.chartInstance.update('none');
    },

    askAiQuick(promptText) {
        this.aiQuery = promptText;
        this.aiLoading = true;
        this.aiResponse = '';
        fetch('{{ route('noc.ask-ai') }}?query=' + encodeURIComponent(promptText))
            .then(r => r.json())
            .then(d => { this.aiResponse = d.answer; this.aiLoading = false; })
            .catch(() => { this.aiResponse = 'Error querying AI assistant.'; this.aiLoading = false; });
    }
}" class="space-y-6">

    <!-- Header & Action Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5">
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-cyan-400 animate-ping"></span>
                    <span>NOC Command &amp; Autonomous Telemetry</span>
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 uppercase tracking-wider font-mono">
                    Tier-2 ISP Core
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Real-time upstream bandwidth headroom, live traffic pulled, optical attenuation, and circuit diagnostics.</p>
        </div>

        <div class="flex items-center space-x-2.5 self-start sm:self-auto flex-wrap gap-y-2">
            <!-- Peak Burst Simulator Toggle -->
            <button @click="simulatePeakBurst()" class="px-3 py-2 rounded-xl text-xs font-semibold flex items-center space-x-1.5 transition border shadow-sm" :class="isBursting ? 'bg-amber-500/20 text-amber-300 border-amber-500/40 shadow-amber-500/20 animate-pulse' : 'bg-slate-900 text-slate-300 border-slate-700 hover:border-amber-500/50 hover:text-amber-300'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <span x-text="isBursting ? 'Peak Surge Active (+48%)' : 'Simulate Peak Surge'"></span>
            </button>

            <!-- Configure Contracted Capacity -->
            <button @click="capacityModal = true" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 hover:border-slate-600 text-xs font-semibold rounded-xl flex items-center space-x-2 transition shadow-sm">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span>Contracted Capacity</span>
            </button>

            <!-- Raise Network Incident -->
            <button @click="incidentModal = true" class="px-4 py-2 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-rose-600/20 flex items-center space-x-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>Raise Network Incident</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- ISP CAPACITY & REAL-TIME BANDWIDTH MONITORING ENGINE -->
    <!-- ========================================================================= -->
    <div class="space-y-4">
        
        <!-- Live Status Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-xl">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white flex items-center space-x-2">
                        <span>ISP Upstream Bandwidth &amp; Real-Time Capacity Engine</span>
                        <span class="text-[10px] font-mono text-cyan-400 font-semibold px-2 py-0.5 rounded bg-cyan-950 border border-cyan-800/60">{{ $capacityMetrics['tenant']['name'] }}</span>
                    </h2>
                    <p class="text-[11px] text-slate-400">Continuous telemetry calculating bandwidth remaining to sell vs. real-time traffic actively pulled from core routers.</p>
                </div>
            </div>

            <div class="flex items-center space-x-3 text-xs">
                <!-- Polling Status -->
                <div class="flex items-center space-x-1.5 font-mono text-[11px] text-slate-400">
                    <span class="w-2 h-2 rounded-full" :class="autoRefresh ? 'bg-cyan-400 animate-ping' : 'bg-slate-500'"></span>
                    <span x-text="autoRefresh ? 'Live Pulse (3s)' : 'Paused'">Live Pulse (3s)</span>
                    <span class="text-slate-600">•</span>
                    <span class="text-slate-500">Last Synced: <strong x-text="metrics.last_sync_human" class="text-slate-300 font-mono">{{ $capacityMetrics['last_sync_human'] }}</strong></span>
                </div>

                <!-- Auto Refresh Toggle -->
                <button type="button" @click="toggleAutoRefresh()" class="px-2.5 py-1.5 rounded-lg border text-[11px] font-medium transition" :class="autoRefresh ? 'bg-cyan-950/60 border-cyan-500/40 text-cyan-300 hover:bg-cyan-900/60' : 'bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700'">
                    <span x-text="autoRefresh ? 'Pause Auto-Sync' : 'Resume Auto-Sync'"></span>
                </button>

                <!-- Sync Now Button -->
                <button type="button" @click="pollTelemetry()" :disabled="isRefreshing" class="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 transition" title="Refresh Live Data">
                    <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isRefreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                </button>
            </div>
        </div>

        <!-- 4 Primary Capacity Hero Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Total Upstream Contracted Capacity -->
            <div class="bg-gradient-to-br from-slate-900/95 via-slate-900 to-slate-950 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition"></div>
                <div>
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span class="uppercase tracking-wider font-semibold text-[11px] text-slate-300">Total Upstream Capacity</span>
                        <button @click="capacityModal = true" class="text-[10px] font-mono text-cyan-400 hover:text-cyan-300 underline">Adjust</button>
                    </div>
                    <div class="mt-2.5 flex items-baseline space-x-2">
                        <span class="text-3xl font-extrabold text-white font-mono tracking-tight" x-text="metrics.total_capacity_formatted">
                            {{ $capacityMetrics['total_capacity_formatted'] }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-400 font-mono mt-0.5" x-text="'(' + metrics.total_capacity_mbps.toLocaleString() + ' Mbps Contracted)'">
                        ({{ number_format($capacityMetrics['total_capacity_mbps']) }} Mbps Contracted)
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                    <span class="flex items-center space-x-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                        <span>Carrier Upstream Transit</span>
                    </span>
                    <span class="font-mono text-slate-300">100% Pipe</span>
                </div>
            </div>

            <!-- Card 2: Capacity Sold to Clients (Committed Bandwidth) -->
            <div class="bg-gradient-to-br from-slate-900/95 via-slate-900 to-slate-950 border border-slate-800 rounded-2xl p-5 shadow-xl flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition"></div>
                <div>
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span class="uppercase tracking-wider font-semibold text-[11px] text-indigo-400">Capacity Sold to Clients</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-indigo-500/15 text-indigo-300 border border-indigo-500/30" x-text="metrics.sold_pct + '% Sold'">
                            {{ $capacityMetrics['sold_pct'] }}% Sold
                        </span>
                    </div>
                    <div class="mt-2.5 flex items-baseline space-x-2">
                        <span class="text-3xl font-extrabold text-white font-mono tracking-tight" x-text="metrics.sold_download_formatted">
                            {{ $capacityMetrics['sold_download_formatted'] }}
                        </span>
                        <span class="text-xs text-indigo-300 font-mono">Committed</span>
                    </div>
                    <!-- Sold Progress Bar -->
                    <div class="w-full bg-slate-800/80 rounded-full h-1.5 mt-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-blue-500 h-1.5 rounded-full transition-all duration-500" :style="'width: ' + Math.min(100, metrics.sold_pct) + '%'"></div>
                    </div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400 font-mono">
                    <span><strong x-text="metrics.active_subscribers_count" class="text-white">{{ $capacityMetrics['active_subscribers_count'] }}</strong> Active Clients</span>
                    <span title="Effective load with contention ratio applied">Contended: <strong x-text="metrics.contended_sold_formatted" class="text-indigo-300">{{ $capacityMetrics['contended_sold_formatted'] }}</strong></span>
                </div>
            </div>

            <!-- Card 3: Capacity Remaining to Sell (Commercial Headroom) -->
            <div class="bg-gradient-to-br from-slate-900/95 via-slate-900 to-slate-950 border border-emerald-500/30 rounded-2xl p-5 shadow-xl flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition"></div>
                <div>
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span class="uppercase tracking-wider font-semibold text-[11px] text-emerald-400">Capacity Remaining to Sell</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-emerald-500/20 text-emerald-300 border border-emerald-500/40" x-text="metrics.remaining_pct + '% Available'">
                            {{ $capacityMetrics['remaining_pct'] }}% Available
                        </span>
                    </div>
                    <div class="mt-2.5 flex items-baseline space-x-2">
                        <span class="text-3xl font-extrabold text-emerald-400 font-mono tracking-tight" x-text="metrics.remaining_capacity_formatted">
                            {{ $capacityMetrics['remaining_capacity_formatted'] }}
                        </span>
                        <span class="text-xs text-emerald-300/80 font-mono">Headroom</span>
                    </div>
                    <!-- Remaining Progress Bar -->
                    <div class="w-full bg-slate-800/80 rounded-full h-1.5 mt-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-1.5 rounded-full transition-all duration-500" :style="'width: ' + Math.min(100, metrics.remaining_pct) + '%'"></div>
                    </div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                    <span class="text-emerald-400 font-semibold truncate" x-text="metrics.headroom_status.badge">
                        {{ $capacityMetrics['headroom_status']['badge'] }}
                    </span>
                    <span class="font-mono text-slate-300 text-[10px]">
                        ~<span x-text="metrics.sales_projections[0] ? metrics.sales_projections[0].contended_slots_remaining.toLocaleString() : '500'">{{ number_format($capacityMetrics['sales_projections'][0]['contended_slots_remaining']) }}</span> x 10M Slots
                    </span>
                </div>
            </div>

            <!-- Card 4: Real-Time Capacity Being Pulled (Live Throughput) -->
            <div class="bg-gradient-to-br from-slate-900/95 via-slate-900 to-slate-950 border border-cyan-500/30 rounded-2xl p-5 shadow-xl flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-cyan-500/10 rounded-full blur-2xl group-hover:bg-cyan-500/20 transition"></div>
                <div>
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span class="uppercase tracking-wider font-semibold text-[11px] text-cyan-400 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                            <span>Real-Time Pulled</span>
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-cyan-500/20 text-cyan-300 border border-cyan-500/40" x-text="metrics.pulled_utilization_pct + '% Pipe Load'">
                            {{ $capacityMetrics['pulled_utilization_pct'] }}% Pipe Load
                        </span>
                    </div>
                    <div class="mt-2.5 flex items-baseline space-x-2">
                        <span class="text-3xl font-extrabold text-cyan-300 font-mono tracking-tight" x-text="metrics.pulled_total_formatted">
                            {{ $capacityMetrics['pulled_total_formatted'] }}
                        </span>
                        <span class="text-xs text-cyan-400/80 font-mono">Total Pulled</span>
                    </div>
                    <!-- Pulled Progress Bar -->
                    <div class="w-full bg-slate-800/80 rounded-full h-1.5 mt-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-1.5 rounded-full transition-all duration-300" :style="'width: ' + Math.min(100, metrics.pulled_utilization_pct) + '%'"></div>
                    </div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400 font-mono">
                    <span class="text-cyan-400">↓ <span x-text="metrics.pulled_rx_formatted">{{ $capacityMetrics['pulled_rx_formatted'] }}</span></span>
                    <span class="text-blue-400">↑ <span x-text="metrics.pulled_tx_formatted">{{ $capacityMetrics['pulled_tx_formatted'] }}</span></span>
                    <span class="text-slate-400 text-[10px]"><strong x-text="metrics.active_router_sessions" class="text-slate-200">{{ $capacityMetrics['active_router_sessions'] }}</strong> Sessions</span>
                </div>
            </div>

        </div>

        <!-- Live Waveform Chart & Commercial Capacity Headroom Matrix -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            
            <!-- Left 2 Cols: Real-time Live Throughput Waveform Oscilloscope -->
            <div class="lg:col-span-2 bg-slate-900/90 backdrop-blur border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                            <span>Live WAN Bandwidth Pulled vs. Capacity Ceiling</span>
                            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                        </h3>
                        <p class="text-xs text-slate-400">Dynamic oscilloscope stream sampling aggregate inbound Rx and outbound Tx traffic every 3s.</p>
                    </div>
                    <div class="flex items-center space-x-2 text-[11px] font-mono">
                        <span class="px-2.5 py-1 bg-slate-950 rounded-lg text-slate-400 border border-slate-800">
                            Ceiling: <strong class="text-rose-400" x-text="metrics.total_capacity_formatted">{{ $capacityMetrics['total_capacity_formatted'] }}</strong>
                        </span>
                        <span class="px-2.5 py-1 bg-slate-950 rounded-lg text-slate-400 border border-slate-800">
                            Rx: <strong class="text-cyan-400" x-text="metrics.pulled_rx_formatted">{{ $capacityMetrics['pulled_rx_formatted'] }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Chart Canvas Container -->
                <div class="h-64 sm:h-72 w-full relative">
                    <canvas id="nocLiveThroughputChart"></canvas>
                </div>

                <!-- Tri-Color Stacked Bandwidth Distribution Strip -->
                <div class="space-y-1.5 pt-2 border-t border-slate-800/80">
                    <div class="flex items-center justify-between text-[11px] font-mono text-slate-400">
                        <span>Upstream Bandwidth Partitioning</span>
                        <span>
                            <strong class="text-cyan-400" x-text="metrics.pulled_utilization_pct + '% Pulled'">{{ $capacityMetrics['pulled_utilization_pct'] }}% Pulled</strong> • 
                            <strong class="text-indigo-400" x-text="Math.max(0, (metrics.sold_pct - metrics.pulled_utilization_pct).toFixed(1)) + '% Idle Sold'">{{ max(0, round($capacityMetrics['sold_pct'] - $capacityMetrics['pulled_utilization_pct'], 1)) }}% Idle Sold</strong> • 
                            <strong class="text-emerald-400" x-text="metrics.remaining_pct + '% Available to Sell'">{{ $capacityMetrics['remaining_pct'] }}% Available to Sell</strong>
                        </span>
                    </div>
                    <div class="w-full h-3 bg-slate-950 rounded-xl overflow-hidden flex border border-slate-800">
                        <div class="bg-cyan-500 h-full transition-all duration-300" :style="'width: ' + Math.min(100, metrics.pulled_utilization_pct) + '%'" title="Real-Time Traffic Pulled"></div>
                        <div class="bg-indigo-600 h-full transition-all duration-300" :style="'width: ' + Math.max(0, Math.min(100 - metrics.pulled_utilization_pct, metrics.sold_pct - metrics.pulled_utilization_pct)) + '%'" title="Committed Sold (Idle/Buffer)"></div>
                        <div class="bg-emerald-500 h-full transition-all duration-300" :style="'width: ' + Math.min(100, metrics.remaining_pct) + '%'" title="Available to Sell to New Clients"></div>
                    </div>
                </div>
            </div>

            <!-- Right Col: Commercial Sales Headroom & Client Onboarding Calculator -->
            <div class="bg-slate-900/90 backdrop-blur border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-white">Commercial Capacity Headroom</h3>
                            <p class="text-xs text-slate-400">Available client slots for onboarding before saturation.</p>
                        </div>
                        <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </span>
                    </div>

                    <!-- Headroom Diagnostic Alert Card -->
                    <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl space-y-1.5">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full" :class="metrics.remaining_pct >= 40 ? 'bg-emerald-400' : (metrics.remaining_pct >= 20 ? 'bg-amber-400' : 'bg-rose-400')"></span>
                            <span class="text-xs font-bold text-white" x-text="metrics.headroom_status.badge">{{ $capacityMetrics['headroom_status']['badge'] }}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed" x-text="metrics.headroom_status.text">{{ $capacityMetrics['headroom_status']['text'] }}</p>
                    </div>

                    <!-- Contention Policy Multiplier Switcher -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <span>Target Contention Ratio:</span>
                            <span class="text-cyan-400 font-mono" x-text="'1:' + contentionMultiplier">1:4</span>
                        </div>
                        <div class="grid grid-cols-4 gap-1.5 text-xs font-mono">
                            <button type="button" @click="contentionMultiplier = 1" class="py-1 rounded-lg border text-center transition" :class="contentionMultiplier === 1 ? 'bg-cyan-600 text-white border-cyan-500' : 'bg-slate-950 text-slate-400 border-slate-800 hover:text-white'">1:1 CIR</button>
                            <button type="button" @click="contentionMultiplier = 2" class="py-1 rounded-lg border text-center transition" :class="contentionMultiplier === 2 ? 'bg-cyan-600 text-white border-cyan-500' : 'bg-slate-950 text-slate-400 border-slate-800 hover:text-white'">1:2 Pro</button>
                            <button type="button" @click="contentionMultiplier = 4" class="py-1 rounded-lg border text-center transition" :class="contentionMultiplier === 4 ? 'bg-cyan-600 text-white border-cyan-500' : 'bg-slate-950 text-slate-400 border-slate-800 hover:text-white'">1:4 Home</button>
                            <button type="button" @click="contentionMultiplier = 8" class="py-1 rounded-lg border text-center transition" :class="contentionMultiplier === 8 ? 'bg-cyan-600 text-white border-cyan-500' : 'bg-slate-950 text-slate-400 border-slate-800 hover:text-white'">1:8 Dense</button>
                        </div>
                    </div>

                    <!-- Client Onboarding Capacity Projection Matrix -->
                    <div class="space-y-2">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sellable Subscriber Headroom By Tier:</div>
                        <div class="space-y-1.5 max-h-52 overflow-y-auto pr-1">
                            <template x-for="proj in metrics.sales_projections" :key="proj.plan_name">
                                <div class="p-2.5 bg-slate-950/60 border border-slate-800/80 rounded-xl flex items-center justify-between text-xs hover:border-slate-700 transition">
                                    <div class="space-y-0.5">
                                        <div class="font-semibold text-slate-200" x-text="proj.plan_name"></div>
                                        <div class="text-[10px] text-slate-500 font-mono">
                                            Dedicated: <span class="text-slate-400" x-text="proj.raw_slots_remaining.toLocaleString() + ' slots'"></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold font-mono text-emerald-400 text-sm">
                                            +<span x-text="calculateCustomSlots(proj.speed_mbps).toLocaleString()"></span> <span class="text-[10px] font-normal text-slate-400">clients</span>
                                        </div>
                                        <div class="text-[10px] font-mono text-slate-400" title="Monthly potential revenue from remaining capacity">
                                            ~<span x-text="proj.currency + ' ' + Math.round((calculateCustomSlots(proj.speed_mbps) * (proj.monthly_revenue_potential / Math.max(1, proj.contended_slots_remaining))) / 1000).toLocaleString() + 'k/mo'"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800 text-[11px] flex items-center justify-between text-slate-400">
                    <span>Upstream Expansion Needed?</span>
                    <button @click="capacityModal = true" class="text-cyan-400 hover:text-cyan-300 font-semibold font-mono">Upgrade Upstream Pool &rarr;</button>
                </div>
            </div>

        </div>

        <!-- Core Gateways & WAN Interface Telemetry Table -->
        <div class="bg-slate-900/90 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center space-x-2">
                        <span>Core Gateways &amp; WAN Interfaces Telemetry</span>
                        <span class="text-xs font-mono font-normal text-slate-400">({{ count($capacityMetrics['routers_telemetry']) }} Interfaces Tracked)</span>
                    </h3>
                    <p class="text-xs text-slate-400">Physical interface link rates, optical SFP+ uplinks, and real-time bandwidth consumption per router.</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold font-mono bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">
                        Agg. Rx: <span x-text="metrics.pulled_rx_formatted">{{ $capacityMetrics['pulled_rx_formatted'] }}</span>
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold font-mono bg-blue-500/10 text-blue-300 border border-blue-500/30">
                        Agg. Tx: <span x-text="metrics.pulled_tx_formatted">{{ $capacityMetrics['pulled_tx_formatted'] }}</span>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Gateway Router</th>
                            <th class="px-4 py-3">WAN Interface</th>
                            <th class="px-4 py-3">IP Address</th>
                            <th class="px-4 py-3">Live Rx (Down)</th>
                            <th class="px-4 py-3">Live Tx (Up)</th>
                            <th class="px-4 py-3">Total Pulled</th>
                            <th class="px-4 py-3">Port Limit</th>
                            <th class="px-4 py-3">Port Load</th>
                            <th class="px-4 py-3">Active Sessions</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono text-[11px]">
                        <template x-for="r in metrics.routers_telemetry" :key="r.id">
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-white font-sans text-xs" x-text="r.name"></div>
                                    <div class="text-[10px] text-slate-500" x-text="r.model"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-cyan-300 border border-slate-700" x-text="r.interface_name"></span>
                                </td>
                                <td class="px-4 py-3 text-slate-400" x-text="r.ip_address"></td>
                                <td class="px-4 py-3 text-cyan-400 font-bold">
                                    ↓ <span x-text="r.rx_mbps.toLocaleString() + ' Mbps'"></span>
                                </td>
                                <td class="px-4 py-3 text-blue-400 font-bold">
                                    ↑ <span x-text="r.tx_mbps.toLocaleString() + ' Mbps'"></span>
                                </td>
                                <td class="px-4 py-3 text-white font-bold" x-text="r.total_mbps.toLocaleString() + ' Mbps'"></td>
                                <td class="px-4 py-3 text-slate-400" x-text="r.port_capacity_formatted"></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-16 bg-slate-950 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full" :class="r.port_load_pct > 80 ? 'bg-rose-500' : (r.port_load_pct > 60 ? 'bg-amber-500' : 'bg-cyan-400')" :style="'width: ' + Math.min(100, r.port_load_pct) + '%'"></div>
                                        </div>
                                        <span class="text-[10px]" :class="r.port_load_pct > 80 ? 'text-rose-400' : 'text-slate-400'" x-text="r.port_load_pct + '%'"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-300" x-text="r.sessions_count.toLocaleString() + ' users'"></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold" :class="r.status === 'optimal' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : (r.status === 'elevated' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/40' : 'bg-rose-500/20 text-rose-400 border border-rose-500/40')" x-text="r.status"></span>
                                </td>
                            </tr>
                        </template>
                        @if(empty($capacityMetrics['routers_telemetry']))
                        <tr>
                            <td colspan="10" class="text-center py-6 text-slate-500 text-xs">No core routers registered yet. <a href="{{ route('network.mikrotik') }}" class="text-cyan-400 underline">Add a MikroTik Core Gateway</a>.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Severity Outages & Circuit Alarms Summary Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-rose-400">Critical / Fatal Outages</div>
                <div class="text-2xl font-bold text-white font-mono mt-1">{{ $severityCounts['critical'] }}</div>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30 font-mono">SEV-1</span>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-amber-400">Degraded Links & Warnings</div>
                <div class="text-2xl font-bold text-white font-mono mt-1">{{ $severityCounts['warning'] }}</div>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 font-mono">SEV-2</span>
        </div>
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-cyan-400">Informational Alarms</div>
                <div class="text-2xl font-bold text-white font-mono mt-1">{{ $severityCounts['info'] }}</div>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 font-mono">SEV-3</span>
        </div>
    </div>

    <!-- Interactive AI ISP Copilot Box with Quick Diagnostic Chips -->
    <div id="ai" class="bg-gradient-to-r from-blue-950/40 via-slate-900/90 to-purple-950/40 border border-cyan-500/30 rounded-2xl p-5 shadow-2xl space-y-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/20 border border-cyan-400/40 flex items-center justify-center font-bold text-cyan-400">
                AI
            </div>
            <div>
                <h3 class="text-sm font-bold text-white">Smartoh AI Operations Diagnostic Copilot</h3>
                <p class="text-xs text-slate-400">Ask operational queries on capacity remaining, real-time traffic pulled, BGP failovers, optical power standards, or FUP quotas.</p>
            </div>
        </div>

        <!-- Quick Query Chips -->
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-[10px] text-slate-400 uppercase font-mono">Quick Inquiries:</span>
            <button type="button" @click="askAiQuick('How much capacity is remaining to sell to clients?')" class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 border border-slate-700 hover:border-cyan-500 text-[11px] text-slate-300 rounded-lg transition font-mono">
                ⚡ Capacity Remaining to Sell
            </button>
            <button type="button" @click="askAiQuick('What is the real-time capacity being pulled across gateways?')" class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 border border-slate-700 hover:border-cyan-500 text-[11px] text-slate-300 rounded-lg transition font-mono">
                📈 Real-Time Pulled Bandwidth
            </button>
            <button type="button" @click="askAiQuick('What are GPON optical power RX standards?')" class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 border border-slate-700 hover:border-cyan-500 text-[11px] text-slate-300 rounded-lg transition font-mono">
                💡 GPON Optical Standards
            </button>
            <button type="button" @click="askAiQuick('How does the Fair Usage Policy FUP engine work?')" class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 border border-slate-700 hover:border-cyan-500 text-[11px] text-slate-300 rounded-lg transition font-mono">
                🛡️ FUP Quota Throttle
            </button>
        </div>

        <div class="flex items-center space-x-2">
            <input type="text" x-model="aiQuery" @keydown.enter="
                if (!aiQuery.trim()) return;
                aiLoading = true;
                aiResponse = '';
                fetch('{{ route('noc.ask-ai') }}?query=' + encodeURIComponent(aiQuery))
                    .then(r => r.json())
                    .then(d => { aiResponse = d.answer; aiLoading = false; })
                    .catch(() => { aiResponse = 'Error querying AI assistant.'; aiLoading = false; });
            " placeholder="e.g. How much capacity is remaining to sell? Or what is real-time traffic pulled?" class="flex-1 px-4 py-2.5 bg-slate-950/90 border border-slate-700/80 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 font-mono transition">
            
            <button type="button" @click="
                if (!aiQuery.trim()) return;
                aiLoading = true;
                aiResponse = '';
                fetch('{{ route('noc.ask-ai') }}?query=' + encodeURIComponent(aiQuery))
                    .then(r => r.json())
                    .then(d => { aiResponse = d.answer; aiLoading = false; })
                    .catch(() => { aiResponse = 'Error querying AI assistant.'; aiLoading = false; });
            " class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-cyan-600/30 transition">
                <span x-show="!aiLoading">Consult Copilot</span>
                <span x-show="aiLoading" x-cloak>Analyzing...</span>
            </button>
        </div>

        <!-- AI Response Box -->
        <div x-show="aiResponse" x-cloak class="p-4 bg-slate-950/80 border border-cyan-500/40 rounded-xl text-xs font-mono text-cyan-300 whitespace-pre-line leading-relaxed">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Copilot Diagnostic Analysis:</div>
            <div x-text="aiResponse"></div>
        </div>
    </div>

    <!-- Active NOC Incidents & Outage Alarms -->
    <div class="bg-slate-900/80 backdrop-blur border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white">Active NOC Incidents &amp; Alarms</h3>
            <span class="text-xs text-slate-400 font-mono">{{ count($incidents) }} Incidents Tracked</span>
        </div>
        <div class="divide-y divide-slate-800/60 p-4 space-y-3">
            @forelse($incidents as $inc)
            <div class="p-4 bg-slate-950/60 border border-slate-800 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs">
                <div class="space-y-1.5">
                    <div class="flex items-center space-x-2.5">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono {{ $inc->severity === 'critical' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' : ($inc->severity === 'warning' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/40' : 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/40') }}">
                            {{ $inc->severity }}
                        </span>
                        <span class="font-bold text-white font-mono text-sm">{{ $inc->incident_number }}: {{ $inc->title }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-slate-800 text-slate-300">{{ $inc->status }}</span>
                    </div>
                    <p class="text-slate-400 text-xs">{{ $inc->description }}</p>
                    <div class="text-[11px] text-slate-500 font-mono flex flex-wrap items-center gap-3 pt-1">
                        <span>Affected Component: <strong class="text-slate-300">{{ $inc->affected_component }}</strong></span>
                        <span>•</span>
                        <span>Clients Impacted: <strong class="text-amber-400">{{ $inc->affected_clients_count }}</strong></span>
                        <span>•</span>
                        <span>Assigned Tech: <strong class="text-cyan-400">{{ $inc->technician?->user?->name ?? 'Unassigned' }}</strong></span>
                        <span>•</span>
                        <span>Started: {{ $inc->started_at->diffForHumans() }}</span>
                    </div>
                </div>

                @if($inc->status !== 'resolved')
                <form method="POST" action="{{ route('noc.incident.resolve', $inc) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 whitespace-nowrap transition">
                        Mark Resolved
                    </button>
                </form>
                @endif
            </div>
            @empty
            <div class="text-center py-6 text-slate-500 text-xs">No active network alarms or incidents! All circuits nominal.</div>
            @endforelse
        </div>
    </div>

    <!-- Configure Upstream Capacity Modal -->
    <div x-show="capacityModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="capacityModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </span>
                    <h3 class="text-base font-bold text-white">Configure Contracted Upstream Bandwidth</h3>
                </div>
                <button @click="capacityModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('noc.capacity.update') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Contracted Upstream Capacity (in Mbps) *</label>
                    <input type="number" name="capacity_mbps" x-model="newCapacityMbps" min="100" max="10000000" step="100" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono text-sm focus:border-cyan-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Specify total purchased bandwidth from Tier-1 transit or Wholesale Capacity Pools.</p>
                </div>

                <!-- Quick Presets -->
                <div class="space-y-1.5">
                    <label class="block font-medium text-slate-400 text-[11px]">Quick Carrier Presets:</label>
                    <div class="grid grid-cols-4 gap-2 font-mono text-xs">
                        <button type="button" @click="newCapacityMbps = 1000" class="px-2 py-1.5 bg-slate-950 border border-slate-800 hover:border-cyan-500 rounded-lg text-slate-300">1 Gbps</button>
                        <button type="button" @click="newCapacityMbps = 2500" class="px-2 py-1.5 bg-slate-950 border border-slate-800 hover:border-cyan-500 rounded-lg text-slate-300">2.5 Gbps</button>
                        <button type="button" @click="newCapacityMbps = 5000" class="px-2 py-1.5 bg-slate-950 border border-slate-800 hover:border-cyan-500 rounded-lg text-slate-300">5 Gbps</button>
                        <button type="button" @click="newCapacityMbps = 10000" class="px-2 py-1.5 bg-slate-950 border border-slate-800 hover:border-cyan-500 rounded-lg text-slate-300">10 Gbps</button>
                        <button type="button" @click="newCapacityMbps = 20000" class="px-2 py-1.5 bg-slate-950 border border-slate-800 hover:border-cyan-500 rounded-lg text-slate-300">20 Gbps</button>
                        <button type="button" @click="newCapacityMbps = 40000" class="px-2 py-1.5 bg-slate-950 border border-slate-800 hover:border-cyan-500 rounded-lg text-slate-300">40 Gbps</button>
                        <button type="button" @click="newCapacityMbps = 100000" class="px-2 py-1.5 bg-slate-950 border border-slate-800 hover:border-cyan-500 rounded-lg text-slate-300">100 Gbps</button>
                        <button type="button" @click="newCapacityMbps = 200000" class="px-2 py-1.5 bg-slate-950 border border-slate-800 hover:border-cyan-500 rounded-lg text-slate-300">200 Gbps</button>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="capacityModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-cyan-600/30">
                        Save Upstream Capacity
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Raise Incident Modal -->
    <div x-show="incidentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur">
        <div @click.away="incidentModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Log NOC Network Incident</h3>
                <button @click="incidentModal = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form method="POST" action="{{ route('noc.incident.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-300 mb-1">Incident Headline *</label>
                    <input type="text" name="title" placeholder="e.g. Core Gateway BGP Peering Packet Loss" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Severity Level *</label>
                        <select name="severity" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            <option value="warning">Warning (SEV-2)</option>
                            <option value="critical">Critical (SEV-1)</option>
                            <option value="fatal">Fatal Outage (SEV-0)</option>
                            <option value="info">Informational (SEV-3)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-300 mb-1">Assign Technician</label>
                        <select name="assigned_technician_id" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            <option value="">Unassigned</option>
                            @foreach($technicians as $t)
                            <option value="{{ $t->id }}">{{ $t->user?->name }} ({{ $t->staff_id_number }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Affected Component / Interface *</label>
                    <input type="text" name="affected_component" placeholder="e.g. ether1-WAN / OLT-1 Port 3" required class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-medium text-slate-300 mb-1">Detailed Diagnostic Notes</label>
                    <textarea name="description" rows="3" placeholder="Provide context, ping loss figures, or failover status..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="incidentModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl shadow-lg shadow-rose-600/30">
                        Dispatch Alarm
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
