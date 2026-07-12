<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Truck;
use Illuminate\Database\Seeder;

class FleetSeeder extends Seeder
{
    public function run(): void
    {
        $trailers = collect([
            ['reg_no' => 'TR-1001'],
            ['reg_no' => 'TR-1002'],
            ['reg_no' => 'TR-1003'],
        ])->map(fn ($data) => Trailer::firstOrCreate($data));

        $drivers = collect([
            ['full_name' => 'John Mwita', 'phone' => '0712000001'],
            ['full_name' => 'Peter Komba', 'phone' => '0712000002'],
            ['full_name' => 'Ally Hassan', 'phone' => '0712000003'],
        ])->map(fn ($data) => Driver::firstOrCreate($data));

        $trucks = [
            ['reg_no' => 'T111AAA', 'capacity' => 30, 'trailer_id' => $trailers[0]->id, 'driver_id' => $drivers[0]->id],
            ['reg_no' => 'T222BBB', 'capacity' => 28, 'trailer_id' => $trailers[1]->id, 'driver_id' => $drivers[1]->id],
            ['reg_no' => 'T333CCC', 'capacity' => 32, 'trailer_id' => $trailers[2]->id, 'driver_id' => $drivers[2]->id],
        ];

        foreach ($trucks as $truckData) {
            Truck::firstOrCreate(['reg_no' => $truckData['reg_no']], $truckData);
        }
    }
}