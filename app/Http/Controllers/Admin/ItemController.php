<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Services\IaeCentralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use OpenApi\Annotations as OA;

class ItemController extends Controller
{
    protected IaeCentralService $iaeService;

    public function __construct(IaeCentralService $iaeService)
    {
        $this->iaeService = $iaeService;
    }

    /**
     * @OA\Post(
     *     path="/v1/items",
     *     tags={"User - Items"},
     *     summary="Membuat barang lelang baru",
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","base_price","auction_start_at","auction_end_at","status"},
     *             @OA\Property(property="name", type="string", example="Baju Band"),
     *             @OA\Property(property="description", type="string", example="Deskripsi"),
     *             @OA\Property(property="base_price", type="number", format="float", example=50000),
     *             @OA\Property(property="auction_start_at", type="string", format="date-time", example="2026-06-25T10:00:00Z"),
     *             @OA\Property(property="auction_end_at", type="string", format="date-time", example="2026-06-30T10:00:00Z"),
     *             @OA\Property(property="status", type="string", example="OPEN")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent())
     * )
     */
    public function store(StoreItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['current_price'] = $data['current_price'] ?? $data['base_price'];

        // 1. SOAP XML Audit Call (pre-persistence)
        $receiptNumber = $this->iaeService->sendSoapAudit('ItemCreated', $data);
        if ($receiptNumber) {
            $data['receipt_number'] = $receiptNumber;
        }

        // 2. Persist to local database
        $item = Item::query()->create($data);
        $this->clearItemCache($item);

        // 3. Publish event to RabbitMQ (AMQP)
        $this->iaeService->publishAmqpEvent('item.created', [
            'event' => 'item.created',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'base_price' => $item->base_price,
                'current_price' => $item->current_price,
                'auction_start_at' => $item->auction_start_at?->toIso8601String(),
                'auction_end_at' => $item->auction_end_at?->toIso8601String(),
                'status' => $item->status,
                'receipt_number' => $item->receipt_number,
            ]
        ]);

        return (new ItemResource($item))
            ->additional([
                'status' => 'success',
                'message' => 'Data created successfully',
                'meta' => [
                    'service_name' => 'Catalog-Service',
                    'api_version' => 'v1'
                ]
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateItemRequest $request, Item $item): ItemResource
    {
        $data = $request->validated();

        // 1. SOAP XML Audit Call
        $receiptNumber = $this->iaeService->sendSoapAudit('ItemUpdated', array_merge(['id' => $item->id], $data));
        if ($receiptNumber) {
            $data['receipt_number'] = $receiptNumber;
        }

        // 2. Persist update
        $item->update($data);
        $this->clearItemCache($item);

        // 3. Publish event to RabbitMQ
        $this->iaeService->publishAmqpEvent('item.updated', [
            'event' => 'item.updated',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'base_price' => $item->base_price,
                'current_price' => $item->current_price,
                'auction_start_at' => $item->auction_start_at?->toIso8601String(),
                'auction_end_at' => $item->auction_end_at?->toIso8601String(),
                'status' => $item->status,
                'receipt_number' => $item->receipt_number,
            ]
        ]);

        return (new ItemResource($item->refresh()))
            ->additional([
                'status' => 'success',
                'message' => 'Data updated successfully',
                'meta' => [
                    'service_name' => 'Catalog-Service',
                    'api_version' => 'v1'
                ]
            ]);
    }

    public function destroy(Item $item): JsonResponse
    {
        // 1. SOAP XML Audit Call
        $this->iaeService->sendSoapAudit('ItemDeleted', ['id' => $item->id, 'name' => $item->name]);

        // 2. Delete
        $item->delete();
        $this->clearItemCache($item);

        // 3. Publish event to RabbitMQ
        $this->iaeService->publishAmqpEvent('item.deleted', [
            'event' => 'item.deleted',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'id' => $item->id,
                'name' => $item->name,
            ]
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Barang lelang berhasil dihapus.',
            'data' => null,
            'meta' => [
                'service_name' => 'Catalog-Service',
                'api_version' => 'v1'
            ]
        ]);
    }

    private function clearItemCache(Item $item): void
    {
        Cache::forget("items:show:{$item->id}");
        Cache::flush();
    }
}

