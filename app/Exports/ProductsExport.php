<?php

namespace App\Exports;

use App\Models\Store;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Store::query()
            ->with('warehouseStocks.warehouse')
            ->orderBy('name_product')
            ->get();
    }

    public function map($product): array
    {
        return $product->warehouseStocks->map(function ($stock) use ($product) {
            return [
                $product->code_product,
                $product->name_product,
                $product->fabric_type,
                $product->color,
                $product->proveedor,
                $product->price,
                $product->public_price,
                $product->wholesale_price,
                $product->price_roll,
                $product->special_price,
                $product->minimum_stock,
                $stock->warehouse?->code,
                $stock->warehouse?->name,
                $stock->kilos_available,
                $stock->metros_available,
                $stock->kilos_reserved,
                $stock->metros_reserved,
                $product->is_active ? 'Activo' : 'Inactivo',
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Código producto',
            'Producto',
            'Tipo de tela',
            'Color',
            'Proveedor',
            'Precio',
            'Precio público',
            'Precio mayorista',
            'Precio rollo',
            'Precio especial',
            'Stock mínimo',
            'Código almacén',
            'Almacén',
            'Kilos disponibles',
            'Metros disponibles',
            'Kilos reservados',
            'Metros reservados',
            'Estado',
        ];
    }

    public function title(): string
    {
        return 'Productos';
    }
}
