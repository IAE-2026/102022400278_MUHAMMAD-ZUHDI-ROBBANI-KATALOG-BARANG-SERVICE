<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Service A - Katalog Barang API",
 *     version="1.0.0",
 *     description="REST API untuk katalog barang lelang. User dapat membaca katalog, admin dapat mengelola barang."
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8080/api",
 *     description="Docker local"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="ApiKeyAuth",
 *     type="apiKey",
 *     in="header",
 *     name="X-IAE-KEY",
 *     description="Masukkan NIM Anda sebagai API Key (102022400278)"
 * )
 */
class OpenApiSpec
{
}
