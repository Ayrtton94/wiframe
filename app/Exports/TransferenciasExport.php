<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransferenciasExport implements 

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
            'Fecha solicitud',
            'Código',
            'Origen',
            'Destino',
            'Solicitado por',
            'Despachado por',
            'Recibido por',
            'Código producto',
            'Producto',
            'Rollos solicitados',
            'Rollos despachados',
            'Rollos recibidos',
            'Metros solicitados',
            'Metros despachados',
            'Metros recibidos',
            'Estado',
            'Fecha despacho',
            'Fecha recepción',
        ];
    }

    public function map($row): array
    {
        return [
            $row->fecha_solicitud
                ? \Carbon\Carbon::parse(
                    $row->fecha_solicitud
                )->format('d/m/Y H:i')
                : '',

            $row->transferencia_code ?? '',
            $row->almacen_origen ?? '',
            $row->almacen_destino ?? '',
            $row->solicitado_por ?? '',
            $row->despachado_por ?? '',
            $row->recibido_por ?? '',
            $row->codigo_producto ?? '',
            $row->producto ?? '',

            (float) ($row->rollos_solicitados ?? 0),
            (float) ($row->rollos_despachados ?? 0),
            (float) ($row->rollos_recibidos ?? 0),

            (float) ($row->metros_solicitados ?? 0),
            (float) ($row->metros_despachados ?? 0),
            (float) ($row->metros_recibidos ?? 0),

            match ($row->status ?? '') {
                'requested' => 'Solicitada',
                'shipped' => 'Despachada',
                'received' => 'Recibida',
                'cancelled' => 'Cancelada',
                default => $row->status ?? '',
            },

            $row->fecha_despacho
                ? \Carbon\Carbon::parse(
                    $row->fecha_despacho
                )->format('d/m/Y H:i')
                : '',

            $row->fecha_recepcion
                ? \Carbon\Carbon::parse(
                    $row->fecha_recepcion
                )->format('d/m/Y H:i')
                : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:R1')
            ->getFont()
            ->setBold(true);

        $sheet->freezePane('A2');

        foreach ([
            'A' => 20,
            'B' => 18,
            'C' => 25,
            'D' => 25,
            'E' => 24,
            'F' => 24,
            'G' => 24,
            'H' => 18,
            'I' => 35,
            'J' => 18,
            'K' => 19,
            'L' => 18,
            'M' => 19,
            'N' => 20,
            'O' => 19,
            'P' => 16,
            'Q' => 20,
            'R' => 20,
        ] as $column => $width) {
            $sheet
                ->getColumnDimension($column)
                ->setWidth($width);
        }

        return [];
    }
}
