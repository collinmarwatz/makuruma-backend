<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ExpenseLine;
use App\Models\Invoice;
use App\Models\Vendor;
use App\Models\VendorPayment;
use App\Exports\ClientStatementExport;
use App\Exports\VendorLedgerExport;
use Maatwebsite\Excel\Facades\Excel;

class ReconciliationController extends Controller
{
    /**
     * Live preview data for the Reconciliation page — same numbers
     * the downloadable statement will show.
     */
    public function clientSummary(Client $client)
    {
        $invoices = Invoice::where('client_id', $client->id)->with('lines')->orderBy('invoice_date')->get();

        $totalInvoiced = (float) $invoices->sum('total_amount');
        $totalPaid = (float) $invoices->where('status', 'paid')->sum('total_amount');

        return response()->json([
            'client' => $client->company_name,
            'total_invoiced' => round($totalInvoiced, 2),
            'total_paid' => round($totalPaid, 2),
            'outstanding' => round($totalInvoiced - $totalPaid, 2),
            'invoice_count' => $invoices->count(),
        ]);
    }

    /**
     * Live preview data for the Reconciliation page — same numbers
     * the downloadable ledger will show.
     */
    public function vendorSummary(Vendor $vendor)
    {
        $totalDebt = (float) ExpenseLine::where('vendor_id', $vendor->id)->sum('amount');
        $totalPaid = (float) VendorPayment::where('vendor_id', $vendor->id)->sum('amount');

        return response()->json([
            'vendor' => $vendor->company_name,
            'total_debt' => round($totalDebt, 2),
            'total_paid' => round($totalPaid, 2),
            'balance' => round($totalDebt - $totalPaid, 2),
        ]);
    }

    public function downloadClientStatement(Client $client)
    {
        return Excel::download(new ClientStatementExport($client), "statement-{$client->company_name}.xlsx");
    }

    public function downloadVendorLedger(Vendor $vendor)
    {
        return Excel::download(new VendorLedgerExport($vendor), "vendor-ledger-{$vendor->company_name}.xlsx");
    }
}