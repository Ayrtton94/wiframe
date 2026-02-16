<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseStockRequest;
use App\Models\Store;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Inertia\Inertia;

class WarehouseStockController extends Controller
{
    /**
     * Display a listing of warehouse stocks.
     */
    public function index()
    {
        $stocks = WarehouseStock::query()
            ->with(['warehouse:id,name,code', 'store:id,code_product,name_product'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $warehouses = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $products = Store::query()
            ->orderBy('name_product')
            ->get(['id', 'code_product', 'name_product']);

        return Inertia::render('WarehouseStock/Index', [
            'stocks' => $stocks,
            'warehouses' => $warehouses,
            'products' => $products,
        ]);
    }

    /**
     * Store or update stock for a warehouse and product pair.
     */
    public function store(WarehouseStockRequest $request)
    {
        $validated = $request->validated();

        WarehouseStock::updateOrCreate(
            [
                'warehouse_id' => $validated['warehouse_id'],
                'store_id' => $validated['store_id'],
            ],
            [
                'kilos_available' => (float) ($validated['kilos_available'] ?? 0),
                'metros_available' => (float) ($validated['metros_available'] ?? 0),
                'kilos_reserved' => (float) ($validated['kilos_reserved'] ?? 0),
                'metros_reserved' => (float) ($validated['metros_reserved'] ?? 0),
            ],
        );

        return back()->with('success', 'Stock guardado correctamente.');
    }

    /**
     * Update the specified stock row.
     */
    public function update(WarehouseStockRequest $request, WarehouseStock $warehouseStock)
    {
        $validated = $request->validated();

        $warehouseStock->update([
            'warehouse_id' => $validated['warehouse_id'],
            'store_id' => $validated['store_id'],
            'kilos_available' => (float) ($validated['kilos_available'] ?? 0),
            'metros_available' => (float) ($validated['metros_available'] ?? 0),
            'kilos_reserved' => (float) ($validated['kilos_reserved'] ?? 0),
            'metros_reserved' => (float) ($validated['metros_reserved'] ?? 0),
        ]);

        return back()->with('success', 'Stock actualizado correctamente.');
    }

    /**
     * Remove the specified stock row.
     */
    public function destroy(WarehouseStock $warehouseStock)
    {
        $warehouseStock->delete();

        return back()->with('success', 'Stock eliminado correctamente.');
    }
}