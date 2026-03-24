<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Store;
use App\Models\Suppliers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreRequest;

class StoreController extends Controller
{
    public function getRouteKeyName()
    {
        return 'code_product';
    }
    public function index(Request $request)
    {
        $products = Store::paginate(10);
        $products->getCollection()->transform(function ($product) {
            $imagePath = $product->image_path ?? $product->image ?? null;
            $product->image_url = $imagePath ? asset('storage/' . $imagePath) : null;

            return $product;
        });

        return Inertia::render("Store/Index", [
            'products' => $products,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Store/Create', [
            'suppliers' => Suppliers::query()
                ->orderBy('company_name')
                ->get(['id', 'company_name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')->store('stores', 'public');
        }

        Store::create($validated);

        return redirect()->route('stores.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Store $store)
    {
        $imagePath = $store->image_path ?? $store->image ?? null;
        $store->image_url = $imagePath ? asset('storage/' . $imagePath) : null;
        return Inertia::render('Store/Edit', [
            'product' => $store,
            'suppliers' => Suppliers::query()
                ->orderBy('company_name')
                ->get(['id', 'company_name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
public function update(StoreRequest $request, Store $store)
{
    $validated = $request->validated();

        $imageColumn = Schema::hasColumn('stores', 'image_path')
            ? 'image_path'
            : (Schema::hasColumn('stores', 'image') ? 'image' : null);

        if ($imageColumn !== null && $request->hasFile('image')) {
            $existingPath = $store->{$imageColumn};
            if ($existingPath) {
                Storage::disk('public')->delete($existingPath);
            }

            $validated[$imageColumn] = $request->file('image')->store('products', 'public');
        }

        $store->update($validated);

    return redirect()
        ->route('stores.index')
        ->with('success', 'Producto actualizado exitosamente.');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->route('stores.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
