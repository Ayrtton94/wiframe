<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Suppliers;
use Illuminate\Http\Request;
use App\Http\Requests\SupplierRequest;

class SuppliersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Suppliers::all();
        return Inertia::render("Supplier/Index", ['suppliers' => $suppliers]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("Supplier/Create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        Suppliers::create($request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Proveedor creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Suppliers $suppliers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Suppliers $supplier)
    {
        return Inertia::render("Supplier/Edit", ['supplier' => $supplier]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, Suppliers $supplier)
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Suppliers $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Proveedor eliminado exitosamente.');
    }
}
