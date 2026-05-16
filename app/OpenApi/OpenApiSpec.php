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
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="API Key",
 *     description="Masukkan API key admin sebagai Bearer token."
 * )
 */
class OpenApiSpec
{
}
