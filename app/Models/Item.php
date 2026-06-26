<?php

namespace App\Models;

use App\Enums\ItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'current_price',
        'auction_start_at',
        'auction_end_at',
        'status',
        'receipt_number',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'auction_start_at' => 'datetime',
        'auction_end_at' => 'datetime',
        'status' => ItemStatus::class,
    ];
}
