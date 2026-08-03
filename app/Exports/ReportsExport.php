<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsExport implements WithMultipleSheets
{
    protected $salesByWarehouse;

    protected $inventoryByWarehouse;

    protected $filters;

    public function __construct($salesByWarehouse, $inventoryByWarehouse, $filters)
    {
        $this->salesByWarehouse = $salesByWarehouse;
        $this->inventoryByWarehouse = $inventoryByWarehouse;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new SalesReportSheet($this->salesByWarehouse, $this->filters),
            new InventoryReportSheet($this->inventoryByWarehouse, $this->filters),
        ];
    }
}

class SalesReportSheet implements FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings
{
    protected $rows;

    protected $filters;

    public function __construct($rows, $filters)
    {
        $this->rows = $rows;
        $this->filters = $filters;
    }

    public function array(): array
    {
        return $this->rows->map(function ($row) {
            return [
                'warehouse_code' => $row->warehouse_code,
                'warehouse_name' => $row->warehouse_name,
                'sale_date' => $row->sale_date,
                'seller_name' => $row->seller_name,
                'sales_count' => (int) $row->sales_count,
                'total_units' => number_format((float) $row->total_units, 2, '.', ''),
                'total_sales' => number_format((float) $row->total_sales, 2, '.', ''),
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return ['Código', 'Nombre', 'Fecha', 'Vendedor', 'N° ventas', 'Unidades', 'Total vendido'];
    }

    public function title(): string
    {
        return 'Ventas';
    }
}

class InventoryReportSheet implements FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings
{
    protected $rows;

    protected $filters;

    public function __construct($rows, $filters)
    {
        $this->rows = $rows;
        $this->filters = $filters;
    }

    public function array(): array
    {
        return $this->rows->map(function ($row) {
            return [
                'warehouse_code' => $row->warehouse_code,
                'warehouse_name' => $row->warehouse_name,
                'code_product' => $row->code_product,
                'name_product' => $row->name_product,
                'kilos_available' => number_format((float) $row->kilos_available, 3, '.', ''),
                'metros_available' => number_format((float) $row->metros_available, 3, '.', ''),
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return ['Almacén', 'Nombre', 'Código producto', 'Producto', 'Kilos disp.', 'Metros disp.'];
    }

    public function title(): string
    {
        return 'Inventario';
    }
}
