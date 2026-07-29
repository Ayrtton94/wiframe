<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Suppliers;
use App\Http\Requests\SupplierRequest;
use Illuminate\Support\Arr;

class SuppliersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $suppliers = Suppliers::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('company_name', 'like', "%{$search}%")
                        ->orWhere('ruc', 'like', "%{$search}%");
                });
            })
            ->orderBy('company_name')
            ->paginate(10)
            ->appends(['search' => $search]);

        return Inertia::render('Supplier/Index', [
            'suppliers' => $suppliers,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("Supplier/Create", [
            'suppliers' => $suppliers,
            'filters' => ['search' => $search]
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        Suppliers::create($this->supplierData($request));

        return redirect()->route('suppliers.index')->with('success', 'Proveedor creado exitosamente.');
    }

     private function supplierData(SupplierRequest $request): array
    {
        $defaults = [
            'name' => '',
            'address' => '',
            'city' => '',
            'country' => '',
            'account' => '',
            'cod_swift' => '',
            'bank_name' => '',
            'bank_address' => '',
            'bank_city' => '',
            'bank_country' => '',
            'bank_cod_swift' => '',
        ];

        return array_merge($defaults, Arr::whereNotNull($request->validated()));
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
        $supplier->update($this->supplierData($request));

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
