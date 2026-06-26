<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('base_price', 15, 2);
            $table->decimal('current_price', 15, 2)->default(0);
            $table->dateTime('auction_start_at');
            $table->dateTime('auction_end_at');
            $table->enum('status', ['DRAFT', 'OPEN', 'CLOSED', 'CANCELLED'])->default('DRAFT');
            $table->timestamps();

            $table->index(['status', 'auction_end_at']);
            $table->index('auction_start_at');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
