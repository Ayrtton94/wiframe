<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovimientoProductosExport implements
    FromCollection,
    WithEvents,
    WithStyles,
    WithColumnWidths
{
    protected Collection $rows;

    protected array $filters;

    public function __construct(
        Collection $rows,
        array $filters = []
    ) {
        $this->rows = $rows;
        $this->filters = $filters;
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS
    |--------------------------------------------------------------------------
    */

public function collection(): Collection
{
    $grouped = $this->rows
        ->groupBy(function ($row) {
            return
                ($row['codigo_producto'] ?? '') .
                '|' .
                ($row['almacen'] ?? '');
        });

    return $grouped->map(function (Collection $items) {

        $first = $items->first();

        $rollos = $items->firstWhere(
            'unidad',
            'ROLLOS'
        );

        $metros = $items->firstWhere(
            'unidad',
            'METROS'
        );

        // ROLLOS
        $rollosInicial = $rollos
            ? (float) ($rollos['saldo_inicial'] ?? 0)
            : 0;

        $rollosActual = $rollos
            ? (float) ($rollos['saldo_actual'] ?? 0)
            : 0;

        $rollosSalida = $rollos
            ? (float) ($rollos['salidas'] ?? 0)
            : 0;

        // METROS
        $metrosInicial = $metros
            ? (float) ($metros['saldo_inicial'] ?? 0)
            : 0;

        $metrosActual = $metros
            ? (float) ($metros['saldo_actual'] ?? 0)
            : 0;

        $metrosSalida = $metros
            ? (float) ($metros['salidas'] ?? 0)
            : 0;

        return [
            'codigo_producto' =>
                $first['codigo_producto'] ?? '',

            'producto' =>
                $first['producto'] ?? '',

            'almacen' =>
                $first['almacen'] ?? '',

            'rollos_inicial' =>
                $this->cleanNumber($rollosInicial),

            'rollos_actual' =>
                $this->cleanNumber($rollosActual),

            'rollos_salida' =>
                $this->cleanNumber($rollosSalida),

            'metros_inicial' =>
                $this->cleanNumber($metrosInicial),

            'metros_actual' =>
                $this->cleanNumber($metrosActual),

            'metros_salida' =>
                $this->cleanNumber($metrosSalida),
        ];
    })->values();
}

    /*
    |--------------------------------------------------------------------------
    | QUITAR .000 / .00
    |--------------------------------------------------------------------------
    */

    private function cleanNumber(mixed $value): int|float
    {
        if (
            $value === null ||
            $value === '' ||
            !is_numeric($value)
        ) {
            return 0;
        }

        $number = (float) $value;

        if (
            abs(
                $number - round($number)
            ) < 0.000001
        ) {
            return (int) round($number);
        }

        return $number;
    }

    /*
    |--------------------------------------------------------------------------
    | STYLES
    |--------------------------------------------------------------------------
    */

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | ANCHOS
    |--------------------------------------------------------------------------
    */

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 28,
            'C' => 25,

            'D' => 15,
            'E' => 15,
            'F' => 15,

            'G' => 15,
            'H' => 15,
            'I' => 15,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | EVENTOS
    |--------------------------------------------------------------------------
    */

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (
                AfterSheet $event
            ) {

                $sheet =
                    $event->sheet->getDelegate();

                /*
                |--------------------------------------------------------------------------
                | COLORES
                |--------------------------------------------------------------------------
                */

                $navy = 'FF17243B';
                $blue = 'FF2868A8';
                $teal = 'FF159DA0';

                $white = 'FFFFFFFF';
                $gray = 'FF64748B';
                $border = 'FFB8C2CC';

                /*
                |--------------------------------------------------------------------------
                | FILAS SUPERIORES
                |--------------------------------------------------------------------------
                */

                $sheet->insertNewRowBefore(
                    1,
                    8
                );

                /*
                |--------------------------------------------------------------------------
                | TÍTULO
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A1:I1');

                $sheet->setCellValue(
                    'A1',
                    'MOVIMIENTO DE PRODUCTOS'
                );

                $sheet->getStyle('A1:I1')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setARGB($navy);

                $sheet->getStyle('A1:I1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(20)
                    ->getColor()
                    ->setARGB($white);

                $sheet->getStyle('A1:I1')
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet->getRowDimension(1)
                    ->setRowHeight(38);

                /*
                |--------------------------------------------------------------------------
                | DESCRIPCIÓN
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A2:I2');

                $sheet->setCellValue(
                    'A2',
                    'Saldo inicial, movimientos y saldo actual por producto.'
                );

                $sheet->getStyle('A2:I2')
                    ->getFont()
                    ->setItalic(true)
                    ->setSize(11)
                    ->getColor()
                    ->setARGB($gray);

                /*
                |--------------------------------------------------------------------------
                | FILTROS
                |--------------------------------------------------------------------------
                */

                $from = $this->filters['from'] ?? null;
                $to = $this->filters['to'] ?? null;

                $warehouseId =
                    $this->filters['warehouse_id'] ?? null;

                $productId =
                    $this->filters['product_id'] ?? null;

                $unit =
                    $this->filters['unit'] ?? null;

                $sheet->setCellValue(
                    'A4',
                    'Fecha inicio'
                );

                $sheet->setCellValue(
                    'B4',
                    $from ?: 'Todos'
                );

                $sheet->setCellValue(
                    'D4',
                    'Fecha fin'
                );

                $sheet->setCellValue(
                    'E4',
                    $to ?: 'Todos'
                );

                $sheet->setCellValue(
                    'G4',
                    'Almacén'
                );

                $sheet->setCellValue(
                    'H4',
                    $warehouseId ?: 'Todos'
                );

                $sheet->setCellValue(
                    'A5',
                    'Producto'
                );

                $sheet->setCellValue(
                    'B5',
                    $productId ?: 'Todos'
                );

                $sheet->setCellValue(
                    'D5',
                    'Unidad'
                );

                $sheet->setCellValue(
                    'E5',
                    $unit ?: 'Todas las unidades'
                );

                $sheet->getStyle('A4:I5')
                    ->getFont()
                    ->setSize(10)
                    ->getColor()
                    ->setARGB($gray);

                /*
                |--------------------------------------------------------------------------
                | RESUMEN
                |--------------------------------------------------------------------------
                */

                $totalIngresos =
                    $this->rows->sum(
                        fn ($row) =>
                            (float) (
                                $row->ingresos ?? 0
                            )
                    );

                $totalSalidas =
                    $this->rows->sum(
                        fn ($row) =>
                            (float) (
                                $row->salidas ?? 0
                            )
                    );

                $totalRecibidas =
                    $this->rows->sum(
                        fn ($row) =>
                            (float) (
                                $row->transferencias_recibidas
                                ?? 0
                            )
                    );

                $totalEnviadas =
                    $this->rows->sum(
                        fn ($row) =>
                            (float) (
                                $row->transferencias_enviadas
                                ?? 0
                            )
                    );

                $sheet->mergeCells('A7:B7');
                $sheet->mergeCells('C7:D7');
                $sheet->mergeCells('E7:F7');
                $sheet->mergeCells('G7:I7');

                $sheet->setCellValue(
                    'A7',
                    'INGRESOS'
                );

                $sheet->setCellValue(
                    'C7',
                    'SALIDAS'
                );

                $sheet->setCellValue(
                    'E7',
                    'TRANSFERENCIAS RECIBIDAS'
                );

                $sheet->setCellValue(
                    'G7',
                    'TRANSFERENCIAS ENVIADAS'
                );

                $this->summaryStyle(
                    $sheet,
                    'A7:B7',
                    'FF164C4D',
                    $white
                );

                $this->summaryStyle(
                    $sheet,
                    'C7:D7',
                    'FF392039',
                    $white
                );

                $this->summaryStyle(
                    $sheet,
                    'E7:F7',
                    'FF1F4270',
                    $white
                );

                $this->summaryStyle(
                    $sheet,
                    'G7:I7',
                    'FF30251F',
                    $white
                );

                $sheet->mergeCells('A8:B8');
                $sheet->mergeCells('C8:D8');
                $sheet->mergeCells('E8:F8');
                $sheet->mergeCells('G8:I8');

                $sheet->setCellValue(
                    'A8',
                    $this->cleanNumber(
                        $totalIngresos
                    )
                );

                $sheet->setCellValue(
                    'C8',
                    $this->cleanNumber(
                        $totalSalidas
                    )
                );

                $sheet->setCellValue(
                    'E8',
                    $this->cleanNumber(
                        $totalRecibidas
                    )
                );

                $sheet->setCellValue(
                    'G8',
                    $this->cleanNumber(
                        $totalEnviadas
                    )
                );

                $sheet->getStyle('A8:I8')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(14);

                /*
                |--------------------------------------------------------------------------
                | GRUPOS
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A10:C10');
                $sheet->mergeCells('D10:F10');
                $sheet->mergeCells('G10:I10');

                $sheet->setCellValue(
                    'A10',
                    'PRODUCTO / UBICACIÓN'
                );

                $sheet->setCellValue(
                    'D10',
                    'ROLLOS'
                );

                $sheet->setCellValue(
                    'G10',
                    'METROS'
                );

                $this->groupStyle(
                    $sheet,
                    'A10:C10',
                    $navy
                );

                $this->groupStyle(
                    $sheet,
                    'D10:F10',
                    $blue
                );

                $this->groupStyle(
                    $sheet,
                    'G10:I10',
                    $teal
                );

                /*
                |--------------------------------------------------------------------------
                | ENCABEZADOS
                |--------------------------------------------------------------------------
                */

                $headers = [
                    'Código',
                    'Producto',
                    'Almacén',

                    'Saldo inicial',
                    'Actual',
                    'Salida',

                    'Saldo inicial',
                    'Actual',
                    'Salida',
                ];

                foreach (
                    $headers as $index => $header
                ) {

                    $column =
                        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                            $index + 1
                        );

                    $sheet->setCellValue(
                        $column . '11',
                        $header
                    );
                }

                $sheet->getStyle('A11:I11')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setARGB($navy);

                $sheet->getStyle('A11:I11')
                    ->getFont()
                    ->setBold(true)
                    ->getColor()
                    ->setARGB($white);

                $sheet->getStyle('A11:I11')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    )
                    ->setWrapText(true);

                $sheet->getRowDimension(11)
                    ->setRowHeight(32);

                /*
                |--------------------------------------------------------------------------
                | DATOS
                |--------------------------------------------------------------------------
                */

                $data = $this->collection();

                $firstRow = 12;

                foreach (
                    $data as $index => $row
                ) {

                    $excelRow =
                        $firstRow + $index;

                    $sheet->setCellValue(
                        "A{$excelRow}",
                        $row['codigo_producto']
                    );

                    $sheet->setCellValue(
                        "B{$excelRow}",
                        $row['producto']
                    );

                    $sheet->setCellValue(
                        "C{$excelRow}",
                        $row['almacen']
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | ROLLOS
                    |--------------------------------------------------------------------------
                    */

                    $sheet->setCellValue(
                        "D{$excelRow}",
                        $row['rollos_inicial']
                    );

                    $sheet->setCellValue(
                        "E{$excelRow}",
                        $row['rollos_actual']
                    );

                    $sheet->setCellValue(
                        "F{$excelRow}",
                        $row['rollos_salida']
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | METROS
                    |--------------------------------------------------------------------------
                    */

                    $sheet->setCellValue(
                        "G{$excelRow}",
                        $row['metros_inicial']
                    );

                    $sheet->setCellValue(
                        "H{$excelRow}",
                        $row['metros_actual']
                    );

                    $sheet->setCellValue(
                        "I{$excelRow}",
                        $row['metros_salida']
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                $lastDataRow =
                    $firstRow +
                    $data->count() -
                    1;

                $totalRow =
                    $lastDataRow + 1;

                $sheet->mergeCells(
                    "A{$totalRow}:C{$totalRow}"
                );

                $sheet->setCellValue(
                    "A{$totalRow}",
                    'TOTAL'
                );

                /*
                |--------------------------------------------------------------------------
                | SUMAS
                |--------------------------------------------------------------------------
                */

                $totalRollosInicial =
                    $data->sum(
                        'rollos_inicial'
                    );

                $totalRollosActual =
                    $data->sum(
                        'rollos_actual'
                    );

                $totalRollosSalida =
                    $data->sum(
                        'rollos_salida'
                    );

                $totalMetrosInicial =
                    $data->sum(
                        'metros_inicial'
                    );

                $totalMetrosActual =
                    $data->sum(
                        'metros_actual'
                    );

                $totalMetrosSalida =
                    $data->sum(
                        'metros_salida'
                    );

                $sheet->setCellValue(
                    "D{$totalRow}",
                    $this->cleanNumber(
                        $totalRollosInicial
                    )
                );

                $sheet->setCellValue(
                    "E{$totalRow}",
                    $this->cleanNumber(
                        $totalRollosActual
                    )
                );

                $sheet->setCellValue(
                    "F{$totalRow}",
                    $this->cleanNumber(
                        $totalRollosSalida
                    )
                );

                $sheet->setCellValue(
                    "G{$totalRow}",
                    $this->cleanNumber(
                        $totalMetrosInicial
                    )
                );

                $sheet->setCellValue(
                    "H{$totalRow}",
                    $this->cleanNumber(
                        $totalMetrosActual
                    )
                );

                $sheet->setCellValue(
                    "I{$totalRow}",
                    $this->cleanNumber(
                        $totalMetrosSalida
                    )
                );

                /*
                |--------------------------------------------------------------------------
                | ESTILO TOTAL
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle(
                    "A{$totalRow}:I{$totalRow}"
                )
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setARGB($navy);

                $sheet->getStyle(
                    "A{$totalRow}:I{$totalRow}"
                )
                    ->getFont()
                    ->setBold(true)
                    ->getColor()
                    ->setARGB($white);

                $sheet->getStyle(
                    "A{$totalRow}:I{$totalRow}"
                )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                /*
                |--------------------------------------------------------------------------
                | BORDES
                |--------------------------------------------------------------------------
                */

                if ($data->count() > 0) {

                    $sheet->getStyle(
                        "A11:I{$lastDataRow}"
                    )
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(
                            Border::BORDER_THIN
                        )
                        ->getColor()
                        ->setARGB($border);
                }

                /*
                |--------------------------------------------------------------------------
                | FORMATO NÚMEROS
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle(
                    "D{$firstRow}:I{$totalRow}"
                )
                    ->getNumberFormat()
                    ->setFormatCode('0');

                /*
                |--------------------------------------------------------------------------
                | ALINEACIÓN
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle(
                    "D{$firstRow}:I{$totalRow}"
                )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle(
                    "A11:I{$totalRow}"
                )
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                /*
                |--------------------------------------------------------------------------
                | FILTRO EXCEL
                |--------------------------------------------------------------------------
                */

                if ($data->count() > 0) {

                    $sheet->setAutoFilter(
                        "A11:I{$lastDataRow}"
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | CONGELAR
                |--------------------------------------------------------------------------
                */

                $sheet->freezePane('A12');

                /*
                |--------------------------------------------------------------------------
                | ALTURA
                |--------------------------------------------------------------------------
                */

                for (
                    $i = $firstRow;
                    $i <= $totalRow;
                    $i++
                ) {

                    $sheet
                        ->getRowDimension($i)
                        ->setRowHeight(22);
                }

                /*
                |--------------------------------------------------------------------------
                | GRID
                |--------------------------------------------------------------------------
                */

                $sheet->setShowGridlines(false);
            },
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ESTILO RESUMEN
    |--------------------------------------------------------------------------
    */

    private function summaryStyle(
        Worksheet $sheet,
        string $range,
        string $background,
        string $fontColor
    ): void {

        $sheet->getStyle($range)
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB($background);

        $sheet->getStyle($range)
            ->getFont()
            ->setBold(true)
            ->getColor()
            ->setARGB($fontColor);

        $sheet->getStyle($range)
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_LEFT
            )
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ESTILO GRUPOS
    |--------------------------------------------------------------------------
    */

    private function groupStyle(
        Worksheet $sheet,
        string $range,
        string $background
    ): void {

        $sheet->getStyle($range)
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB($background);

        $sheet->getStyle($range)
            ->getFont()
            ->setBold(true)
            ->setSize(13)
            ->getColor()
            ->setARGB('FFFFFFFF');

        $sheet->getStyle($range)
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            )
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );
    }
}