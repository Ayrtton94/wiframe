<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransferenciasExport implements
    FromArray,
    WithColumnWidths,
    WithEvents,
    WithTitle
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
    | NOMBRE DE LA HOJA
    |--------------------------------------------------------------------------
    */

    public function title(): string
    {
        return 'Transferencias';
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
                    /*
                    |--------------------------------------------------------------------------
                    | FECHA SOLICITUD
                    |--------------------------------------------------------------------------
                    */

                    $this->dateTime(
                        $row->fecha_solicitud
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | CÓDIGO
                    |--------------------------------------------------------------------------
                    */

                    $row->transferencia_code
                        ?? '',

                    /*
                    |--------------------------------------------------------------------------
                    | ORIGEN
                    |--------------------------------------------------------------------------
                    */

                    $row->almacen_origen
                        ?? '',

                    /*
                    |--------------------------------------------------------------------------
                    | DESTINO
                    |--------------------------------------------------------------------------
                    */

                    $row->almacen_destino
                        ?? '',

                    /*
                    |--------------------------------------------------------------------------
                    | PRODUCTO
                    |--------------------------------------------------------------------------
                    */

                    ($row->codigo_producto ?? '')
                    . ' - '
                    . ($row->producto ?? ''),

                    /*
                    |--------------------------------------------------------------------------
                    | ROLLOS
                    |--------------------------------------------------------------------------
                    */

                    $this->number(
                        $row->rollos_solicitados
                    ),

                    $this->number(
                        $row->rollos_despachados
                    ),

                    $this->number(
                        $row->rollos_recibidos
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | METROS
                    |--------------------------------------------------------------------------
                    */

                    $this->number(
                        $row->metros_solicitados
                    ),

                    $this->number(
                        $row->metros_despachados
                    ),

                    $this->number(
                        $row->metros_recibidos
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | ESTADO
                    |--------------------------------------------------------------------------
                    */

                    $this->statusLabel(
                        $row->status
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | DESPACHO
                    |--------------------------------------------------------------------------
                    */

                    $this->dateTime(
                        $row->fecha_despacho
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | RECEPCIÓN
                    |--------------------------------------------------------------------------
                    */

                    $this->dateTime(
                        $row->fecha_recepcion
                    ),
                ];
            })
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | FECHA + HORA
    |--------------------------------------------------------------------------
    */

    private function dateTime(
        mixed $value
    ): string {
        if (!$value) {
            return '';
        }

        return Carbon::parse(
            $value
        )->format(
            'd/m/Y h:i:s A'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NÚMEROS
    |--------------------------------------------------------------------------
    |
    | 30    -> 30
    | 30.00 -> 30
    | 30.50 -> 30.5
    |
    */

    private function number(
        mixed $value
    ): int|float|null {
        if ($value === null) {
            return null;
        }

        $number = round(
            (float) $value,
            3
        );

        if (
            abs(
                $number - round($number)
            ) < 0.000001
        ) {
            return (int) round(
                $number
            );
        }

        return $number;
    }

    /*
    |--------------------------------------------------------------------------
    | ESTADO
    |--------------------------------------------------------------------------
    */

    private function statusLabel(
        mixed $status
    ): string {
        $status = strtolower(
            trim(
                (string) $status
            )
        );

        return match ($status) {
            'received',
            'recibido',
            'receibido',
            'completed',
            'completado' =>
                'Recibida',

            'partial',
            'parcial' =>
                'Parcial',

            'pending',
            'pendiente' =>
                'Pendiente',

            'cancelled',
            'cancelado' =>
                'Cancelada',

            'shipped',
            'despachado' =>
                'Despachada',

            default =>
                $status !== ''
                    ? ucfirst($status)
                    : '',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | VALOR DEL FILTRO
    |--------------------------------------------------------------------------
    */

    private function filterText(
        string $key,
        string $default = 'Todos'
    ): string {
        $value =
            $this->filters[$key]
            ?? null;

        return $value !== null &&
            $value !== ''
            ? (string) $value
            : $default;
    }

    /*
    |--------------------------------------------------------------------------
    | FECHA DEL FILTRO
    |--------------------------------------------------------------------------
    */

    private function filterDate(
        string $key
    ): string {
        $value =
            $this->filters[$key]
            ?? null;

        if (!$value) {
            return '';
        }

        return Carbon::parse(
            $value
        )->format(
            'd/m/Y'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | COLUMNAS
    |--------------------------------------------------------------------------
    */

    public function columnWidths(): array
    {
        return [
            'A' => 22, // Fecha solicitud
            'B' => 24, // Código
            'C' => 23, // Origen
            'D' => 23, // Destino
            'E' => 30, // Producto

            'F' => 16, // Rollos solicitados
            'G' => 16, // Rollos despachados
            'H' => 16, // Rollos recibidos

            'I' => 16, // Metros solicitados
            'J' => 16, // Metros despachados
            'K' => 16, // Metros recibidos

            'L' => 15, // Estado
            'M' => 21, // Despacho
            'N' => 21, // Recepción
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ESTILOS
    |--------------------------------------------------------------------------
    */

    public function registerEvents(): array
    {
        return [
            AfterSheet::class =>
                function (
                    AfterSheet $event
                ) {

                    /** @var Worksheet $sheet */
                    $sheet =
                        $event
                            ->sheet
                            ->getDelegate();

                    /*
                    |--------------------------------------------------------------------------
                    | RESERVAR ESPACIO
                    |--------------------------------------------------------------------------
                    */

                    $sheet->insertNewRowBefore(
                        1,
                        9
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | TÍTULO
                    |--------------------------------------------------------------------------
                    */

                    $sheet->setCellValue(
                        'A1',
                        'REPORTE DE TRANSFERENCIAS'
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
                        ->setHorizontal(
                            Alignment::HORIZONTAL_LEFT
                        )
                        ->setVertical(
                            Alignment::VERTICAL_CENTER
                        );

                    $sheet
                        ->getRowDimension(1)
                        ->setRowHeight(28);

                    /*
                    |--------------------------------------------------------------------------
                    | DESCRIPCIÓN
                    |--------------------------------------------------------------------------
                    */

                    $sheet->setCellValue(
                        'A2',
                        'Histórico de movimientos entre almacenes.'
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
                            '718096'
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | FILTROS
                    |--------------------------------------------------------------------------
                    */

                    $sheet->setCellValue(
                        'A4',
                        'Fecha inicio'
                    );

                    $sheet->setCellValue(
                        'B4',
                        $this->filterDate('from')
                    );

                    $sheet->setCellValue(
                        'C4',
                        'Fecha fin'
                    );

                    $sheet->setCellValue(
                        'D4',
                        $this->filterDate('to')
                    );

                    $sheet->setCellValue(
                        'E4',
                        'Almacén origen'
                    );

                    $sheet->setCellValue(
                        'F4',
                        $this->filterText(
                            'from_warehouse_id'
                        )
                    );

                    $sheet->setCellValue(
                        'G4',
                        'Almacén destino'
                    );

                    $sheet->setCellValue(
                        'H4',
                        $this->filterText(
                            'to_warehouse_id'
                        )
                    );

                    $sheet->setCellValue(
                        'I4',
                        'Estado'
                    );

                    $sheet->setCellValue(
                        'J4',
                        $this->filterText(
                            'status',
                            'Todos los estados'
                        )
                    );

                    $sheet->setCellValue(
                        'K4',
                        'Buscar'
                    );

                    $sheet->setCellValue(
                        'L4',
                        $this->filterText(
                            'search',
                            ''
                        )
                    );

                    $sheet->mergeCells(
                        'L4:N4'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | ESTILO FILTROS
                    |--------------------------------------------------------------------------
                    */

                    $sheet
                        ->getStyle('A4:N4')
                        ->getFont()
                        ->setSize(9);

                    foreach (
                        [
                            'A4',
                            'C4',
                            'E4',
                            'G4',
                            'I4',
                            'K4',
                        ] as $cell
                    ) {
                        $sheet
                            ->getStyle($cell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB(
                                '8A99AE'
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | INDICADORES
                    |--------------------------------------------------------------------------
                    */

                    $transferencias =
                        $this->rows
                            ->pluck('id')
                            ->unique()
                            ->count();

                    $metrosSolicitados =
                        $this->rows->sum(
                            fn ($row) =>
                                (float) (
                                    $row->metros_solicitados
                                    ?? 0
                                )
                        );

                    $metrosDespachados =
                        $this->rows->sum(
                            fn ($row) =>
                                (float) (
                                    $row->metros_despachados
                                    ?? 0
                                )
                        );

                    $metrosRecibidos =
                        $this->rows->sum(
                            fn ($row) =>
                                (float) (
                                    $row->metros_recibidos
                                    ?? 0
                                )
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | CARDS
                    |--------------------------------------------------------------------------
                    */

                    $cards = [
                        [
                            'A',
                            'C',
                            'Transferencias',
                            $transferencias,
                        ],
                        [
                            'D',
                            'F',
                            'Metros solicitados',
                            $this->number(
                                $metrosSolicitados
                            ),
                        ],
                        [
                            'G',
                            'I',
                            'Metros despachados',
                            $this->number(
                                $metrosDespachados
                            ),
                        ],
                        [
                            'J',
                            'L',
                            'Metros recibidos',
                            $this->number(
                                $metrosRecibidos
                            ),
                        ],
                    ];

                    foreach (
                        $cards as $card
                    ) {

                        [
                            $start,
                            $end,
                            $label,
                            $value
                        ] = $card;

                        $sheet->mergeCells(
                            "{$start}6:{$end}6"
                        );

                        $sheet->mergeCells(
                            "{$start}7:{$end}7"
                        );

                        $sheet->setCellValue(
                            "{$start}6",
                            $label
                        );

                        $sheet->setCellValue(
                            "{$start}7",
                            $value
                        );

                        $sheet
                            ->getStyle(
                                "{$start}6:{$end}6"
                            )
                            ->getFill()
                            ->setFillType(
                                Fill::FILL_SOLID
                            )
                            ->getStartColor()
                            ->setRGB(
                                '172A46'
                            );

                        $sheet
                            ->getStyle(
                                "{$start}6:{$end}6"
                            )
                            ->getFont()
                            ->setBold(true)
                            ->setSize(10)
                            ->getColor()
                            ->setRGB(
                                'FFFFFF'
                            );

                        $sheet
                            ->getStyle(
                                "{$start}7:{$end}7"
                            )
                            ->getFill()
                            ->setFillType(
                                Fill::FILL_SOLID
                            )
                            ->getStartColor()
                            ->setRGB(
                                'EEF2F7'
                            );

                        $sheet
                            ->getStyle(
                                "{$start}7:{$end}7"
                            )
                            ->getFont()
                            ->setBold(true)
                            ->setSize(13)
                            ->getColor()
                            ->setRGB(
                                '17263D'
                            );

                        $sheet
                            ->getStyle(
                                "{$start}6:{$end}7"
                            )
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
                    | ENCABEZADOS
                    |--------------------------------------------------------------------------
                    */

                    $headers = [
                        'A9' => 'FECHA SOLICITUD',
                        'B9' => 'CÓDIGO',
                        'C9' => 'ORIGEN',
                        'D9' => 'DESTINO',
                        'E9' => 'PRODUCTO',

                        'F9' => 'ROLLOS SOLICITADOS',
                        'G9' => 'ROLLOS DESPACHADOS',
                        'H9' => 'ROLLOS RECIBIDOS',

                        'I9' => 'METROS SOLICITADOS',
                        'J9' => 'METROS DESPACHADOS',
                        'K9' => 'METROS RECIBIDOS',

                        'L9' => 'ESTADO',
                        'M9' => 'DESPACHO',
                        'N9' => 'RECEPCIÓN',
                    ];

                    foreach (
                        $headers as $cell => $value
                    ) {
                        $sheet->setCellValue(
                            $cell,
                            $value
                        );
                    }

                    $sheet
                        ->getStyle(
                            'A9:N9'
                        )
                        ->getFill()
                        ->setFillType(
                            Fill::FILL_SOLID
                        )
                        ->getStartColor()
                        ->setRGB(
                            '111C34'
                        );

                    $sheet
                        ->getStyle(
                            'A9:N9'
                        )
                        ->getFont()
                        ->setBold(true)
                        ->setSize(9)
                        ->getColor()
                        ->setRGB(
                            'FFFFFF'
                        );

                    $sheet
                        ->getStyle(
                            'A9:N9'
                        )
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
                    | DATOS
                    |--------------------------------------------------------------------------
                    */

                    $firstDataRow = 10;

                    $lastDataRow =
                        $firstDataRow
                        + $this->rows->count()
                        - 1;

                    /*
                    |--------------------------------------------------------------------------
                    | FORMATO NUMÉRICO
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lastDataRow >=
                        $firstDataRow
                    ) {

                        $sheet
                            ->getStyle(
                                "F{$firstDataRow}:K{$lastDataRow}"
                            )
                            ->getNumberFormat()
                            ->setFormatCode(
                                '0'
                            );

                        $sheet
                            ->getStyle(
                                "F{$firstDataRow}:K{$lastDataRow}"
                            )
                            ->getAlignment()
                            ->setHorizontal(
                                Alignment::HORIZONTAL_RIGHT
                            );

                        /*
                        | Código y fechas
                        */

                        $sheet
                            ->getStyle(
                                "A{$firstDataRow}:N{$lastDataRow}"
                            )
                            ->getAlignment()
                            ->setVertical(
                                Alignment::VERTICAL_CENTER
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ESTADO VERDE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lastDataRow >=
                        $firstDataRow
                    ) {

                        for (
                            $row = $firstDataRow;
                            $row <= $lastDataRow;
                            $row++
                        ) {

                            $status =
                                strtolower(
                                    trim(
                                        (string) (
                                            $sheet->getCell(
                                                "L{$row}"
                                            )->getValue()
                                            ?? ''
                                        )
                                    )
                                );

                            if (
                                $status ===
                                'recibida'
                            ) {

                                $sheet
                                    ->getStyle(
                                        "L{$row}"
                                    )
                                    ->getFill()
                                    ->setFillType(
                                        Fill::FILL_SOLID
                                    )
                                    ->getStartColor()
                                    ->setRGB(
                                        'D9F5E6'
                                    );

                                $sheet
                                    ->getStyle(
                                        "L{$row}"
                                    )
                                    ->getFont()
                                    ->setBold(true)
                                    ->getColor()
                                    ->setRGB(
                                        '15803D'
                                    );
                            }

                            if (
                                $status ===
                                'parcial'
                            ) {

                                $sheet
                                    ->getStyle(
                                        "L{$row}"
                                    )
                                    ->getFill()
                                    ->setFillType(
                                        Fill::FILL_SOLID
                                    )
                                    ->getStartColor()
                                    ->setRGB(
                                        'FFF0C2'
                                    );

                                $sheet
                                    ->getStyle(
                                        "L{$row}"
                                    )
                                    ->getFont()
                                    ->setBold(true)
                                    ->getColor()
                                    ->setRGB(
                                        'A16207'
                                    );
                            }
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL
                    |--------------------------------------------------------------------------
                    */

                    $totalRow =
                        max(
                            $lastDataRow + 1,
                            $firstDataRow
                        );

                    $sheet->setCellValue(
                        "A{$totalRow}",
                        'TOTAL'
                    );

                    $sheet->mergeCells(
                        "A{$totalRow}:E{$totalRow}"
                    );

                    $sheet->setCellValue(
                        "F{$totalRow}",
                        $this->number(
                            $this->rows->sum(
                                fn ($row) =>
                                    (float) (
                                        $row->rollos_solicitados
                                        ?? 0
                                    )
                            )
                        )
                    );

                    $sheet->setCellValue(
                        "G{$totalRow}",
                        $this->number(
                            $this->rows->sum(
                                fn ($row) =>
                                    (float) (
                                        $row->rollos_despachados
                                        ?? 0
                                    )
                            )
                        )
                    );

                    $sheet->setCellValue(
                        "H{$totalRow}",
                        $this->number(
                            $this->rows->sum(
                                fn ($row) =>
                                    (float) (
                                        $row->rollos_recibidos
                                        ?? 0
                                    )
                            )
                        )
                    );

                    $sheet->setCellValue(
                        "I{$totalRow}",
                        $this->number(
                            $this->rows->sum(
                                fn ($row) =>
                                    (float) (
                                        $row->metros_solicitados
                                        ?? 0
                                    )
                            )
                        )
                    );

                    $sheet->setCellValue(
                        "J{$totalRow}",
                        $this->number(
                            $this->rows->sum(
                                fn ($row) =>
                                    (float) (
                                        $row->metros_despachados
                                        ?? 0
                                    )
                            )
                        )
                    );

                    $sheet->setCellValue(
                        "K{$totalRow}",
                        $this->number(
                            $this->rows->sum(
                                fn ($row) =>
                                    (float) (
                                        $row->metros_recibidos
                                        ?? 0
                                    )
                            )
                        )
                    );

                    $sheet->setCellValue(
                        "L{$totalRow}",
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
                            "A{$totalRow}:N{$totalRow}"
                        )
                        ->getFill()
                        ->setFillType(
                            Fill::FILL_SOLID
                        )
                        ->getStartColor()
                        ->setRGB(
                            '172A46'
                        );

                    $sheet
                        ->getStyle(
                            "A{$totalRow}:N{$totalRow}"
                        )
                        ->getFont()
                        ->setBold(true)
                        ->setSize(10)
                        ->getColor()
                        ->setRGB(
                            'FFFFFF'
                        );

                    $sheet
                        ->getStyle(
                            "F{$totalRow}:K{$totalRow}"
                        )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_RIGHT
                        );

                    $sheet
                        ->getStyle(
                            "F{$totalRow}:K{$totalRow}"
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '0'
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | BORDES
                    |--------------------------------------------------------------------------
                    */

                    $sheet
                        ->getStyle(
                            "A9:N{$totalRow}"
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
                    | FILTRO EXCEL
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lastDataRow >=
                        $firstDataRow
                    ) {
                        $sheet->setAutoFilter(
                            "A9:N{$lastDataRow}"
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CONGELAR
                    |--------------------------------------------------------------------------
                    */

                    $sheet->freezePane(
                        'F10'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | ALTURAS
                    |--------------------------------------------------------------------------
                    */

                    $sheet
                        ->getRowDimension(9)
                        ->setRowHeight(38);

                    $sheet
                        ->getRowDimension(
                            $totalRow
                        )
                        ->setRowHeight(24);
                },
        ];
    }
}