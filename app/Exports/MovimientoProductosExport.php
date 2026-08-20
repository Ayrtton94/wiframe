<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovimientoProductosExport implements  
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
            'Código',
            'Producto',
            'Almacén',
            'Unidad',
            'Saldo inicial',
            'Ingresos',
            'Salidas',
            'Transferencias recibidas',
            'Transferencias enviadas',
            'Saldo actual',
        ];
    }

    public function map($row): array
    {
        $saldoInicial = (float) ($row['saldo_inicial'] ?? 0);
        $ingresos = (float) ($row['ingresos'] ?? 0);
        $salidas = (float) ($row['salidas'] ?? 0);
        $recibidas = (float) ($row['transferencias_recibidas'] ?? 0);
        $enviadas = (float) ($row['transferencias_enviadas'] ?? 0);
        $saldoActual = (float) ($row['saldo_actual'] ?? 0);

        return [
            $row['codigo_producto'] ?? '',
            $row['producto'] ?? '',
            $row['almacen'] ?? '',

            $row['unidad'] === 'ROLLOS'
                ? 'Rollos'
                : 'Metros',

            $saldoInicial,
            $ingresos,
            $salidas,
            $recibidas,
            $enviadas,
            $saldoActual,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        $sheet->freezePane('A2');

        foreach ([
            'A' => 18,
            'B' => 35,
            'C' => 25,
            'D' => 14,
            'E' => 16,
            'F' => 14,
            'G' => 14,
            'H' => 24,
            'I' => 24,
            'J' => 16,
        ] as $column => $width) {
            $sheet
                ->getColumnDimension($column)
                ->setWidth($width);
        }

        return [];
    }
}