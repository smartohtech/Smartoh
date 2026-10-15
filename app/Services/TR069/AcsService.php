<?php

namespace App\Services\TR069;

use App\Models\ClientRouter;
use Illuminate\Support\Facades\Log;

class AcsService
{
    /**
     * Process TR-069 CWMP SOAP XML Inform from CPE Router
     */
    public function handleInform(string $xmlContent, ?int $tenantId = null): array
    {
        Log::info("TR-069 ACS: Received Inform message");

        $serial = 'SN' . rand(1000000, 9999999);
        $oui = '00259E'; // e.g. Huawei / TP-Link
        $manufacturer = 'Smartoh CPE';
        $model = 'HG8245H';
        $softwareVersion = 'V5R019C00S105';

        // Extract using regex or SimpleXML
        if (preg_match('/<SerialNumber>(.*?)<\/SerialNumber>/i', $xmlContent, $m)) {
            $serial = trim($m[1]);
        }
        if (preg_match('/<OUI>(.*?)<\/OUI>/i', $xmlContent, $m)) {
            $oui = trim($m[1]);
        }
        if (preg_match('/<ProductClass>(.*?)<\/ProductClass>/i', $xmlContent, $m)) {
            $model = trim($m[1]);
        }
        if (preg_match('/<Manufacturer>(.*?)<\/Manufacturer>/i', $xmlContent, $m)) {
            $manufacturer = trim($m[1]);
        }

        // Update or register router in database
        $router = ClientRouter::firstOrCreate(
            ['serial_number' => $serial],
            [
                'tenant_id' => $tenantId ?: 1,
                'manufacturer' => $manufacturer,
                'model' => $model,
                'preferred_management_path' => 'tr069',
                'status' => 'online',
            ]
        );

        $router->update([
            'status' => 'online',
            'software_version' => $softwareVersion,
            'last_inform_at' => now(),
            'optical_power_rx' => $router->optical_power_rx ?: -19.45,
            'optical_power_tx' => $router->optical_power_tx ?: 2.30,
            'uptime_seconds' => $router->uptime_seconds + 3600,
        ]);

        $soapResponse = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cwmp="urn:dslforum-org:cwmp-1-0">
    <soapenv:Header>
        <cwmp:ID soapenv:mustUnderstand="1">1</cwmp:ID>
    </soapenv:Header>
    <soapenv:Body>
        <cwmp:InformResponse>
            <MaxEnvelopes>1</MaxEnvelopes>
        </cwmp:InformResponse>
    </soapenv:Body>
</soapenv:Envelope>
XML;

        return [
            'router' => $router,
            'soap_response' => $soapResponse,
        ];
    }

    /**
     * Generate SOAP SetParameterValues for remote Wi-Fi and WAN provisioning
     */
    public function generateSetParameterValues(array $params): string
    {
        $parameterList = '';
        foreach ($params as $name => $value) {
            $parameterList .= <<<XML
                <ParameterValueStruct>
                    <Name>{$name}</Name>
                    <Value xsi:type="xsd:string">{$value}</Value>
                </ParameterValueStruct>
XML;
        }

        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cwmp="urn:dslforum-org:cwmp-1-0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <soapenv:Header><cwmp:ID soapenv:mustUnderstand="1">2</cwmp:ID></soapenv:Header>
    <soapenv:Body>
        <cwmp:SetParameterValues>
            <ParameterList soapenc:arrayType="cwmp:ParameterValueStruct[" . count($params) . "]">
                {$parameterList}
            </ParameterList>
            <ParameterKey>SmartohConfigJob</ParameterKey>
        </cwmp:SetParameterValues>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    }
}
