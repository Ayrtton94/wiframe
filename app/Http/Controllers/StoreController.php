<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Store;
use App\Models\Suppliers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
        unset($validated['image'], $validated['image_path']);

        $imageFile = $request->file('image') ?? $request->file('image_path');
        if ($imageFile) {
            $validated['image_path'] = $imageFile->store('stores', 'public');
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
    unset($validated['image'], $validated['image_path']);

        $imageColumn = Schema::hasColumn('stores', 'image_path')
            ? 'image_path'
            : (Schema::hasColumn('stores', 'image') ? 'image' : null);
 
            $imageFile = $request->file('image') ?? $request->file('image_path');

        if ($imageColumn !== null && $imageFile) {
            $existingPath = $store->{$imageColumn};
            if ($existingPath) {
                Storage::disk('public')->delete($existingPath);
            }

            $validated[$imageColumn] = $imageFile->store('stores', 'public');
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

     public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx'],
        ], [
            'file.mimes' => 'El archivo debe ser CSV o XLSX (puedes exportarlo desde Excel).',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return back()->withErrors([
                'file' => 'No se pudo leer el archivo seleccionado.',
            ]);
        }

        $headers = fgetcsv($handle);
        if (!is_array($headers) || count($headers) === 0) {
            fclose($handle);

            return back()->withErrors([
                'file' => 'El archivo no contiene encabezados válidos.',
            ]);
        }

        $normalizedHeaders = array_map(fn ($header) => trim((string) $header), $headers);
        $requiredHeaders = [
            'code_product',
            'name_product',
            'fabric_type',
            'color',
            'proveedor',
            'kilos',
            'metros',
            'minimum_stock',
            'price',
            'public_price',
            'wholesale_price',
            'price_roll',
            'special_price',
            'location',
            'description',
        ];

        $missingHeaders = array_diff($requiredHeaders, $normalizedHeaders);
        if (!empty($missingHeaders)) {
            fclose($handle);

            return back()->withErrors([
                'file' => 'Faltan columnas requeridas: ' . implode(', ', $missingHeaders),
            ]);
        }

        $rowsImported = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if ($this->isEmptyCsvRow($row)) {
                continue;
            }

            $payload = array_combine($normalizedHeaders, $row);
            if (!is_array($payload)) {
                continue;
            }

            $codeProduct = trim((string) ($payload['code_product'] ?? ''));
            $nameProduct = trim((string) ($payload['name_product'] ?? ''));

            if ($codeProduct === '' || $nameProduct === '') {
                continue;
            }

            Store::updateOrCreate(
                ['code_product' => $codeProduct],
                [
                    'name_product' => $nameProduct,
                    'fabric_type' => trim((string) ($payload['fabric_type'] ?? '')),
                    'color' => trim((string) ($payload['color'] ?? '')),
                    'proveedor' => trim((string) ($payload['proveedor'] ?? '')),
                    'kilos' => (string) ($payload['kilos'] ?? '0'),
                    'metros' => (string) ($payload['metros'] ?? '0'),
                    'minimum_stock' => (int) ($payload['minimum_stock'] ?? 0),
                    'price' => (float) ($payload['price'] ?? 0),
                    'public_price' => (float) ($payload['public_price'] ?? 0),
                    'wholesale_price' => (float) ($payload['wholesale_price'] ?? 0),
                    'price_roll' => (float) ($payload['price_roll'] ?? 0),
                    'special_price' => (float) ($payload['special_price'] ?? 0),
                    'location' => trim((string) ($payload['location'] ?? '')),
                    'description' => trim((string) ($payload['description'] ?? '')),
                ]
            );

            $rowsImported++;
        }

        fclose($handle);

        return to_route('stores.index')->with(
            'success',
            "Importación completada. Registros procesados: {$rowsImported}."
        );
    }

    private function isEmptyCsvRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

}
