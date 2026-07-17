<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingTruck;
use App\Models\ExpenseLine;
use App\Models\ExpenseOrder;
use App\Models\Trip;
use App\Models\Truck;
use Illuminate\Http\Request;

class TruckProfitReportController extends Controller
{
    protected function buildTripBreakdown(Truck $truck, Trip $trip, string $legFilter = 'both'): array
    {
        $legs = $trip->legs()
            ->when($legFilter === 'go', fn($q) => $q->where('direction', 'go'))
            ->get();

        $legBreakdowns = [];
        $tripRevenue = 0;
        $tripExpenses = 0;

        foreach ($legs as $leg) {
            $bookingTruck = $leg->bookingTrucks()->where('truck_id', $truck->id)->first();
            if (!$bookingTruck)
                continue;

            $expenses = ExpenseLine::where('booking_truck_id', $bookingTruck->id)->get();
            $expensesByCategory = $expenses->groupBy('line_category')->map(fn($group) => [
                'lines' => $group->values(),
                'total' => $group->sum('amount'),
            ]);

            $legRevenue = $bookingTruck->revenue_tzs;
            $legExpenseTotal = $expenses->sum('amount');

            $tripRevenue += $legRevenue;
            $tripExpenses += $legExpenseTotal;

            $legBreakdowns[] = [
                'direction' => $leg->direction,
                'client' => $leg->client?->company_name,
                'loading_point' => $leg->loading_point,
                'offloading_point' => $leg->offloading_point,
                'actual_loading_date' => $bookingTruck->actual_loading_date,
                'actual_offloading_date' => $bookingTruck->actual_offloading_date,
                'cargo' => $bookingTruck->cargo,
                'rate' => $bookingTruck->rate,
                'quantity' => $bookingTruck->quantity,
                'amount_usd' => $bookingTruck->amount,
                'exchange_rate' => $bookingTruck->exchange_rate,
                'revenue_tzs' => $legRevenue,
                'expenses_by_category' => $expensesByCategory,
                'expense_total' => $legExpenseTotal,
            ];
        }

        return [
            'trip_number' => $trip->trip_number,
            'legs' => $legBreakdowns,
            'revenue_tzs' => $tripRevenue,
            'expense_total' => $tripExpenses,
            'profit' => $tripRevenue - $tripExpenses,
        ];
    }

    public function tripReport(Request $request, Truck $truck)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'leg' => 'nullable|in:go,both',
        ]);

        $trip = Trip::with('legs.bookingTrucks.truck')->findOrFail($validated['trip_id']);

        return response()->json(
            $this->buildTripBreakdown($truck, $trip, $validated['leg'] ?? 'both')
        );
    }

    public function annualReport(Request $request, Truck $truck)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $year = $validated['year'];

        $tripIds = BookingTruck::where('truck_id', $truck->id)
            ->whereHas('tripLeg.trip', fn($q) => $q->whereYear('created_at', $year))
            ->with('tripLeg.trip')
            ->get()
            ->pluck('tripLeg.trip.id')
            ->unique();

        $tripBreakdowns = [];
        $totalRevenue = 0;
        $totalTripExpenses = 0;

        foreach ($tripIds as $tripId) {
            $trip = Trip::with('legs.bookingTrucks.truck')->find($tripId);
            if (!$trip)
                continue;

            $breakdown = $this->buildTripBreakdown($truck, $trip, 'both');
            $tripBreakdowns[] = $breakdown;
            $totalRevenue += $breakdown['revenue_tzs'];
            $totalTripExpenses += $breakdown['expense_total'];
        }

        $annualCosts = ExpenseOrder::where('category', 'truck')
            ->where('truck_id', $truck->id)
            ->whereYear('created_at', $year)
            ->with('lines')
            ->get()
            ->flatMap(fn($order) => $order->lines);

        $annualCostsTotal = $annualCosts->sum('amount');

        $officeTotal = ExpenseOrder::where('category', 'office')
            ->whereYear('created_at', $year)
            ->with('lines')
            ->get()
            ->flatMap(fn($order) => $order->lines)
            ->sum('amount');

        $activeTruckCount = max(Truck::where('status', 'active')->count(), 1);
        $overheadShare = round($officeTotal / $activeTruckCount, 2);

        $totalProfit = $totalRevenue - $totalTripExpenses - $annualCostsTotal - $overheadShare;

        return response()->json([
            'truck' => $truck->reg_no,
            'year' => $year,
            'trips' => $tripBreakdowns,
            'trip_revenue_total' => $totalRevenue,
            'trip_expense_total' => $totalTripExpenses,
            'annual_costs' => $annualCosts->values(),
            'annual_costs_total' => $annualCostsTotal,
            'company_overhead_total' => $officeTotal,
            'active_truck_count' => $activeTruckCount,
            'overhead_share' => $overheadShare,
            'total_profit' => $totalProfit,
        ]);
    }
}