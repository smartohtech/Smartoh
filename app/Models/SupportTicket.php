<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'client_id', 'assigned_user_id',
        'ticket_number', 'subject', 'category', 'priority', 'status', 'sla_due_at'
    ];

    protected $casts = [
        'sla_due_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }
}
