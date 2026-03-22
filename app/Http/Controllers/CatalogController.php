<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Store;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Store::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code_product', 'like', "%{$search}%")
                ->orWhere('name_product', 'like', "%{$search}%");
            });
        }

        if ($request->filled('fabric_type')) {
            $query->where('fabric_type', 'like', "%{$request->fabric_type}%");
        }

        $products = $query->paginate(10)->withQueryString();

        $products->getCollection()->transform(function ($product) {
            $imagePath = $product->image_path ?? $product->image ?? null;
            $product->image_url = $imagePath ? asset('storage/' . $imagePath) : null;
            return $product;
        });

        return Inertia::render('Catalog/Index', [
            'products' => $products,
            'filters' => $request->only(['search', 'fabric_type']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
{
    $store = Store::with('warehouseStocks.warehouse:id,name,code')
        ->findOrFail($id);

    $imagePath = $store->image_path ?? $store->image;

    $store->image_url = $imagePath && file_exists(public_path('storage/' . $imagePath))
        ? asset('storage/' . $imagePath)
        : null;

    $stockByLocation = $store->warehouseStocks->map(function ($stock) {
        return [
            'id' => $stock->id,
            'warehouse_id' => $stock->warehouse_id,
            'warehouse_name' => $stock->warehouse?->name,
            'warehouse_code' => $stock->warehouse?->code,
            'kilos_available' => (float) $stock->kilos_available,
            'metros_available' => (float) $stock->metros_available,
            'kilos_reserved' => (float) $stock->kilos_reserved,
            'metros_reserved' => (float) $stock->metros_reserved,
        ];
    });

    return Inertia::render('Catalog/Show', [
        'product' => [
            'id' => $store->id,
            'code_product' => $store->code_product,
            'name_product' => $store->name_product,
            'fabric_type' => $store->fabric_type,
            'color' => $store->color,
            'proveedor' => $store->proveedor,
            'price' => $store->price,
            'public_price' => $store->public_price,
            'wholesale_price' => $store->wholesale_price,
            'price_roll' => $store->price_roll,
            'special_price' => $store->special_price,
            'location' => $store->location,
            'description' => $store->description,
            'image_url' => $store->image_url,
        ],
        'stock_summary' => [
            'kilos_available' => $stockByLocation->sum('kilos_available'),
            'metros_available' => $stockByLocation->sum('metros_available'),
            'kilos_reserved' => $stockByLocation->sum('kilos_reserved'),
            'metros_reserved' => $stockByLocation->sum('metros_reserved'),
        ],
        'stock_locations' => $stockByLocation,
    ]);
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
