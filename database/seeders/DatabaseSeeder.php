<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Item;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        ApiKey::query()->updateOrCreate(
            ['name' => 'Local Admin Key'],
            [
                'key_hash' => hash('sha256', 'local-admin-key'),
                'abilities' => ['admin'],
            ]
        );
    }
}
