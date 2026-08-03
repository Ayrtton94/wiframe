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

        $search = trim((string) $request->input('search', ''));

        $stocksQuery = WarehouseStock::query()
            ->with(['warehouse:id,name,code', 'store:id,code_product,name_product'])
            ->latest();

        if ($assignedWarehouseIds !== null) {
            $stocksQuery->whereIn('warehouse_id', $assignedWarehouseIds);
        }

        if ($search !== '') {
            $stocksQuery->whereHas('store', function ($query) use ($search) {
                $query->where('code_product', 'like', "%{$search}%")
                    ->orWhere('name_product', 'like', "%{$search}%");
            })->orWhereHas('warehouse', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $stocks = $stocksQuery
            ->paginate(20)
            ->appends(['search' => $search])
            ->withQueryString();

        $warehousesQuery = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($assignedWarehouseIds !== null) {
            $warehousesQuery->whereIn('id', $assignedWarehouseIds);
        }

        $warehouses = $warehousesQuery->get(['id', 'name', 'code']);

        $products = Store::query()
            ->where('is_active', true)
            ->orderBy('name_product')
            ->get(['id', 'code_product', 'name_product', 'kilos', 'metros']);

        return Inertia::render('WarehouseStock/Index', [
            'stocks' => $stocks,
            'warehouses' => $warehouses,
            'products' => $products,
            'filters' => ['search' => $search],
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

        // Ajustar el total del producto (`stores.kilos` / `stores.metros`) según la diferencia
        $product = Store::findOrFail($validated['store_id']);

        $existing = WarehouseStock::query()
            ->where('warehouse_id', $validated['warehouse_id'])
            ->where('store_id', $validated['store_id'])
            ->first();

        $kilosBefore = $existing ? (float) $existing->kilos_available : 0.0;
        $metrosBefore = $existing ? (float) $existing->metros_available : 0.0;

        $kilosNew = (int) round((float) ($validated['kilos_available'] ?? 0));
        $metrosNew = (int) round((float) ($validated['metros_available'] ?? 0));

        $deltaKilos = $kilosNew - $kilosBefore;
        $deltaMetros = $metrosNew - $metrosBefore;

        if ($deltaKilos > 0) {
            $product->decrement('kilos', $deltaKilos);
        } elseif ($deltaKilos < 0) {
            $product->increment('kilos', abs($deltaKilos));
        }

        if ($deltaMetros > 0) {
            $product->decrement('metros', $deltaMetros);
        } elseif ($deltaMetros < 0) {
            $product->increment('metros', abs($deltaMetros));
        }

        WarehouseStock::updateOrCreate(
            [
                'warehouse_id' => $validated['warehouse_id'],
                'store_id' => $validated['store_id'],
            ],
            [
                'kilos_available' => $kilosNew,
                'metros_available' => $metrosNew,
                'kilos_reserved' => 0,
                'metros_reserved' => 0,
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

        // Ajustar el total del producto según diferencia entre valores nuevos y anteriores
        $product = Store::findOrFail($validated['store_id']);

        $kilosBefore = (float) $warehouseStock->kilos_available;
        $metrosBefore = (float) $warehouseStock->metros_available;

        $kilosNew = (int) round((float) ($validated['kilos_available'] ?? 0));
        $metrosNew = (int) round((float) ($validated['metros_available'] ?? 0));

        $deltaKilos = $kilosNew - $kilosBefore;
        $deltaMetros = $metrosNew - $metrosBefore;

        if ($deltaKilos > 0) {
            $product->decrement('kilos', $deltaKilos);
        } elseif ($deltaKilos < 0) {
            $product->increment('kilos', abs($deltaKilos));
        }

        if ($deltaMetros > 0) {
            $product->decrement('metros', $deltaMetros);
        } elseif ($deltaMetros < 0) {
            $product->increment('metros', abs($deltaMetros));
        }

        $warehouseStock->update([
            'warehouse_id' => $validated['warehouse_id'],
            'store_id' => $validated['store_id'],
            'kilos_available' => $kilosNew,
            'metros_available' => $metrosNew,
            'kilos_reserved' => 0,
            'metros_reserved' => 0,
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