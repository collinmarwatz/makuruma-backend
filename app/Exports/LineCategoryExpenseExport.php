<?php

namespace App\Exports;

use App\Models\ExpenseOrder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LineCategoryExpenseExport implements WithEvents
{
    public function __construct(protected ExpenseOrder $expense, protected string $category)
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

    protected function categoryLabel(): string
    {
        return match ($this->category) {
            'vibali_tunduma' => 'VIBALI TUNDUMA',
            'vibali_congo' => 'VIBALI CONGO',
            'mengine' => 'MENGINE',
            default => strtoupper($this->category),
        };
    }

    protected function build($sheet): void
    {
        $lines = $this->expense->lines->where('line_category', $this->category);

        $groups = collect();
        foreach ($lines as $line) {
            $key = $line->group_key ?? ('__single_' . $line->id);
            if (!$groups->has($key)) {
                $groups->put($key, collect());
            }
            $groups->get($key)->push($line);
        }

        $client = $this->expense->booking?->client?->company_name ?? '';
        $truckCount = $lines->pluck('booking_truck_id')->unique()->count();

        $sheet->setCellValue('A1', 'NO. ' . $this->expense->id);
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $title = $this->categoryLabel() . ' GARI ' . $truckCount . ' - ' . $this->expense->created_at->format('d.m.Y')
            . ($client ? " - {$client}" : '');
        $sheet->setCellValue('A2', $title);
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2')->getFont()->setBold(true);

        $headers = ['S/N', 'DETAILS', 'AMOUNT', 'NO OF TRUCKS', 'TOTAL'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(ord('A') + $i) . '4', $header);
        }
        $sheet->getStyle('A4:E4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A4:E4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');

        $row = 5;
        $grandTotal = 0;
        $truckListBlocks = [];

        foreach ($groups as $group) {
            $first = $group->first();
            $count = $group->count();
            $total = $group->sum('amount');
            $grandTotal += $total;

            $sheet->setCellValue('A' . $row, $row - 4);
            $sheet->setCellValue('B' . $row, $first->description);
            $sheet->setCellValue('C' . $row, $first->original_amount);
            $sheet->setCellValue('D' . $row, $count);
            $sheet->setCellValue('E' . $row, $total);

            $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E5E7EB');

            $truckListBlocks[] = [
                'label' => "{$count} TRUCKS - " . strtoupper($first->description),
                'trucks' => $group->map(fn($l) => $l->bookingTruck?->truck?->reg_no ?? '—')->values(),
            ];

            $row++;
        }

        $sheet->setCellValue('B' . $row, 'TOTAL');
        $sheet->setCellValue('E' . $row, $grandTotal);
        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
        $row += 2;

        foreach ($truckListBlocks as $block) {
            $sheet->setCellValue('A' . $row, 'S/N');
            $sheet->setCellValue('B' . $row, $block['label']);
            $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
            $row++;

            foreach ($block['trucks'] as $i => $regNo) {
                $sheet->setCellValue('A' . $row, $i + 1);
                $sheet->setCellValue('B' . $row, $regNo);
                $row++;
            }
            $row++;
        }

        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(22);
        }
    }
}