<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Annotations as OA;

class ItemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/items",
     *     tags={"User - Items"},
     *     summary="Melihat daftar barang lelang",
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"DRAFT","OPEN","CLOSED","CANCELLED"})),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $status = $request->query('status');
        $search = $request->query('search');
        $cacheKey = 'items:index:'.md5(json_encode([$page, $status, $search]));

        $items = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($status, $search) {
            return Item::query()
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->latest('auction_start_at')
                ->paginate(10);
        });

        return ItemResource::collection($items);
    }

    /**
     * @OA\Get(
     *     path="/items/{id}",
     *     tags={"User - Items"},
     *     summary="Melihat detail barang lelang",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Barang tidak ditemukan")
     * )
     */
    public function show(Item $item): ItemResource
    {
        $cachedItem = Cache::remember(
            "items:show:{$item->id}",
            now()->addMinutes(5),
            fn () => $item
        );

        return new ItemResource($cachedItem);
    }
}
