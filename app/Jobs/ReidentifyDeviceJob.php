<?php

namespace App\Jobs;

use App\Models\Device;
use App\Services\UDIE\IdentificationEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReidentifyDeviceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $deviceId
    ) {}

    public function handle(IdentificationEngine $engine): void
    {
        $device = Device::find($this->deviceId);
        if (!$device) return;

        $engine->identify($device);
    }
}
