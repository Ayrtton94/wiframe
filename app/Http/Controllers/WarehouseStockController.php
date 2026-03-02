<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseStockRequest;
use App\Models\Store;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseStockController extends Controller
{
    /**
     * Display a listing of warehouse stocks.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $assignedWarehouseIds = $user->hasRole('admin')
            ? null
            : $user->warehouses()->pluck('warehouses.id');

        $stocksQuery = WarehouseStock::query()
            ->with(['warehouse:id,name,code', 'store:id,code_product,name_product'])
            ->latest();

        if ($assignedWarehouseIds !== null) {
            $stocksQuery->whereIn('warehouse_id', $assignedWarehouseIds);
        }

        $stocks = $stocksQuery
            ->paginate(20)
            ->withQueryString();

        $warehousesQuery = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($assignedWarehouseIds !== null) {
            $warehousesQuery->whereIn('id', $assignedWarehouseIds);
        }

        $warehouses = $warehousesQuery->get(['id', 'name', 'code']);

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

        $user = $request->user();
        if (! $user->hasRole('admin')) {
            $assignedWarehouseIds = $user->warehouses()->pluck('warehouses.id');
            if (! $assignedWarehouseIds->contains($validated['warehouse_id'])) {
                abort(403, 'No puedes gestionar stock en almacenes no asignados.');
            }
        }

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

        $user = $request->user();
        if (! $user->hasRole('admin')) {
            $assignedWarehouseIds = $user->warehouses()->pluck('warehouses.id');
            if (! $assignedWarehouseIds->contains($validated['warehouse_id'])) {
                abort(403, 'No puedes gestionar stock en almacenes no asignados.');
            }
        }

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
    public function destroy(Request $request, WarehouseStock $warehouseStock)
    {
        $user = $request->user();
        if (! $user->hasRole('admin')) {
            $assignedWarehouseIds = $user->warehouses()->pluck('warehouses.id');
            if (! $assignedWarehouseIds->contains($warehouseStock->warehouse_id)) {
                abort(403, 'No puedes eliminar stock de almacenes no asignados.');
            }
        }

        $warehouseStock->delete();

        return back()->with('success', 'Stock eliminado correctamente.');
    }
}