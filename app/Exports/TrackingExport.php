<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TrackingExport implements WithMultipleSheets
{
    public function __construct(
        protected Collection $goTrucks,
        protected Collection $returnTrucks,
        protected Collection $checkpoints
    ) {
    }

    public function sheets(): array
    {
        return [
            new TrackingSheetExport($this->goTrucks, $this->checkpoints, 'GOING'),
            new TrackingSheetExport($this->returnTrucks, $this->checkpoints->reverse()->values(), 'RETURN'),
        ];
    }
}