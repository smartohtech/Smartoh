@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    selectedTemplate: '',
    messageContent: '',
    applyTemplate(content) {
        this.messageContent = content;
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider font-mono">Communication Hub</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-mono">WhatsApp Cloud API</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">WhatsApp Client Messaging</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Bulk &amp; individual WhatsApp messaging to clients, automated payment notices, and outage notifications.</p>
        </div>
    </div>

    <!-- Main Grid: Templates & Message Composer -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Quick Templates (Matching Base44 WhatsApp.js) -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Quick Templates</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Click a template to load into composer</p>
            </div>

            <div class="space-y-2.5">
                @foreach($templates as $tmpl)
                <div @click="applyTemplate({{ json_encode($tmpl['content']) }})" class="p-3 rounded-xl border border-slate-100 dark:border-slate-800 hover:border-emerald-500 dark:hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20 cursor-pointer transition">
                    <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $tmpl['title'] }}</div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ $tmpl['content'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Composer -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Compose WhatsApp Broadcast</h3>

            <form method="POST" action="{{ route('whatsapp.send') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Recipients *</label>
                    <select name="recipient" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white">
                        <option value="all">Broadcast to All Active Subscribers ({{ $clients->count() }} clients)</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }} ({{ $c->phone }}) - {{ $c->account_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Message Content *</label>
                    <textarea name="message" x-model="messageContent" rows="6" required placeholder="Type WhatsApp message here. Use variables like @{{ name }}, @{{ account }}, @{{ date }}..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white"></textarea>
                </div>

                <!-- Preview Box -->
                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800 text-[11px] text-emerald-800 dark:text-emerald-300">
                    <span class="font-bold block mb-1">WhatsApp Cloud Preview:</span>
                    <span x-text="messageContent || 'Your message will appear here formatted with emoji and bold variables.'"></span>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl shadow-md shadow-emerald-600/20 flex items-center space-x-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        <span>Dispatch WhatsApp Notification</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
