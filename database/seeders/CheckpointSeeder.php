<?php

namespace Database\Seeders;

use App\Models\Checkpoint;
use Illuminate\Database\Seeder;

class CheckpointSeeder extends Seeder
{
    public function run(): void
    {
        $checkpoints = [
            ['name' => 'Tunduma', 'sequence_order' => 1],
            ['name' => 'Nakonde', 'sequence_order' => 2],
            ['name' => 'Kasumbalesa/Sakania (Zambia)', 'sequence_order' => 3],
            ['name' => 'Kasumbalesa/Sakania (DRC)', 'sequence_order' => 4],
            ['name' => 'Whisky', 'sequence_order' => 5],
        ];

        foreach ($checkpoints as $cp) {
            Checkpoint::firstOrCreate(['name' => $cp['name']], $cp);
        }
    }
}