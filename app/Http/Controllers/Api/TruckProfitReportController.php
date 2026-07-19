<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use App\Exports\TruckProfitReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TruckProfitReportController extends Controller
{
    public function downloadExcel(Request $request, Truck $truck)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        return Excel::download(
            new TruckProfitReportExport($truck, (int) $validated['year']),
            "{$truck->reg_no}-profit-report-{$validated['year']}.xlsx"
        );
    }
}