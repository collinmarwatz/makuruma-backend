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
    return ['lines', 'trip', 'truck', 'trucks', 'creator', 'approver', 'payer'];
}

    public function index()
    {
        return ExpenseOrder::with($this->eagerLoad())->latest()->get();
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'category' => 'required|in:trip,office,truck',
        'trip_id' => 'nullable|required_if:category,trip|exists:trips,id',
        'truck_id' => 'nullable|required_if:category,truck|exists:trucks,id',
        'trucks' => 'nullable|array',
        'trucks.*' => 'exists:trucks,id',
        'lines' => 'required|array|min:1',
        'lines.*.description' => 'required|string',
        'lines.*.amount' => 'required|numeric|min:0',
    ]);

    $orderNumber = $validated['category'] === 'trip'
        ? Trip::findOrFail($validated['trip_id'])->trip_number
        : ExpenseNumberGenerator::generate();

    $order = DB::transaction(function () use ($validated, $orderNumber, $request) {
        $order = ExpenseOrder::create([
            'order_number' => $orderNumber,
            'category' => $validated['category'],
            'trip_id' => $validated['category'] === 'trip' ? $validated['trip_id'] : null,
            'truck_id' => $validated['category'] === 'truck' ? $validated['truck_id'] : null,
            'status' => 'pending',
            'created_by' => $request->user()->id,
        ]);

        foreach ($validated['lines'] as $line) {
            $order->lines()->create($line);
        }

        if ($validated['category'] === 'trip' && !empty($validated['trucks'])) {
            $order->trucks()->sync($validated['trucks']);
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

        $validated = $request->validate([
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.amount' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($expenseOrder, $validated) {
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
    protected function authorizeRole(Request $request, array $allowedSlugs): void
    {
        $userRole = $request->user()->role?->slug;

        if (!in_array($userRole, $allowedSlugs)) {
            abort(403, 'You do not have permission to perform this action.');
        }
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
}