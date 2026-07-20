<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\Invoice;
use App\Services\StatementNumberGenerator;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClientStatementExport implements WithEvents
{
    public function __construct(protected Client $client)
    {
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->build($event->sheet->getDelegate());
            },
        ];
    }

    protected function build($sheet): void
    {
        $invoices = Invoice::where('client_id', $this->client->id)
            ->with('lines')
            ->orderBy('invoice_date')
            ->get();

        $statementNumber = StatementNumberGenerator::generate();

        $sheet->setCellValue('A1', 'STATEMENT');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'NUMBER');
        $sheet->setCellValue('B2', $statementNumber);

        $sheet->setCellValue('A3', 'DATED');
        $sheet->setCellValue('B3', now()->format('d.m.Y'));

        $sheet->setCellValue('A4', 'CUSTOMER ID');
        $sheet->setCellValue('B4', 'C' . str_pad($this->client->id, 4, '0', STR_PAD_LEFT));

        $sheet->setCellValue('A5', 'FROM');
        $sheet->setCellValue('D5', 'TO');

        $sheet->setCellValue('A6', 'POSTAL ADDRESS');
        $sheet->setCellValue('D6', 'POSTAL ADDRESS');

        $sheet->setCellValue('A7', 'MAKURUMA LOGISTICS LTD');
        $sheet->setCellValue('D7', $this->client->company_name);

        $sheet->setCellValue('A8', 'P.O.BOX 31902,');
        $sheet->setCellValue('A9', 'KINONDONI,');
        $sheet->setCellValue('A10', 'DAR ES SALAAM.');
        $sheet->setCellValue('A11', 'TANZANIA');

        $totalInvoiced = (float) $invoices->sum('total_amount');
        $totalPaid = (float) $invoices->where('status', 'paid')->sum('total_amount');
        $outstanding = round($totalInvoiced - $totalPaid, 2);

        $sheet->setCellValue('A12', 'REMITTANCE AMOUNT ENCLOSED');
        $sheet->setCellValue('C12', $outstanding);
        $sheet->setCellValue('D12', 'USD');
        $sheet->getStyle('A12:D12')->getFont()->setBold(true);

        $headerRow = 14;
        $headers = ['DATE', 'DETAILS', 'INVOICE', 'PO', 'AMOUNT($)', 'PAID AMOUNT', 'DATE PAID'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(ord('A') + $i) . $headerRow, $h);
        }
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');

        $row = $headerRow + 1;
        foreach ($invoices as $invoice) {
            $truckCount = $invoice->lines->count();
            $typeLabel = strtoupper(str_replace('_', ' ', $invoice->invoice_type));
            $details = "{$typeLabel} {$truckCount} TRUCKS";

            $sheet->setCellValue("A{$row}", $invoice->invoice_date->format('d.m.Y'));
            $sheet->setCellValue("B{$row}", $details);
            $sheet->setCellValue("C{$row}", $invoice->invoice_number);
            $sheet->setCellValue("D{$row}", $invoice->purchase_order_no ?? '');
            $sheet->setCellValue("E{$row}", (float) $invoice->total_amount);

            if ($invoice->status === 'paid') {
                $sheet->setCellValue("F{$row}", -(float) $invoice->total_amount);
                $sheet->setCellValue("G{$row}", $invoice->paid_at?->format('d.m.Y'));
            }

            $row++;
        }

        $sheet->setCellValue("E{$row}", $totalInvoiced);
        $sheet->setCellValue("F{$row}", -$totalPaid);
        $sheet->getStyle("E{$row}:F{$row}")->getFont()->setBold(true);
        $row += 2;

        $sheet->setCellValue("B{$row}", 'REMITTANCE AMOUNT ENCLOSED');
        $sheet->setCellValue("F{$row}", $outstanding);
        $sheet->getStyle("B{$row}:F{$row}")->getFont()->setBold(true);
        $row += 2;

        $sheet->setCellValue("A{$row}", 'REMINDER:');
        $sheet->setCellValue("B{$row}", 'Please include statement number in your swift.');

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(18);
        }
        $sheet->getColumnDimension('B')->setWidth(28);
    }
}