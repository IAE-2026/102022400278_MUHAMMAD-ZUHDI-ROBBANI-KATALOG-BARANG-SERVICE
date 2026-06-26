<?php

namespace App\Services;

use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IaeCentralService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $teamId;

    public function __construct()
    {
        $this->baseUrl = 'https://iae-sso.virtualfri.id';
        $this->apiKey = env('IAE_API_KEY', 'KEY-MHS-287');
        $this->teamId = env('IAE_TEAM_ID', 'TEAM-04');
    }

    /**
     * Get M2M Token from SSO Server (cached for 50 minutes).
     */
    public function getM2MToken(): ?string
    {
        $token = Cache::get('iae_m2m_token');
        if ($token !== null) {
            return $token;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/auth/token", [
                'api_key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $token = $response->json('token');
                if ($token) {
                    Cache::put('iae_m2m_token', $token, 3000);
                    return $token;
                }
            }

            Log::error('Failed to retrieve IAE M2M token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (Exception $e) {
            Log::error('Exception in getM2MToken: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Fetch JWKS public keys from SSO Server (cached for 24 hours).
     */
    public function fetchJwks(): array
    {
        $jwks = Cache::get('iae_jwks');
        if ($jwks !== null && !empty($jwks['keys'])) {
            return $jwks;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/api/v1/auth/jwks");

            if ($response->successful()) {
                $jwks = $response->json();
                if ($jwks && !empty($jwks['keys'])) {
                    Cache::put('iae_jwks', $jwks, 86400);
                    return $jwks;
                }
            }

            Log::error('Failed to fetch JWKS keys', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (Exception $e) {
            Log::error('Exception in fetchJwks: ' . $e->getMessage());
        }

        return ['keys' => []];
    }

    /**
     * Decode and verify JWT using JWKS keys.
     */
    public function verifyJwt(string $token): ?object
    {
        try {
            $jwks = $this->fetchJwks();
            if (empty($jwks['keys'])) {
                return null;
            }

            // firebase/php-jwt parses the JWKS structure and decodes using the correct key matching 'kid'
            return JWT::decode($token, JWK::parseKeySet($jwks));
        } catch (Exception $e) {
            Log::warning('JWT verification failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send legacy SOAP XML Audit request.
     */
    public function sendSoapAudit(string $activityName, array $payload): ?string
    {
        $m2mToken = $this->getM2MToken();
        if (!$m2mToken) {
            Log::error('Cannot send SOAP audit: M2M token is missing.');
            return null;
        }

        $xmlPayload = sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
 <soap:Body>
 <iae:AuditRequest>
 <iae:TeamID>%s</iae:TeamID>
 <iae:ActivityName>%s</iae:ActivityName>
 <iae:LogContent><![CDATA[%s]]></iae:LogContent>
 </iae:AuditRequest>
 </soap:Body>
</soap:Envelope>',
            htmlspecialchars($this->teamId, ENT_XML1, 'UTF-8'),
            htmlspecialchars($activityName, ENT_XML1, 'UTF-8'),
            json_encode($payload)
        );

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$m2mToken}",
                'Content-Type' => 'text/xml; charset=utf-8',
                'Accept' => 'text/xml',
            ])->withBody($xmlPayload, 'text/xml')
              ->post("{$this->baseUrl}/soap/v1/audit");

            if ($response->successful()) {
                $body = $response->body();
                
                // 1. Try SimpleXML parsing
                $xmlElement = simplexml_load_string(trim($body));
                if ($xmlElement) {
                    $xmlElement->registerXPathNamespace('iae', 'http://iae.central/audit');
                    
                    // Cek Status terlebih dahulu
                    $statuses = $xmlElement->xpath('//iae:Status');
                    $status = !empty($statuses) ? (string) $statuses[0] : '';
                    
                    if ($status === 'SUCCESS') {
                        $receipts = $xmlElement->xpath('//iae:ReceiptNumber');
                        if (!empty($receipts)) {
                            return (string) $receipts[0];
                        }
                    } else {
                        Log::error('SOAP Audit failed: Status is not SUCCESS', ['status' => $status, 'body' => $body]);
                        return null;
                    }
                }

                // 2. Regex fallback
                if (preg_match('/<iae:Status>SUCCESS<\/iae:Status>/', $body)) {
                    if (preg_match('/ReceiptNumber>([^<]+)/', $body, $matches)) {
                        return trim($matches[1]);
                    }
                } else {
                    Log::error('SOAP Audit regex fallback failed: Status is not SUCCESS', ['body' => $body]);
                    return null;
                }
            }

            Log::error('SOAP Audit failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (Exception $e) {
            Log::error('Exception in sendSoapAudit: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Publish message/event to RabbitMQ exchange via REST API.
     */
    public function publishAmqpEvent(string $routingKey, array $message): bool
    {
        $m2mToken = $this->getM2MToken();
        if (!$m2mToken) {
            Log::error('Cannot publish AMQP event: M2M token is missing.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$m2mToken}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/messages/publish", [
                'routing_key' => $routingKey,
                'message' => $message,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Failed to publish AMQP event', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (Exception $e) {
            Log::error('Exception in publishAmqpEvent: ' . $e->getMessage());
        }

        return false;
    }
}
