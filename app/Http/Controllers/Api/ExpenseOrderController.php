<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ExpenseOrder;
use App\Services\ExpenseNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ExpenseOrderExport;
use App\Exports\FuelExpenseExport;
use App\Exports\LineCategoryExpenseExport;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseOrderController extends Controller
{
    protected function eagerLoad()
    {
        return ['lines.vendor', 'lines.bookingTruck.truck', 'lines.bookingTruck.trip', 'booking.client', 'truck', 'creator', 'approver', 'payer'];
    }

    protected function lineRules(): array
    {
        return [
            'lines' => 'required|array|min:1',
            'lines.*.line_category' => 'required|in:fuel,vibali_tunduma,vibali_congo,mengine',
            'lines.*.vendor_id' => 'nullable|exists:vendors,id',
            'lines.*.booking_truck_id' => 'nullable|exists:booking_trucks,id',
            'lines.*.group_key' => 'nullable|string',
            'lines.*.description' => 'required|string',
            'lines.*.currency' => 'required|in:TZS,USD,ZMK',
            'lines.*.exchange_rate' => 'required_unless:lines.*.currency,TZS|numeric|min:0.0001',
            'lines.*.quantity' => 'nullable|numeric|min:0',
            'lines.*.unit_rate' => 'nullable|numeric|min:0',
            'lines.*.original_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function index(Request $request)
    {
        $query = ExpenseOrder::with($this->eagerLoad());

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge([
            'reference_no' => 'required|string|unique:expense_orders,reference_no',
            'category' => 'required|in:trip,office,truck',
            'booking_id' => 'nullable|exists:bookings,id',
            'truck_id' => 'nullable|exists:trucks,id',
            'payment_account' => 'nullable|string',
            'initiated_by' => 'nullable|string',
            'payment_date' => 'nullable|date',
        ], $this->lineRules()));

        if ($validated['category'] === 'trip' && empty($validated['booking_id'])) {
            return response()->json(['message' => 'A booking must be selected for Trip category expenses.'], 422);
        }

        $orderNumber = $validated['category'] === 'trip'
            ? Booking::findOrFail($validated['booking_id'])->booking_number
            : ExpenseNumberGenerator::generate();

        $order = DB::transaction(function () use ($validated, $orderNumber, $request) {
            $order = ExpenseOrder::create([
                'order_number' => $orderNumber,
                'reference_no' => $validated['reference_no'],
                'category' => $validated['category'],
                'booking_id' => $validated['booking_id'] ?? null,
                'truck_id' => $validated['truck_id'] ?? null,
                'status' => 'pending',
                'created_by' => $request->user()->id,
                'payment_account' => $validated['payment_account'] ?? null,
                'initiated_by' => $validated['initiated_by'] ?? null,
                'payment_date' => $validated['payment_date'] ?? null,
            ]);

            foreach ($validated['lines'] as $line) {
                $order->lines()->create($line);
            }

            $order->recalculateTotal();

            return $order;
        });

        return response()->json($order->load($this->eagerLoad()), 201);
    }

    public function show(ExpenseOrder $expenseOrder)
    {
        return $expenseOrder->load($this->eagerLoad());
    }

    public function update(Request $request, ExpenseOrder $expenseOrder)
    {
        if ($expenseOrder->status !== 'pending') {
            return response()->json(['message' => 'Only pending expense orders can be edited.'], 422);
        }

        $validated = $request->validate(array_merge([
            'reference_no' => 'required|string|unique:expense_orders,reference_no,' . $expenseOrder->id,
            'payment_account' => 'nullable|string',
            'initiated_by' => 'nullable|string',
            'payment_date' => 'nullable|date',
        ], $this->lineRules()));

        DB::transaction(function () use ($expenseOrder, $validated) {
            $expenseOrder->update([
                'reference_no' => $validated['reference_no'],
                'payment_account' => $validated['payment_account'] ?? null,
                'initiated_by' => $validated['initiated_by'] ?? null,
                'payment_date' => $validated['payment_date'] ?? null,
            ]);

            $expenseOrder->lines()->delete();
            foreach ($validated['lines'] as $line) {
                $expenseOrder->lines()->create($line);
            }
            $expenseOrder->recalculateTotal();
        });

        return $expenseOrder->load($this->eagerLoad());
    }

    public function approve(Request $request, ExpenseOrder $expenseOrder)
    {
        $this->authorizeRole($request, ['manager', 'admin']);

        if ($expenseOrder->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be approved.'], 422);
        }

        $expenseOrder->update(['status' => 'approved', 'approved_by' => $request->user()->id, 'approved_at' => now()]);

        return $expenseOrder->load($this->eagerLoad());
    }

    public function reject(Request $request, ExpenseOrder $expenseOrder)
    {
        $this->authorizeRole($request, ['manager', 'admin']);

        if ($expenseOrder->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be rejected.'], 422);
        }

        $expenseOrder->update(['status' => 'rejected', 'approved_by' => $request->user()->id, 'approved_at' => now()]);

        return $expenseOrder->load($this->eagerLoad());
    }

    public function markPaid(Request $request, ExpenseOrder $expenseOrder)
    {
        $this->authorizeRole($request, ['accountant', 'admin']);

        if ($expenseOrder->status !== 'approved') {
            return response()->json(['message' => 'Only approved orders can be marked as paid.'], 422);
        }

        $expenseOrder->update(['status' => 'paid', 'paid_by' => $request->user()->id, 'paid_at' => now()]);

        return $expenseOrder->load($this->eagerLoad());
    }

    public function destroy(Request $request, ExpenseOrder $expenseOrder)
    {
        if ($expenseOrder->status !== 'pending') {
            $this->authorizeRole($request, ['manager', 'admin']);
        }

        $expenseOrder->delete();

        return response()->json(null, 204);
    }

    public function download(ExpenseOrder $expenseOrder)
    {
        $expenseOrder->load($this->eagerLoad());

        $logoPath = public_path('images/logo.png');
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

        $pdf = Pdf::loadView('expenses.order-pdf', ['expense' => $expenseOrder, 'logoBase64' => $logoBase64]);

        return $pdf->download("expense-{$expenseOrder->order_number}.pdf");
    }

    public function downloadExcel(ExpenseOrder $expenseOrder)
    {
        $expenseOrder->load($this->eagerLoad());

        return Excel::download(new ExpenseOrderExport($expenseOrder), "expense-{$expenseOrder->order_number}.xlsx");
    }

    public function downloadCategory(ExpenseOrder $expenseOrder, string $category)
    {
        $expenseOrder->load($this->eagerLoad());

        if (!in_array($category, ['fuel', 'vibali_tunduma', 'vibali_congo', 'mengine'])) {
            abort(404);
        }

        $hasLines = $expenseOrder->lines->where('line_category', $category)->isNotEmpty();
        if (!$hasLines) {
            return response()->json(['message' => 'No lines of this category on this order.'], 404);
        }

        $export = $category === 'fuel'
            ? new FuelExpenseExport($expenseOrder)
            : new LineCategoryExpenseExport($expenseOrder, $category);

        return Excel::download($export, "{$expenseOrder->order_number}-{$category}.xlsx");
    }

    protected function authorizeRole(Request $request, array $allowedSlugs): void
    {
        $userRole = $request->user()->role?->slug;

        if (!in_array($userRole, $allowedSlugs)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }
}