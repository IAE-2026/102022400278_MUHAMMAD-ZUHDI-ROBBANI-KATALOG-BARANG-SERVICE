<?php

// 1. Get SSO Token for User
echo "1. Fetching SSO User Token...\n";
$url = 'https://iae-sso.virtualfri.id/api/v1/auth/token';
$data_sso = [
    'email' => 'warga41@ktp.iae.id',
    'password' => 'KtpDigital2026!'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_sso));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
$res_token = json_decode(curl_exec($ch), true);
curl_close($ch);

$token = $res_token['token'] ?? null;
if (!$token) {
    echo "Failed to get User SSO Token: " . json_encode($res_token) . "\n";
    exit(1);
}
echo "User SSO Token obtained successfully (starts with): " . substr($token, 0, 30) . "...\n\n";

// 2. Call local API POST /api/admin/items using the SSO Token
echo "2. Sending POST /api/admin/items to local Docker container...\n";
$localUrl = 'http://localhost:8080/api/admin/items';
$itemData = [
    'name' => 'Kamera Mirrorless Test ' . rand(100, 999),
    'description' => 'Kondisi mulus untuk uji coba.',
    'base_price' => 8500000,
    'auction_start_at' => date('c', strtotime('+1 day')),
    'auction_end_at' => date('c', strtotime('+5 days')),
    'status' => 'OPEN'
];

$ch = curl_init($localUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($itemData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: " . $httpCode . "\n";
echo "Response Body:\n" . json_format($response) . "\n\n";

// Helper function to format json nicely
function json_format($json) {
    $decoded = json_decode($json, true);
    if ($decoded) {
        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    return $json;
}
