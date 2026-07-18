<?php

namespace App\Exports;

use App\Models\ExpenseOrder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FuelExpenseExport implements WithEvents
{
    public function __construct(protected ExpenseOrder $expense)
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
        $fuelLines = $this->expense->lines->where('line_category', 'fuel')->values();

        $vendor = $fuelLines->first()?->vendor?->company_name ?? '';
        $location = $this->expense->booking?->loading_point ?? '';
        $client = $this->expense->booking?->client?->company_name ?? '';

        $sheet->setCellValue('A1', 'NO: ' . $this->expense->id);
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $title = 'MAFUTA ' . strtoupper($vendor ?: $location) . ' ' . $fuelLines->count()
            . ' - ' . $this->expense->created_at->format('d.m.Y')
            . ($client ? " ({$client})" : '');
        $sheet->setCellValue('A2', $title);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getFont()->setBold(true);

        $headers = ['S/N', 'TRUCKS', 'DRIVERS NAME', 'LITRES'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(ord('A') + $i) . '4', $header);
        }
        $sheet->getStyle('A4:D4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A4:D4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');

        $row = 5;
        $totalLitres = 0;
        $rate = null;

        foreach ($fuelLines as $index => $line) {
            $truck = $line->bookingTruck?->truck;
            $driver = $line->bookingTruck?->driver;
            $litres = (float) ($line->quantity ?? 0);
            $totalLitres += $litres;
            $rate = $rate ?? $line->unit_rate;

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $truck?->reg_no ?? '—');
            $sheet->setCellValue('C' . $row, $driver?->full_name ?? '—');
            $sheet->setCellValue('D' . $row, $litres);

            $sheet->getStyle("A{$row}:D{$row}")->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E5E7EB');

            $row++;
        }

        $sheet->setCellValue('B' . $row, 'TOTAL');
        $sheet->setCellValue('D' . $row, $totalLitres);
        $sheet->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('C' . $row, 'RATE');
        $sheet->setCellValue('D' . $row, $rate ?? 0);
        $row++;

        $sheet->setCellValue('C' . $row, 'AMOUNTS');
        $sheet->setCellValue('D' . $row, $totalLitres * ($rate ?? 0));
        $sheet->getStyle("C{$row}:D{$row}")->getFont()->setBold(true);

        foreach (['A', 'B', 'C', 'D'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }
    }
}