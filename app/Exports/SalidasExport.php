<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalidasExport implements FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles
{
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS
    |--------------------------------------------------------------------------
    */

    public function collection()
    {
        return $this->rows;
    }

    /*
    |--------------------------------------------------------------------------
    | ENCABEZADOS
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        return [
            'Fecha / hora',
            'Código salida',
            'Almacén',
            'Cliente',
            'Responsable',
            'Código producto',
            'Producto',
            'Cantidad',
            'Unidad',
            'Precio',
            'Total',
            'Motivo',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | MAPEO
    |--------------------------------------------------------------------------
    */

    public function map($row): array
    {
        return [
            optional($row->fecha_hora)
                ? \Carbon\Carbon::parse($row->fecha_hora)
                    ->format('d/m/Y H:i')
                : '',

            $row->salida_code ?? '',

            $row->almacen ?? '',

            $row->cliente ?? '',

            $row->responsable ?? '',

            $row->codigo_producto ?? '',

            $row->producto ?? '',

            (float) ($row->cantidad ?? 0),

            strtolower((string) ($row->unidad ?? '')) === 'kilos'
                ? 'Rollos'
                : 'Metros',

            (float) ($row->precio ?? 0),

            (float) ($row->total ?? 0),

            $row->notes ?? '',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ESTILOS
    |--------------------------------------------------------------------------
    */

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        $sheet->freezePane('A2');

        foreach ([
            'A' => 20,
            'B' => 18,
            'C' => 24,
            'D' => 28,
            'E' => 25,
            'F' => 18,
            'G' => 35,
            'H' => 14,
            'I' => 14,
            'J' => 14,
            'K' => 16,
            'L' => 35,
        ] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet
            ->getStyle('H2:H' . ($this->rows->count() + 1))
            ->getNumberFormat()
            ->setFormatCode('0.###');

        $sheet
            ->getStyle('J2:K' . ($this->rows->count() + 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        return [];
    }
}
