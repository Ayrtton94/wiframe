<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles
{
    public function __construct(
        protected Collection $rows
    ) {}

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Almacén',
            'Código producto',
            'Producto',
            'Rollos',
            'Metros',
            'Stock mínimo',
            'Estado',
        ];
    }

    public function map($row): array
    {
        $rollos = (float) ($row->rollos ?? 0);
        $metros = (float) ($row->metros ?? 0);
        $stockMinimo = (float) ($row->stock_minimo ?? 0);

        return [
            $row->almacen ?? '',
            $row->codigo_producto ?? '',
            $row->producto ?? '',
            $rollos,
            $metros,
            $stockMinimo,
            $metros <= $stockMinimo
                ? 'Stock bajo'
                : 'Disponible',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')
            ->getFont()
            ->setBold(true);

        $sheet->freezePane('A2');

        foreach ([
            'A' => 25,
            'B' => 20,
            'C' => 35,
            'D' => 15,
            'E' => 15,
            'F' => 18,
            'G' => 18,
        ] as $column => $width) {
            $sheet
                ->getColumnDimension($column)
                ->setWidth($width);
        }

        return [];
    }
}
