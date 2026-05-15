<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use OpenApi\Annotations as OA;

class ItemController extends Controller
{
    public function store(StoreItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['current_price'] = $data['current_price'] ?? $data['base_price'];

        $item = Item::query()->create($data);
        $this->clearItemCache($item);

        return (new ItemResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateItemRequest $request, Item $item): ItemResource
    {
        $item->update($request->validated());
        $this->clearItemCache($item);

        return new ItemResource($item->refresh());
    }

    public function destroy(Item $item): JsonResponse
    {
        $item->delete();
        $this->clearItemCache($item);

        return response()->json([
            'message' => 'Barang lelang berhasil dihapus.',
        ]);
    }

    private function clearItemCache(Item $item): void
    {
        Cache::forget("items:show:{$item->id}");
        Cache::flush();
    }
}
