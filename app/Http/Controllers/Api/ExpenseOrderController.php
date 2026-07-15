<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseOrder;
use App\Models\Trip;
use App\Services\ExpenseNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ExpenseOrderController extends Controller
{
    protected function eagerLoad()
    {
        return ['lines.vendor', 'lines.bookingTruck.truck', 'trip', 'truck', 'creator', 'approver', 'payer'];
    }

    protected function lineRules(): array
    {
        return [
            'lines' => 'required|array|min:1',
            'lines.*.line_category' => 'required|in:fuel,vibali_tunduma,vibali_congo,mengine',
            'lines.*.vendor_id' => 'nullable|exists:vendors,id',
            'lines.*.booking_truck_id' => 'nullable|exists:booking_trucks,id',
            'lines.*.description' => 'required|string',
            'lines.*.currency' => 'required|in:TZS,USD,ZMK',
            'lines.*.exchange_rate' => 'required_unless:lines.*.currency,TZS|numeric|min:0.0001',
            'lines.*.original_amount' => 'required|numeric|min:0',
            'lines.*.group_key' => 'nullable|string',
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
            'category' => 'required|in:trip,office,truck',
            'trip_id' => 'nullable|exists:trips,id',
            'truck_id' => 'nullable|exists:trucks,id',
            'payment_account' => 'nullable|string',
            'initiated_by' => 'nullable|string',
            'payment_date' => 'nullable|date',
        ], $this->lineRules()));

        if ($validated['category'] === 'trip' && empty($validated['trip_id'])) {
            return response()->json(['message' => 'A trip must be selected for Trip category expenses.'], 422);
        }

        $orderNumber = $validated['category'] === 'trip'
            ? Trip::findOrFail($validated['trip_id'])->trip_number
            : ExpenseNumberGenerator::generate();

        $order = DB::transaction(function () use ($validated, $orderNumber, $request) {
            $order = ExpenseOrder::create([
                'order_number' => $orderNumber,
                'category' => $validated['category'],
                'trip_id' => $validated['trip_id'] ?? null,
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
            'payment_account' => 'nullable|string',
            'initiated_by' => 'nullable|string',
            'payment_date' => 'nullable|date',
        ], $this->lineRules()));

        DB::transaction(function () use ($expenseOrder, $validated) {
            $expenseOrder->update([
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

        $expenseOrder->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return $expenseOrder->load($this->eagerLoad());
    }

    public function reject(Request $request, ExpenseOrder $expenseOrder)
    {
        $this->authorizeRole($request, ['manager', 'admin']);

        if ($expenseOrder->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be rejected.'], 422);
        }

        $expenseOrder->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return $expenseOrder->load($this->eagerLoad());
    }

    public function markPaid(Request $request, ExpenseOrder $expenseOrder)
    {
        $this->authorizeRole($request, ['accountant', 'admin']);

        if ($expenseOrder->status !== 'approved') {
            return response()->json(['message' => 'Only approved orders can be marked as paid.'], 422);
        }

        $expenseOrder->update([
            'status' => 'paid',
            'paid_by' => $request->user()->id,
            'paid_at' => now(),
        ]);

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

        $pdf = Pdf::loadView('expenses.order-pdf', [
            'expense' => $expenseOrder,
            'logoBase64' => $logoBase64,
        ]);

        return $pdf->download("expense-{$expenseOrder->order_number}.pdf");
    }

    protected function authorizeRole(Request $request, array $allowedSlugs): void
    {
        $userRole = $request->user()->role?->slug;

        if (!in_array($userRole, $allowedSlugs)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }
}