<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRequest;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       $products = Store::all()->map(function ($product) {
            $product->image_path = $product->image_path ? asset('storage/' . $product->image_path) : null;
            return $product;
        });
        return Inertia::render("Store/Index", ['products' => $products]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return Inertia::render("Store/Create");
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
        return Inertia::render("Store/Edit", ['product' => $store]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Store $store)
    {
        $store->update($request->validated());

        return redirect()->route('stores.index')->with('success', 'Producto actualizado exitosamente.');
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
