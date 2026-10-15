<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'location', 'latitude', 'longitude',
        'bank_account_name', 'bank_account_number', 'mpesa_paybill', 'mpesa_till',
        'invoice_prefix', 'receipt_prefix', 'next_invoice_number'
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function mikrotikDevices(): HasMany
    {
        return $this->hasMany(MikroTikDevice::class);
    }

    public function oltDevices(): HasMany
    {
        return $this->hasMany(OLTDevice::class);
    }
}
