@extends('layouts.app')

@section('title', 'Platform System Settings')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Platform System Settings</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Configure core engine parameters, security thresholds, telemetry intervals, and maintenance policies.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Engine Operational (v2.4.1)
            </span>
            <button type="button" onclick="alert('Configuration changes saved to cache!')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-500/20 transition">
                Save System Settings
            </button>
        </div>
    </div>

    <!-- Main Grid Settings Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Security, Engine & Networking -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Security & Authentication -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800/80 shadow-sm">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/60 flex items-center justify-center text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Security &amp; Session Policies</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Manage user session timeouts, authentication guards, and audit retention.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Session Inactivity Timeout (mins)</label>
                        <input type="number" value="120" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Max Failed Logins Lockout</label>
                        <select class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                            <option value="5" selected>5 attempts (15 min lockout)</option>
                            <option value="3">3 attempts (30 min lockout)</option>
                            <option value="10">10 attempts (5 min lockout)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Two-Factor Authentication (2FA)</label>
                        <select class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                            <option value="optional">Optional for All Users</option>
                            <option value="enforced_admin" selected>Enforced for SuperAdmin &amp; NOC</option>
                            <option value="enforced_all">Enforced for Entire Staff</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Audit Log Retention Policy</label>
                        <select class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                            <option value="365" selected>365 Days (Recommended)</option>
                            <option value="180">180 Days</option>
                            <option value="90">90 Days</option>
                            <option value="indefinite">Indefinite Retention</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Force HTTPS / TLS 1.3 Strict Transport</div>
                        <div class="text-[11px] text-slate-500">Requires HSTS headers and prevents cleartext API / TR-069 traffic.</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>

            <!-- MikroTik & Network Telemetry Engine -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800/80 shadow-sm">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 flex items-center justify-center text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">RouterOS &amp; TR-069 Telemetry Tuning</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Polling frequencies, ICMP ping monitors, and auto-quarantine parameters.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">RouterOS Polling Cadence</label>
                        <select class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                            <option value="15">Every 15 Seconds (Real-time NOC)</option>
                            <option value="30" selected>Every 30 Seconds (Balanced)</option>
                            <option value="60">Every 60 Seconds (Low Overhead)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">TR-069 CWMP Periodic Inform</label>
                        <select class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                            <option value="300" selected>300s (5 Minutes)</option>
                            <option value="600">600s (10 Minutes)</option>
                            <option value="1800">1800s (30 Minutes)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Ping Timeout Threshold (ms)</label>
                        <input type="number" value="150" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Rogue DHCP Quarantine Action</label>
                        <select class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                            <option value="auto_isolate" selected>Auto-Blacklist &amp; Isolate Port</option>
                            <option value="alert_only">Alert Only (NOC Notification)</option>
                            <option value="rate_limit">Throttle to 64 Kbps</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Billing & Dunning Automation Parameters -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800/80 shadow-sm">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Billing &amp; Grace Period Engine</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Invoice lead times, automatic cutoffs, and tax configurations.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Invoice Generation Days Prior</label>
                        <input type="number" value="3" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Grace Period (Days)</label>
                        <input type="number" value="2" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">VAT / Tax Rate (%)</label>
                        <input type="number" value="16" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Col: Backups, System Health & Maintenance Mode -->
        <div class="space-y-6">

            <!-- System Maintenance Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800/80 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2">Maintenance Mode</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">When active, client portals and self-service are offline with an informational banner.</p>

                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 mb-4">
                    <div>
                        <span class="text-xs font-semibold text-slate-800 dark:text-white">Status: Live Mode</span>
                        <span class="block text-[10px] text-emerald-500 font-medium">All services accessible</span>
                    </div>
                    <button type="button" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition">
                        Enable Maintenance
                    </button>
                </div>
            </div>

            <!-- Database & Configuration Backups -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800/80 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2">Platform Automated Backups</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Encrypted off-site snapshots of SQLite database, MikroTik scripts, and tenant configs.</p>

                <div class="space-y-3 mb-5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Last Snapshot:</span>
                        <span class="font-mono text-slate-800 dark:text-slate-200">Today, 03:00 AM (48 MB)</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Backup Destination:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Amazon S3 (Encrypted AES-256)</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Frequency:</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">Every 6 Hours</span>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <button type="button" onclick="alert('Backup triggered immediately! Check storage bucket in 2 minutes.')" class="w-full py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-semibold rounded-xl transition border border-slate-300 dark:border-slate-700">
                        Trigger Immediate Backup
                    </button>
                    <button type="button" class="w-full py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 text-xs font-semibold rounded-xl transition border border-blue-200 dark:border-blue-900">
                        Download Latest Snapshot (.sqlite.gz)
                    </button>
                </div>
            </div>

            <!-- System Info -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800/80 shadow-sm text-xs space-y-2.5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">System Specifications</h3>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Framework:</span>
                    <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">Laravel v12.1.1</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">PHP Version:</span>
                    <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">8.3.16 CLI</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500">Database Driver:</span>
                    <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">SQLite 3 (WAL mode)</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Cache Driver:</span>
                    <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">Database / File</span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
