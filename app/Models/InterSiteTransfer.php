<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterSiteTransfer extends Model
{
    use HasFactory, BelongsToTenant;

    protected $guarded = [];

    public function fromSite()
    {
        return $this->belongsTo(Site::class, 'from_site_id');
    }

    public function toSite()
    {
        return $this->belongsTo(Site::class, 'to_site_id');
    }
}
