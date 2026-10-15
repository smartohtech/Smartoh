<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\SupportTicket;
use App\Models\User;
use Tests\TestCase;

class SupportTicketsFeatureTest extends TestCase
{
    protected function getSuperAdmin(): User
    {
        return User::where('role', 'super_admin')->first() 
            ?? User::first();
    }

    public function test_tickets_desk_renders_successfully(): void
    {
        $user = $this->getSuperAdmin();

        $response = $this->actingAs($user)->get('/tickets');
        $response->assertStatus(200);

        $response->assertSee('Customer Support Tickets &amp; Helpdesk', false);
        $response->assertSee('Open Tickets');
        $response->assertSee('Create Support Ticket');
        $response->assertSee('Tickets Queue');
    }

    public function test_staff_can_create_support_ticket(): void
    {
        $user = $this->getSuperAdmin();
        $client = Client::first();

        $response = $this->actingAs($user)->post('/tickets', [
            'client_id' => $client->id,
            'subject' => 'Fiber drop cable damaged by road works',
            'category' => 'no_internet',
            'priority' => 'urgent',
            'assigned_user_id' => $user->id,
            'sla_hours' => 4,
            'message' => 'Subscriber called reporting excavator cut fiber drop outside gate.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('support_tickets', [
            'subject' => 'Fiber drop cable damaged by road works',
            'priority' => 'urgent',
            'category' => 'no_internet',
        ]);
    }

    public function test_staff_can_view_ticket_and_post_reply(): void
    {
        $user = $this->getSuperAdmin();
        $ticket = SupportTicket::first();

        $viewResponse = $this->actingAs($user)->get('/tickets/' . $ticket->id);
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee($ticket->ticket_number);
        $viewResponse->assertSee('Post Staff Reply');

        $replyResponse = $this->actingAs($user)->post('/tickets/' . $ticket->id . '/reply', [
            'message' => 'Technician dispatched to resplice drop fiber cable.',
            'status' => 'in_progress',
        ]);

        $replyResponse->assertSessionHas('success');
        $this->assertDatabaseHas('ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'message' => 'Technician dispatched to resplice drop fiber cable.',
        ]);
    }

    public function test_staff_can_mark_ticket_resolved(): void
    {
        $user = $this->getSuperAdmin();
        $ticket = SupportTicket::first();

        $response = $this->actingAs($user)->post('/tickets/' . $ticket->id . '/status', [
            'status' => 'resolved',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('resolved', $ticket->fresh()->status);
    }
}
