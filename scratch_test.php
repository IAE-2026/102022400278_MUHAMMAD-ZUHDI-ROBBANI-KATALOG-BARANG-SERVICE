<?php

// 1. First get M2M token
$url = 'https://iae-sso.virtualfri.id/api/v1/auth/token';
$data_m2m = [
    'api_key' => 'KEY-MHS-287'
];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_m2m));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
$res_token = json_decode(curl_exec($ch), true);
curl_close($ch);

$token = $res_token['token'] ?? null;
echo "M2M Token: " . $token . "\n\n";

if ($token) {
    // 2. SOAP audit call
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
 <soap:Body>
 <iae:AuditRequest>
 <iae:TeamID>TEAM-04</iae:TeamID>
 <iae:ActivityName>ItemCreated</iae:ActivityName>
 <iae:LogContent><![CDATA[{"id": 1, "name": "Kamera Mirrorless", "price": 7500000}]]></iae:LogContent>
 </iae:AuditRequest>
 </soap:Body>
</soap:Envelope>';

    $url = 'https://iae-sso.virtualfri.id/soap/v1/audit';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: text/xml; charset=utf-8',
        'Accept: text/xml'
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $xmlElement = simplexml_load_string($response);
    $xmlElement->registerXPathNamespace('iae', 'http://iae.central/audit');
    $receipts = $xmlElement->xpath('//iae:ReceiptNumber');
    $receiptNumber = isset($receipts[0]) ? (string) $receipts[0] : null;
    echo "Receipt Number Parsed: " . $receiptNumber . "\n";
}




