<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioExport implements
    FromArray,
    WithCustomStartCell,
    WithColumnWidths,
    WithEvents,
    WithTitle
{
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    /*
    |--------------------------------------------------------------------------
    | NOMBRE DE LA HOJA
    |--------------------------------------------------------------------------
    */

    public function title(): string
    {
        return 'Inventario';
    }

    /*
    |--------------------------------------------------------------------------
    | CELDA DONDE COMIENZAN LOS DATOS
    |--------------------------------------------------------------------------
    |
    | Los datos empiezan en A9.
    |
    */

    public function startCell(): string
    {
        return 'A9';
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS
    |--------------------------------------------------------------------------
    */

    public function array(): array
    {
        return $this->rows
            ->map(function ($row) {

                return [
                    // A
                    $row->almacen ?? '',

                    // B
                    $row->codigo_producto ?? '',

                    // C
                    $row->producto ?? '',

                    // D
                    $row->color ?? '',

                    // E
                    $this->number(
                        $row->rollos ?? 0
                    ),

                    // F
                    $this->number(
                        $row->metros ?? 0
                    ),

                    // G
                    $this->number(
                        $row->stock_minimo ?? 0
                    ),

                    // H
                    $this->getEstado(
                        (float) ($row->rollos ?? 0),
                        (float) ($row->stock_minimo ?? 0)
                    ),
                ];
            })
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | FORMATO DE NÚMEROS
    |--------------------------------------------------------------------------
    */

    private function number(mixed $value): int|float
    {
        $number = round(
            (float) ($value ?? 0),
            3
        );

        if (fmod($number, 1.0) == 0.0) {
            return (int) $number;
        }

        return $number;
    }

    /*
    |--------------------------------------------------------------------------
    | ESTADO
    |--------------------------------------------------------------------------
    */

    private function getEstado(
        float $rollos,
        float $stockMinimo
    ): string {

        if ($rollos <= 0) {
            return 'Sin stock';
        }

        if ($rollos <= $stockMinimo) {
            return 'Stock mínimo';
        }

        return 'Disponible';
    }

    /*
    |--------------------------------------------------------------------------
    | ANCHOS
    |--------------------------------------------------------------------------
    */

    public function columnWidths(): array
    {
        return [
            'A' => 28,
            'B' => 16,
            'C' => 32,
            'D' => 22,
            'E' => 16,
            'F' => 16,
            'G' => 18,
            'H' => 18,
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

                /** @var Worksheet $sheet */
                $sheet = $event
                    ->sheet
                    ->getDelegate();

                /*
                |--------------------------------------------------------------------------
                | TÍTULO
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    'A1',
                    'REPORTE DE INVENTARIO'
                );

                $sheet->mergeCells(
                    'A1:H1'
                );

                $sheet
                    ->getStyle('A1:H1')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB('111C34');

                $sheet
                    ->getStyle('A1:H1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(16)
                    ->getColor()
                    ->setRGB('FFFFFF');

                $sheet
                    ->getStyle('A1:H1')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_LEFT
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet
                    ->getRowDimension(1)
                    ->setRowHeight(30);

                /*
                |--------------------------------------------------------------------------
                | ESPACIO
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getRowDimension(2)
                    ->setRowHeight(8);

                /*
                |--------------------------------------------------------------------------
                | TOTALES PARA LOS INDICADORES
                |--------------------------------------------------------------------------
                */

                $totalProductos = $this->rows->count();

                $totalRollos = $this->rows->sum(
                    fn ($row) =>
                        (float) ($row->rollos ?? 0)
                );

                $totalMetros = $this->rows->sum(
                    fn ($row) =>
                        (float) ($row->metros ?? 0)
                );

                /*
                |--------------------------------------------------------------------------
                | INDICADOR PRODUCTOS
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A3:B3');
                $sheet->mergeCells('A4:B4');

                $sheet->setCellValue(
                    'A3',
                    'PRODUCTOS'
                );

                $sheet->setCellValue(
                    'A4',
                    $this->number(
                        $totalProductos
                    )
                );

                /*
                |--------------------------------------------------------------------------
                | INDICADOR ROLLOS
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('C3:D3');
                $sheet->mergeCells('C4:D4');

                $sheet->setCellValue(
                    'C3',
                    'ROLLOS'
                );

                $sheet->setCellValue(
                    'C4',
                    $this->number(
                        $totalRollos
                    )
                );

                /*
                |--------------------------------------------------------------------------
                | INDICADOR METROS
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('E3:H3');
                $sheet->mergeCells('E4:H4');

                $sheet->setCellValue(
                    'E3',
                    'METROS DISPONIBLES'
                );

                $sheet->setCellValue(
                    'E4',
                    $this->number(
                        $totalMetros
                    )
                );

                /*
                |--------------------------------------------------------------------------
                | ESTILO INDICADORES
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getStyle('A3:D3')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB('1B2B45');

                $sheet
                    ->getStyle('E3:H3')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB('0F3B3E');

                $sheet
                    ->getStyle('A3:H3')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(10)
                    ->getColor()
                    ->setRGB('FFFFFF');

                $sheet
                    ->getStyle('A4:H4')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB('EEF2F7');

                $sheet
                    ->getStyle('A4:H4')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(14)
                    ->getColor()
                    ->setRGB('172033');

                $sheet
                    ->getStyle('A3:H4')
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                /*
                |--------------------------------------------------------------------------
                | TÍTULO DE TABLA
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    'A6',
                    'INVENTARIO POR PRODUCTO'
                );

                $sheet->mergeCells(
                    'A6:H6'
                );

                $sheet
                    ->getStyle('A6:H6')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB('111C34');

                $sheet
                    ->getStyle('A6:H6')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(14)
                    ->getColor()
                    ->setRGB('FFFFFF');

                /*
                |--------------------------------------------------------------------------
                | SUBTÍTULO
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    'A7',
                    'Inventario actual por almacén y producto'
                );

                $sheet->mergeCells(
                    'A7:H7'
                );

                $sheet
                    ->getStyle('A7:H7')
                    ->getFont()
                    ->setSize(9)
                    ->getColor()
                    ->setRGB('64748B');

                /*
                |--------------------------------------------------------------------------
                | ENCABEZADOS
                |--------------------------------------------------------------------------
                */

                $headers = [
                    'A8' => 'ALMACÉN',
                    'B8' => 'CÓDIGO',
                    'C8' => 'PRODUCTO',
                    'D8' => 'COLOR',
                    'E8' => 'ROLLOS',
                    'F8' => 'METROS',
                    'G8' => 'STOCK MÍNIMO',
                    'H8' => 'ESTADO',
                ];

                foreach ($headers as $cell => $value) {

                    $sheet->setCellValue(
                        $cell,
                        $value
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | ESTILO ENCABEZADOS
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getStyle('A8:H8')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB('1B2B45');

                $sheet
                    ->getStyle('A8:H8')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(10)
                    ->getColor()
                    ->setRGB('FFFFFF');

                $sheet
                    ->getStyle('A8:H8')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    )
                    ->setWrapText(true);

                /*
                |--------------------------------------------------------------------------
                | FILAS DE DATOS
                |--------------------------------------------------------------------------
                */

                $firstDataRow = 9;

                $lastDataRow =
                    $firstDataRow +
                    $this->rows->count() -
                    1;

                /*
                |--------------------------------------------------------------------------
                | FORMATO NUMÉRICO
                |--------------------------------------------------------------------------
                */

                if ($lastDataRow >= $firstDataRow) {

                    $sheet
                        ->getStyle(
                            "E{$firstDataRow}:G{$lastDataRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            'General'
                        );

                    $sheet
                        ->getStyle(
                            "E{$firstDataRow}:G{$lastDataRow}"
                        )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_RIGHT
                        );

                    $sheet
                        ->getStyle(
                            "A{$firstDataRow}:D{$lastDataRow}"
                        )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_LEFT
                        );

                    $sheet
                        ->getStyle(
                            "H{$firstDataRow}:H{$lastDataRow}"
                        )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );
                }

                /*
                |--------------------------------------------------------------------------
                | COLORES DEL ESTADO
                |--------------------------------------------------------------------------
                */

                if ($lastDataRow >= $firstDataRow) {

                    for (
                        $rowNumber = $firstDataRow;
                        $rowNumber <= $lastDataRow;
                        $rowNumber++
                    ) {

                        $estado = strtolower(
                            trim(
                                (string) (
                                    $sheet
                                        ->getCell(
                                            "H{$rowNumber}"
                                        )
                                        ->getValue()
                                    ?? ''
                                )
                            )
                        );

                        if ($estado === 'disponible') {

                            $sheet
                                ->getStyle(
                                    "H{$rowNumber}"
                                )
                                ->getFill()
                                ->setFillType(
                                    Fill::FILL_SOLID
                                )
                                ->getStartColor()
                                ->setRGB('D8F3E5');

                            $sheet
                                ->getStyle(
                                    "H{$rowNumber}"
                                )
                                ->getFont()
                                ->setBold(true)
                                ->getColor()
                                ->setRGB('15803D');
                        }

                        elseif ($estado === 'stock mínimo') {

                            $sheet
                                ->getStyle(
                                    "H{$rowNumber}"
                                )
                                ->getFill()
                                ->setFillType(
                                    Fill::FILL_SOLID
                                )
                                ->getStartColor()
                                ->setRGB('FFF1C7');

                            $sheet
                                ->getStyle(
                                    "H{$rowNumber}"
                                )
                                ->getFont()
                                ->setBold(true)
                                ->getColor()
                                ->setRGB('A16207');
                        }

                        elseif ($estado === 'sin stock') {

                            $sheet
                                ->getStyle(
                                    "H{$rowNumber}"
                                )
                                ->getFill()
                                ->setFillType(
                                    Fill::FILL_SOLID
                                )
                                ->getStartColor()
                                ->setRGB('FEE2E2');

                            $sheet
                                ->getStyle(
                                    "H{$rowNumber}"
                                )
                                ->getFont()
                                ->setBold(true)
                                ->getColor()
                                ->setRGB('B91C1C');
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                $totalRow =
                    $firstDataRow +
                    $this->rows->count();

                $totalStockMinimo = $this->rows->sum(
                    fn ($row) =>
                        (float) ($row->stock_minimo ?? 0)
                );

                $sheet->setCellValue(
                    "A{$totalRow}",
                    'TOTAL'
                );

                $sheet->mergeCells(
                    "A{$totalRow}:D{$totalRow}"
                );

                $sheet->setCellValue(
                    "E{$totalRow}",
                    $this->number(
                        $totalRollos
                    )
                );

                $sheet->setCellValue(
                    "F{$totalRow}",
                    $this->number(
                        $totalMetros
                    )
                );

                $sheet->setCellValue(
                    "G{$totalRow}",
                    $this->number(
                        $totalStockMinimo
                    )
                );

                $sheet->setCellValue(
                    "H{$totalRow}",
                    $this->rows->count()
                    . ' registros'
                );

                /*
                |--------------------------------------------------------------------------
                | ESTILO TOTAL
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getStyle(
                        "A{$totalRow}:H{$totalRow}"
                    )
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB('1B2B45');

                $sheet
                    ->getStyle(
                        "A{$totalRow}:H{$totalRow}"
                    )
                    ->getFont()
                    ->setBold(true)
                    ->setSize(10)
                    ->getColor()
                    ->setRGB('FFFFFF');

                $sheet
                    ->getStyle(
                        "E{$totalRow}:G{$totalRow}"
                    )
                    ->getNumberFormat()
                    ->setFormatCode(
                        'General'
                    );

                $sheet
                    ->getStyle(
                        "E{$totalRow}:G{$totalRow}"
                    )
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_RIGHT
                    );

                /*
                |--------------------------------------------------------------------------
                | BORDES
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getStyle(
                        "A8:H{$totalRow}"
                    )
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        Border::BORDER_THIN
                    )
                    ->getColor()
                    ->setRGB('CBD5E1');

                /*
                |--------------------------------------------------------------------------
                | FILTRO EXCEL
                |--------------------------------------------------------------------------
                */

                if ($lastDataRow >= $firstDataRow) {

                    $sheet->setAutoFilter(
                        "A8:H{$lastDataRow}"
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | CONGELAR ENCABEZADO
                |--------------------------------------------------------------------------
                */

                $sheet->freezePane('A9');

                /*
                |--------------------------------------------------------------------------
                | ALTURAS
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getRowDimension(8)
                    ->setRowHeight(30);

                $sheet
                    ->getRowDimension($totalRow)
                    ->setRowHeight(24);
            },
        ];
    }
}