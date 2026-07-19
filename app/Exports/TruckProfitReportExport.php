<?php

namespace App\Exports;

use App\Models\Truck;
use App\Services\TripProfitCalculator;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TruckProfitReportExport implements WithMultipleSheets
{
    public function __construct(protected Truck $truck, protected int $year)
    {
    }

    public function sheets(): array
    {
        $trips = $this->truck->trips()
            ->whereHas('goBookingTruck.booking', function ($q) {
                $q->whereYear('created_at', $this->year);
            })
            ->with(['goBookingTruck.booking.client', 'returnBookingTruck.booking.client'])
            ->orderBy('id')
            ->get();

        $sheets = [];
        $tripProfits = [];

        foreach ($trips as $index => $trip) {
            $sheets[] = new TripSheetExport($trip, $index + 1);
            $tripProfits[] = TripProfitCalculator::tripProfit($trip);
        }

        $sheets[] = new AnnualSheetExport($this->truck, $this->year, $tripProfits);

        return $sheets;
    }
}