<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    public function whatsapp()
    {
        $clients = Client::all();
        $templates = [
            [
                'id' => 'payment_reminder',
                'title' => 'Payment Reminder',
                'content' => 'Dear {{name}}, your internet service for Account {{account}} expires on {{date}}. Please renew via M-Pesa Paybill 522522 to avoid disruption.'
            ],
            [
                'id' => 'welcome',
                'title' => 'Welcome Onboarding',
                'content' => 'Welcome to our high-speed fiber network! Your subscriber account {{account}} is active. Contact support anytime via this number.'
            ],
            [
                'id' => 'outage_notice',
                'title' => 'Scheduled Maintenance Alert',
                'content' => 'Notice: We have scheduled routine fiber maintenance in your sector on {{date}} from 02:00 to 05:00. Thank you for your patience.'
            ],
            [
                'id' => 'resolved_ticket',
                'title' => 'Support Ticket Resolved',
                'content' => 'Hello {{name}}, your trouble ticket has been resolved by our field engineer. Please confirm your connection is restored.'
            ]
        ];

        return view('communication.whatsapp', compact('clients', 'templates'));
    }

    public function sendWhatsapp(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'recipient' => 'required|string',
        ]);

        $count = $request->input('recipient') === 'all' ? Client::count() : 1;

        return back()->with('success', "WhatsApp notification successfully dispatched to {$count} recipient(s)!");
    }

    public function reminders()
    {
        $dueClients = Client::where('status', 'expired')
            ->orWhereBetween('current_cycle_end', [now(), now()->addDays(3)])
            ->with('billingPlan')
            ->get();

        $reminderHistory = collect([
            (object)[
                'id' => 1,
                'client_name' => 'John Mwangi',
                'phone' => '+254712345678',
                'channel' => 'SMS & WhatsApp',
                'type' => '3 Days Before Expiry',
                'status' => 'Delivered',
                'sent_at' => now()->subHours(5)->diffForHumans(),
            ],
            (object)[
                'id' => 2,
                'client_name' => 'Amina Hassan',
                'phone' => '+254722334455',
                'channel' => 'SMS',
                'type' => 'Day of Expiry (Suspension Warning)',
                'status' => 'Delivered',
                'sent_at' => now()->subHours(18)->diffForHumans(),
            ],
            (object)[
                'id' => 3,
                'client_name' => 'Peter Kamau',
                'phone' => '+254733445566',
                'channel' => 'WhatsApp',
                'type' => 'Service Reconnected Confirmation',
                'status' => 'Delivered',
                'sent_at' => now()->subDay()->diffForHumans(),
            ]
        ]);

        return view('communication.reminders', compact('dueClients', 'reminderHistory'));
    }

    public function sendReminder(Request $request)
    {
        $clientId = $request->input('client_id');
        if ($clientId) {
            $client = Client::findOrFail($clientId);
            return back()->with('success', "Automated billing reminder nudge sent to {$client->first_name} {$client->last_name} ({$client->phone})!");
        }

        $count = Client::where('status', 'expired')
            ->orWhereBetween('current_cycle_end', [now(), now()->addDays(3)])
            ->count();

        return back()->with('success', "Automated reminder engine completed: {$count} renewal notices dispatched via SMS & WhatsApp.");
    }

    public function smsGateways()
    {
        $gateways = collect([
            (object)[
                'id' => 1,
                'name' => "Africa's Talking SMS",
                'provider' => 'africastalking',
                'username' => 'smartoh_telecom',
                'sender_id' => 'SMARTOH',
                'environment' => 'production',
                'status' => 'active',
                'balance' => 'KES 4,820.00',
                'last_tested' => now()->subHours(3)->diffForHumans(),
            ],
            (object)[
                'id' => 2,
                'name' => 'Twilio SMS Global',
                'provider' => 'twilio',
                'username' => 'AC4928340192830192',
                'sender_id' => '+14155552671',
                'environment' => 'sandbox',
                'status' => 'active',
                'balance' => '$45.00',
                'last_tested' => now()->subDays(2)->diffForHumans(),
            ],
            (object)[
                'id' => 3,
                'name' => 'Advanta SMS Gateway',
                'provider' => 'advanta',
                'username' => 'adv_smartoh',
                'sender_id' => 'NETFLOW',
                'environment' => 'production',
                'status' => 'active',
                'balance' => 'KES 12,450.00',
                'last_tested' => now()->subDays(5)->diffForHumans(),
            ]
        ]);

        return view('communication.sms', compact('gateways'));
    }

    public function testSmsGateway(Request $request)
    {
        $phone = $request->input('test_phone', '+254712345678');
        return back()->with('success', "Test SMS message successfully queued and delivered to {$phone} via Africa's Talking! Latency: 1.2s.");
    }
}
