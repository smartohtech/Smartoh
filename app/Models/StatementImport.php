<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatementImport extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'payment_gateway_id',
        'uploaded_by_user_id',
        'filename',
        'provider',
        'total_rows',
        'matched_count',
        'exception_count',
        'duplicate_count',
        'status',
    ];

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
