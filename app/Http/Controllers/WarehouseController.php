<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index()
    {
        $warehouses = Warehouse::query()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Warehouse/Index', [
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Store a newly created warehouse in storage.
     */
    public function store(WarehouseRequest $request)
    {
        Warehouse::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Almacén creado correctamente.');
    }

    /**
     * Update the specified warehouse in storage.
     */
    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Almacén actualizado correctamente.');
    }

    /**
     * Remove the specified warehouse from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->stocks()->exists()) {
            return back()->with('error', 'No puedes eliminar un almacén con stock asociado.');
        }

        $warehouse->delete();

        return back()->with('success', 'Almacén eliminado correctamente.');
    }
}
