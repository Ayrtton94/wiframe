<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SalidasExport implements
    FromArray,
    WithCustomStartCell,
    WithColumnWidths,
    WithEvents,
    WithTitle
{
    protected array $filters;

    protected $rows;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;

        $this->rows = $this->getRows();
    }

    /*
    |--------------------------------------------------------------------------
    | NOMBRE DE LA HOJA
    |--------------------------------------------------------------------------
    */

    public function title(): string
    {
        return 'Salidas';
    }

    /*
    |--------------------------------------------------------------------------
    | CELDA INICIAL
    |--------------------------------------------------------------------------
    */

    public function startCell(): string
    {
        return 'A11';
    }

    /*
    |--------------------------------------------------------------------------
    | CONSULTA
    |--------------------------------------------------------------------------
    */

    protected function getRows()
    {
        $query = DB::table('sale_items as si')

            ->join(
                'sales as s',
                's.id',
                '=',
                'si.sale_id'
            )

            ->join(
                'stores as p',
                'p.id',
                '=',
                'si.store_id'
            )

            ->join(
                'warehouses as w',
                'w.id',
                '=',
                's.warehouse_id'
            )

            ->leftJoin(
                'customers as c',
                'c.id',
                '=',
                's.customer_id'
            )

            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                's.sold_by'
            )

            ->where(
                'p.is_active',
                true
            )

            /*
            |--------------------------------------------------------------------------
            | FECHA INICIO
            |--------------------------------------------------------------------------
            */

            ->when(
                !empty($this->filters['from']),
                function ($query) {
                    $query->where(
                        's.created_at',
                        '>=',
                        Carbon::parse(
                            $this->filters['from']
                        )->startOfDay()
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | FECHA FIN
            |--------------------------------------------------------------------------
            */

            ->when(
                !empty($this->filters['to']),
                function ($query) {
                    $query->where(
                        's.created_at',
                        '<=',
                        Carbon::parse(
                            $this->filters['to']
                        )->endOfDay()
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | ALMACÉN
            |--------------------------------------------------------------------------
            */

            ->when(
                !empty($this->filters['warehouse_id']),
                function ($query) {
                    $query->where(
                        's.warehouse_id',
                        $this->filters['warehouse_id']
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | RESPONSABLE
            |--------------------------------------------------------------------------
            */

            ->when(
                !empty($this->filters['responsible_id']),
                function ($query) {
                    $query->where(
                        's.sold_by',
                        $this->filters['responsible_id']
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | CLIENTE
            |--------------------------------------------------------------------------
            */

            ->when(
                !empty($this->filters['customer_id']),
                function ($query) {
                    $query->where(
                        's.customer_id',
                        $this->filters['customer_id']
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | BÚSQUEDA
            |--------------------------------------------------------------------------
            */

            ->when(
                !empty($this->filters['search']),
                function ($query) {

                    $search = trim(
                        $this->filters['search']
                    );

                    $query->where(function ($q) use ($search) {

                        $q->where(
                            's.code',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'p.code_product',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'p.name_product',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'p.color',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'w.name',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'c.name',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'u.name',
                            'like',
                            "%{$search}%"
                        );
                    });
                }
            )

            ->select([
                's.id as sale_id',

                's.code as salida_code',

                's.created_at as fecha_hora',

                'w.name as almacen',

                'c.name as cliente',

                'u.name as responsable',

                'p.code_product as codigo_producto',

                'p.name_product as producto',

                'p.color as color',

                'si.quantity as cantidad',

                'si.unit as unidad',

                'si.unit_price as precio',

                'si.line_total as total',

                's.notes as motivo',
            ])

            ->orderByDesc(
                's.created_at'
            )

            ->orderBy(
                's.id'
            )

            ->orderBy(
                'si.id'
            );

        return $query->get();
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS DEL EXCEL
    |--------------------------------------------------------------------------
    */

    public function array(): array
    {
        $data = [
            [
                'FECHA',
                'HORA',
                'CÓDIGO SALIDA',
                'ALMACÉN',
                'CLIENTE',
                'RESPONSABLE',
                'CÓDIGO PRODUCTO',
                'PRODUCTO',
                'COLOR',
                'ROLLOS',
                'METROS',
                'PRECIO UNITARIO',
                'SUBTOTAL',
                'MOTIVO',
            ],
        ];

        foreach ($this->rows as $row) {

            $fecha = $row->fecha_hora
                ? Carbon::parse(
                    $row->fecha_hora
                )
                : null;

            $unidad = strtolower(
                trim((string) ($row->unidad ?? ''))
            );

            $rollos = 0;
            $metros = 0;

            if (in_array($unidad, ['kilos', 'kilo', 'rollos', 'rollo'], true)) {
                $rollos = $this->cleanNumber($row->cantidad);
            }

            if (in_array($unidad, ['metros', 'metro'], true)) {
                $metros = $this->cleanNumber($row->cantidad);
            }

            $precio = round(
                (float) (
                    $row->precio ?? 0
                ),
                2
            );

            $subtotal = round(
                (float) (
                    $row->total
                    ??
                    (
                        (float) (
                            $row->cantidad ?? 0
                        )
                        *
                        $precio
                    )
                ),
                2
            );

            $data[] = [

                /*
                |--------------------------------------------------------------------------
                | FECHA
                |--------------------------------------------------------------------------
                */

                $fecha
                    ? $fecha->format('d/m/Y')
                    : '',

                /*
                |--------------------------------------------------------------------------
                | HORA
                |--------------------------------------------------------------------------
                */

                $fecha
                    ? $fecha->format('h:i A')
                    : '',

                /*
                |--------------------------------------------------------------------------
                | CÓDIGO
                |--------------------------------------------------------------------------
                */

                $row->salida_code ?? '',

                /*
                |--------------------------------------------------------------------------
                | ALMACÉN
                |--------------------------------------------------------------------------
                */

                $row->almacen ?? '',

                /*
                |--------------------------------------------------------------------------
                | CLIENTE
                |--------------------------------------------------------------------------
                */

                $row->cliente
                    ?: 'CONSUMIDOR FINAL',

                /*
                |--------------------------------------------------------------------------
                | RESPONSABLE
                |--------------------------------------------------------------------------
                */

                $row->responsable ?? '',

                /*
                |--------------------------------------------------------------------------
                | CÓDIGO PRODUCTO
                |--------------------------------------------------------------------------
                */

                $row->codigo_producto ?? '',

                /*
                |--------------------------------------------------------------------------
                | PRODUCTO
                |--------------------------------------------------------------------------
                */

                $row->producto ?? '',

                /*
                |--------------------------------------------------------------------------
                | COLOR
                |--------------------------------------------------------------------------
                */

                $row->color ?? '',

                /*
                |--------------------------------------------------------------------------
                | CANTIDAD
                |--------------------------------------------------------------------------
                */

                $rollos,

                $metros,

                /*
                |--------------------------------------------------------------------------
                | PRECIO
                |--------------------------------------------------------------------------
                */

                $precio,

                /*
                |--------------------------------------------------------------------------
                | SUBTOTAL
                |--------------------------------------------------------------------------
                */

                $subtotal,

                /*
                |--------------------------------------------------------------------------
                | MOTIVO
                |--------------------------------------------------------------------------
                */

                $row->motivo
                    ?: 'SALIDA',
            ];
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | LIMPIAR NÚMEROS
    |--------------------------------------------------------------------------
    |
    | 1.00  -> 1
    | 10.00 -> 10
    | 5.00  -> 5
    | 10.50 -> 10.5
    |
    */

    protected function cleanNumber(
        mixed $value
    ): int|float {

        $number = round(
            (float) (
                $value ?? 0
            ),
            3
        );

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
    | UNIDAD
    |--------------------------------------------------------------------------
    */

    protected function unitLabel(
        mixed $unit
    ): string {

        $unit = strtolower(
            trim(
                (string) $unit
            )
        );

        return match ($unit) {

            'kilos',
            'kilo',
            'rollos',
            'rollo' => 'Rollos',

            'metros',
            'metro' => 'Metros',

            default => ucfirst($unit),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL SALIDAS
    |--------------------------------------------------------------------------
    */

    protected function totalSalidas(): int
    {
        return $this->rows
            ->pluck('sale_id')
            ->unique()
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL IMPORTE
    |--------------------------------------------------------------------------
    */

    protected function totalImporte(): float
    {
        return round(
            $this->rows->sum(
                fn ($row) =>
                    (float) (
                        $row->total ?? 0
                    )
            ),
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL METROS
    |--------------------------------------------------------------------------
    */

    protected function totalMetros(): int|float
    {
        $total = $this->rows
            ->filter(function ($row) {

                return in_array(
                    strtolower(
                        trim(
                            (string) $row->unidad
                        )
                    ),
                    [
                        'metros',
                        'metro',
                    ],
                    true
                );
            })
            ->sum(
                fn ($row) =>
                    (float) (
                        $row->cantidad ?? 0
                    )
            );

        return $this->cleanNumber(
            $total
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL ROLLOS
    |--------------------------------------------------------------------------
    */

    protected function totalRollos(): int|float
    {
        $total = $this->rows
            ->filter(function ($row) {

                return in_array(
                    strtolower(
                        trim(
                            (string) $row->unidad
                        )
                    ),
                    [
                        'kilos',
                        'kilo',
                        'rollos',
                        'rollo',
                    ],
                    true
                );
            })
            ->sum(
                fn ($row) =>
                    (float) (
                        $row->cantidad ?? 0
                    )
            );

        return $this->cleanNumber(
            $total
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NOMBRES DE FILTROS
    |--------------------------------------------------------------------------
    */

    protected function filterNames(): array
    {
        $warehouse = null;
        $responsible = null;
        $customer = null;

        if (!empty($this->filters['warehouse_id'])) {

            $warehouse = DB::table(
                'warehouses'
            )
                ->where(
                    'id',
                    $this->filters['warehouse_id']
                )
                ->value('name');
        }

        if (!empty($this->filters['responsible_id'])) {

            $responsible = DB::table(
                'users'
            )
                ->where(
                    'id',
                    $this->filters['responsible_id']
                )
                ->value('name');
        }

        if (!empty($this->filters['customer_id'])) {

            $customer = DB::table(
                'customers'
            )
                ->where(
                    'id',
                    $this->filters['customer_id']
                )
                ->value('name');
        }

        return [
            'warehouse' =>
                $warehouse ?: 'Todos',

            'responsible' =>
                $responsible ?: 'Todos',

            'customer' =>
                $customer ?: 'Todos',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ANCHO DE COLUMNAS
    |--------------------------------------------------------------------------
    */

    public function columnWidths(): array
    {
        return [

            'A' => 14,
            'B' => 12,
            'C' => 28,
            'D' => 24,
            'E' => 24,
            'F' => 20,
            'G' => 18,
            'H' => 30,
            'I' => 24,
            'J' => 12,
            'K' => 12,
            'L' => 18,
            'M' => 18,
            'N' => 16,
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
                | DATOS GENERALES
                |--------------------------------------------------------------------------
                */

                $totalSalidas =
                    $this->totalSalidas();

                $importeTotal =
                    $this->totalImporte();

                $totalMetros =
                    $this->totalMetros();

                $totalRollos =
                    $this->totalRollos();

                $filters =
                    $this->filterNames();

                /*
                |--------------------------------------------------------------------------
                | TÍTULO
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    'A1',
                    'REPORTE DE SALIDAS'
                );

                $sheet->mergeCells(
                    'A1:N1'
                );

                $sheet
                    ->getStyle('A1:N1')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB(
                        '111C34'
                    );

                $sheet
                    ->getStyle('A1:N1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(16)
                    ->getColor()
                    ->setRGB(
                        'FFFFFF'
                    );

                $sheet
                    ->getStyle('A1:N1')
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );

                $sheet
                    ->getRowDimension(1)
                    ->setRowHeight(30);

                /*
                |--------------------------------------------------------------------------
                | SUBTÍTULO
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    'A2',
                    'Histórico de salidas registradas'
                );

                $sheet->mergeCells(
                    'A2:N2'
                );

                $sheet
                    ->getStyle('A2:N2')
                    ->getFont()
                    ->setItalic(true)
                    ->setSize(10)
                    ->getColor()
                    ->setRGB(
                        '64748B'
                    );

                /*
                |--------------------------------------------------------------------------
                | FILTROS
                |--------------------------------------------------------------------------
                */

                $from =
                    $this->filters['from']
                    ?? 'Todos';

                $to =
                    $this->filters['to']
                    ?? 'Todos';

                $sheet->setCellValue(
                    'A4',
                    'Fecha inicio'
                );

                $sheet->setCellValue(
                    'B4',
                    $from
                );

                $sheet->setCellValue(
                    'C4',
                    'Fecha fin'
                );

                $sheet->setCellValue(
                    'D4',
                    $to
                );

                $sheet->setCellValue(
                    'E4',
                    'Almacén'
                );

                $sheet->setCellValue(
                    'F4',
                    $filters['warehouse']
                );

                $sheet->setCellValue(
                    'G4',
                    'Responsable'
                );

                $sheet->setCellValue(
                    'H4',
                    $filters['responsible']
                );

                $sheet->setCellValue(
                    'I4',
                    'Cliente'
                );

                $sheet->setCellValue(
                    'J4',
                    $filters['customer']
                );

                $sheet->setCellValue(
                    'K4',
                    'Motivo'
                );

                $sheet->setCellValue(
                    'L4',
                    'Todos'
                );

                $sheet
                    ->getStyle('A4:M4')
                    ->getFont()
                    ->setSize(9);

                /*
                |--------------------------------------------------------------------------
                | INDICADORES
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells(
                    'A6:C6'
                );

                $sheet->mergeCells(
                    'A7:C7'
                );

                $sheet->setCellValue(
                    'A6',
                    'SALIDAS'
                );

                $sheet->setCellValue(
                    'A7',
                    $totalSalidas
                );

                $sheet->mergeCells(
                    'D6:F6'
                );

                $sheet->mergeCells(
                    'D7:F7'
                );

                $sheet->setCellValue(
                    'D6',
                    'IMPORTE TOTAL'
                );

                $sheet->setCellValue(
                    'D7',
                    'S/ '
                    . number_format(
                        $importeTotal,
                        2,
                        '.',
                        ','
                    )
                );

                $sheet->mergeCells(
                    'G6:I6'
                );

                $sheet->mergeCells(
                    'G7:I7'
                );

                $sheet->setCellValue(
                    'G6',
                    'METROS'
                );

                $sheet->setCellValue(
                    'G7',
                    $totalMetros
                );

                $sheet->mergeCells(
                    'J6:N6'
                );

                $sheet->mergeCells(
                    'J7:N7'
                );

                $sheet->setCellValue(
                    'J6',
                    'ROLLOS'
                );

                $sheet->setCellValue(
                    'J7',
                    $totalRollos
                );

                /*
                |--------------------------------------------------------------------------
                | ESTILO INDICADORES
                |--------------------------------------------------------------------------
                */

                foreach (
                    [
                        'A6:C6',
                        'D6:F6',
                        'G6:I6',
                        'J6:N6',
                    ] as $range
                ) {

                    $sheet
                        ->getStyle($range)
                        ->getFill()
                        ->setFillType(
                            Fill::FILL_SOLID
                        )
                        ->getStartColor()
                        ->setRGB(
                            '1B2B45'
                        );

                    $sheet
                        ->getStyle($range)
                        ->getFont()
                        ->setBold(true)
                        ->getColor()
                        ->setRGB(
                            'FFFFFF'
                        );
                }

                $sheet
                    ->getStyle('D6:F6')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB(
                        '0F3B3E'
                    );

                $sheet
                    ->getStyle('G6:I6')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB(
                        '0F3B3E'
                    );

                $sheet
                    ->getStyle('J6:N6')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB(
                        '3A351D'
                    );

                $sheet
                    ->getStyle('A7:N7')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB(
                        'EEF2F7'
                    );

                $sheet
                    ->getStyle('A7:N7')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(13);

                /*
                |--------------------------------------------------------------------------
                | TÍTULO DETALLE
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    'A9',
                    'DETALLE CONSOLIDADO DE SALIDAS'
                );

                $sheet->mergeCells(
                    'A9:N9'
                );

                $sheet
                    ->getStyle('A9:N9')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB(
                        '111C34'
                    );

                $sheet
                    ->getStyle('A9:N9')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(13)
                    ->getColor()
                    ->setRGB(
                        'FFFFFF'
                    );

                /*
                |--------------------------------------------------------------------------
                | SUBTÍTULO DETALLE
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    'A10',
                    'Mostrando '
                    . $this->rows->count()
                    . ' registros de productos.'
                );

                $sheet->mergeCells(
                    'A10:N10'
                );

                $sheet
                    ->getStyle('A10:N10')
                    ->getFont()
                    ->setSize(9)
                    ->getColor()
                    ->setRGB(
                        '64748B'
                    );

                /*
                |--------------------------------------------------------------------------
                | ENCABEZADOS
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getStyle('A11:N11')
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB(
                        '1B2B45'
                    );

                $sheet
                    ->getStyle('A11:N11')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(9)
                    ->getColor()
                    ->setRGB(
                        'FFFFFF'
                    );

                $sheet
                    ->getStyle('A11:N11')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    )
                    ->setWrapText(true);

                $sheet
                    ->getRowDimension(11)
                    ->setRowHeight(38);

                /*
                |--------------------------------------------------------------------------
                | FILAS
                |--------------------------------------------------------------------------
                */

                $firstRow = 12;

                $lastRow =
                    $firstRow
                    + $this->rows->count()
                    - 1;

                if ($lastRow >= $firstRow) {

                    /*
                    | Cantidades sin puntos
                    */

                    $sheet
                        ->getStyle(
                            "J{$firstRow}:K{$lastRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '0'
                        );

                    /*
                    | Precios
                    */

                    $sheet
                        ->getStyle(
                            "L{$firstRow}:M{$lastRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '"S/ " #,##0.00'
                        );

                    /*
                    | Alineación
                    */

                    $sheet
                        ->getStyle(
                            "J{$firstRow}:M{$lastRow}"
                        )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_RIGHT
                        );

                    $sheet
                        ->getStyle(
                            "J{$firstRow}:K{$lastRow}"
                        )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_RIGHT
                        );

                    /*
                    | Motivo
                    */

                    for (
                        $r = $firstRow;
                        $r <= $lastRow;
                        $r++
                    ) {

                        $sheet
                            ->getStyle(
                                "N{$r}"
                            )
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB(
                                '2563EB'
                            );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                $totalRow =
                    $firstRow
                    + $this->rows->count();

                $sheet->setCellValue(
                    "A{$totalRow}",
                    'TOTALES DEL PERÍODO'
                );

                $sheet->mergeCells(
                    "A{$totalRow}:I{$totalRow}"
                );

                $sheet->setCellValue(
                    "J{$totalRow}",
                    $totalRollos
                );

                $sheet->setCellValue(
                    "K{$totalRow}",
                    $totalMetros
                );

                $sheet->setCellValue(
                    "L{$totalRow}",
                    $importeTotal
                );

                $sheet->setCellValue(
                    "M{$totalRow}",
                    $totalSalidas
                    . ' salidas'
                );

                $sheet
                    ->getStyle(
                        "A{$totalRow}:N{$totalRow}"
                    )
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB(
                        '1B2B45'
                    );

                $sheet
                    ->getStyle(
                        "A{$totalRow}:N{$totalRow}"
                    )
                    ->getFont()
                    ->setBold(true)
                    ->getColor()
                    ->setRGB(
                        'FFFFFF'
                    );

                $sheet
                    ->getStyle(
                        "J{$totalRow}:K{$totalRow}"
                    )
                    ->getNumberFormat()
                    ->setFormatCode(
                        '0'
                    );

                $sheet
                    ->getStyle(
                        "L{$totalRow}"
                    )
                    ->getNumberFormat()
                    ->setFormatCode(
                        '"S/ " #,##0.00'
                    );

                /*
                |--------------------------------------------------------------------------
                | RESUMEN DEL PERÍODO
                |--------------------------------------------------------------------------
                */

                $summaryRow =
                    $totalRow + 3;

                $sheet->setCellValue(
                    "A{$summaryRow}",
                    'RESUMEN DEL PERÍODO'
                );

                $sheet->mergeCells(
                    "A{$summaryRow}:C{$summaryRow}"
                );

                $sheet
                    ->getStyle(
                        "A{$summaryRow}:C{$summaryRow}"
                    )
                    ->getFill()
                    ->setFillType(
                        Fill::FILL_SOLID
                    )
                    ->getStartColor()
                    ->setRGB(
                        '6B5A99'
                    );

                $sheet
                    ->getStyle(
                        "A{$summaryRow}:C{$summaryRow}"
                    )
                    ->getFont()
                    ->setBold(true)
                    ->getColor()
                    ->setRGB(
                        'FFFFFF'
                    );

                $summary = [
                    'Total de salidas' =>
                        $totalSalidas,

                    'Total de ítems' =>
                        $this->rows->count(),

                    'Total de metros' =>
                        $totalMetros,

                    'Total de rollos' =>
                        $totalRollos,

                    'Importe total' =>
                        'S/ '
                        . number_format(
                            $importeTotal,
                            2,
                            '.',
                            ','
                        ),
                ];

                $r = $summaryRow + 1;

                foreach (
                    $summary as $label => $value
                ) {

                    $sheet->setCellValue(
                        "A{$r}",
                        $label
                    );

                    $sheet->setCellValue(
                        "B{$r}",
                        $value
                    );

                    $r++;
                }

                /*
                |--------------------------------------------------------------------------
                | BORDES
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getStyle(
                        "A11:N{$totalRow}"
                    )
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        Border::BORDER_THIN
                    )
                    ->getColor()
                    ->setRGB(
                        'CBD5E1'
                    );

                /*
                |--------------------------------------------------------------------------
                | FILTRO DE EXCEL
                |--------------------------------------------------------------------------
                */

                if ($lastRow >= $firstRow) {

                    $sheet->setAutoFilter(
                        "A11:N{$lastRow}"
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | CONGELAR ENCABEZADO
                |--------------------------------------------------------------------------
                */

                $sheet->freezePane(
                    'A12'
                );
            },
        ];
    }
}