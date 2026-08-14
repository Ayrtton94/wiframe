<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $assignedWarehouseIds = $user->hasRole('admin')
            ? null
            : $user->warehouses()->pluck('warehouses.id');

        $salesQuery = Sale::query()
            ->with([
                'customer:id,name', 
                'warehouse:id,name,code', 
                'seller:id,name',
                'items.store:id,code_product,name_product',
                ])
            ->latest();

        if ($assignedWarehouseIds !== null) {
            $salesQuery->whereIn('warehouse_id', $assignedWarehouseIds);
        }

        $sales = $salesQuery->paginate(15)->withQueryString();

        $warehousesQuery = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($assignedWarehouseIds !== null) {
            $warehousesQuery->whereIn('id', $assignedWarehouseIds);
        }

        $warehouses = $warehousesQuery->get(['id', 'name', 'code']);
        $defaultWarehouseId = $warehouses->count() === 1 ? $warehouses->first()->id : null;

        $warehouseStocks = WarehouseStock::query()
            ->with('warehouse:id,name,code')
            ->whereHas('warehouse', fn ($query) => $query->where('is_active', true));

        if ($assignedWarehouseIds !== null) {
            $warehouseStocks->whereIn('warehouse_id', $assignedWarehouseIds);
        }

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name', 'dni']),
            'warehouses' => $warehouses,
            'defaultWarehouseId' => $defaultWarehouseId,
            'warehouseStocks' => $warehouseStocks->get([
                'warehouse_id',
                'store_id',
                'kilos_available',
                'metros_available',
            ]),
            'products' => Store::query()
                ->where('is_active', true)
                ->orderBy('name_product')
                ->get([
                    'id',
                    'code_product',
                    'name_product',
                    'price',
                    'public_price',
                    'wholesale_price',
                    'price_roll',
                    'special_price',
                    'kilos',
                    'metros',
                ]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
        {

        }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        if (! $user->hasRole('admin')) {
            $assignedWarehouseIds = $user->warehouses()->pluck('warehouses.id');

                if (! $assignedWarehouseIds->contains($validated['warehouse_id'])) {
                    throw ValidationException::withMessages([
                        'warehouse_id' => 'Solo puedes vender con almacenes o tiendas asignadas a tu usuario.',
                    ]);
                }
            }

            $sale = DB::transaction(function () use ($validated, $user) {
                $subtotal = 0;

                $sale = Sale::create([
                    'code' => 'SAL-'.strtoupper((string) str()->ulid()),
                    'customer_id' => $validated['customer_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'sold_by' => $user->id,
                    'status' => 'completo',
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($validated['items'] as $index => $item) {
                    $product = Store::query()
                        ->where('is_active', true)
                        ->findOrFail($item['store_id']);
                    $stock = WarehouseStock::query()
                        ->where('warehouse_id', $validated['warehouse_id'])
                        ->where('store_id', $item['store_id'])
                        ->first();

                    if (! $stock) {
                        $stock = WarehouseStock::firstOrCreate(
                            [
                                'warehouse_id' => $validated['warehouse_id'],
                                'store_id' => $item['store_id'],
                            ],
                            [
                                'kilos_available' => (float) $product->kilos,
                                'metros_available' => (float) $product->metros,
                                'kilos_reserved' => 0,
                                'metros_reserved' => 0,
                            ],
                        );
                    }

                    $quantity = (float) $item['quantity'];
                    $unit = $item['unit'];

                    if ($unit === 'kilos' && (float) $stock->kilos_available < $quantity) {
                        throw ValidationException::withMessages([
                            "items.$index.quantity" => 'No hay kilos suficientes para este producto en la ubicación seleccionada.',
                        ]);
                    }

                    if ($unit === 'metros' && (float) $stock->metros_available < $quantity) {
                        throw ValidationException::withMessages([
                            "items.$index.quantity" => 'No hay metros suficientes para este producto en la ubicación seleccionada.',
                        ]);
                    }

                    $priceType = $item['price_type'] ?? 'public';
                    $unitPrice = match ($priceType) {
                        'wholesale' => (float) $product->wholesale_price,
                        'price_roll' => (float) $product->price_roll,
                        'special' => (float) ($product->special_price > 0 ? $product->special_price : $product->public_price),
                        'price' => (float) $product->price,
                        default => (float) $product->public_price,
                    };
                    $lineTotal = round($quantity * $unitPrice, 2);
                    $subtotal += $lineTotal;

                    if ($unit === 'kilos') {
                        $stock->decrement('kilos_available', (int) round($quantity));
                        $product->decrement('kilos', (int) round($quantity));
                    } else {
                        $stock->decrement('metros_available', (int) round($quantity));
                        $product->decrement('metros', (int) round($quantity));
                    }

                    $sale->items()->create([
                        'store_id' => $product->id,
                        'unit' => $unit,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ]);
                }

                $sale->update([
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                ]);

                return $sale;
            });

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Venta registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Sale $sale)
    {
        $user = $request->user();

        if (! $user->hasRole('admin')) {
            $assignedWarehouseIds = $user->warehouses()->pluck('warehouses.id');

            if (! $assignedWarehouseIds->contains($sale->warehouse_id)) {
                abort(403);
            }
        }

        $sale->load([
            'customer:id,name,dni',
            'warehouse:id,name,code',
            'seller:id,name',
            'items.store:id,code_product,name_product',
        ]);

        return Inertia::render('Sales/Show', [
            'sale' => $sale,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Sale $sale)
{
    $user = $request->user();

    // Verificar acceso al almacén
    if (! $user->hasRole('admin')) {
        $assignedWarehouseIds = $user->warehouses()
            ->pluck('warehouses.id');

        if (! $assignedWarehouseIds->contains($sale->warehouse_id)) {
            abort(403);
        }
    }

    $sale->load([
        'customer:id,name,dni',
        'warehouse:id,name,code',
        'seller:id,name',
        'items.store:id,code_product,name_product,price,public_price,wholesale_price,price_roll,special_price',
    ]);

    $warehousesQuery = Warehouse::query()
        ->where('is_active', true)
        ->orderBy('name');

    if (! $user->hasRole('admin')) {
        $warehousesQuery->whereIn(
            'id',
            $user->warehouses()->pluck('warehouses.id')
        );
    }

    $warehouses = $warehousesQuery->get([
        'id',
        'name',
        'code',
    ]);

    $products = Store::query()
        ->where('is_active', true)
        ->orderBy('name_product')
        ->get([
            'id',
            'code_product',
            'name_product',
            'price',
            'public_price',
            'wholesale_price',
            'price_roll',
            'special_price',
            'kilos',
            'metros',
        ]);

    return Inertia::render('Sales/Edit', [
        'sale' => $sale,
        'customers' => Customer::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'dni',
            ]),
        'warehouses' => $warehouses,
        'products' => $products,
    ]);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSaleRequest $request, Sale $sale)
{
    $validated = $request->validated();
    $user = $request->user();

    // Verificar que el usuario pueda trabajar con el almacén seleccionado
    if (! $user->hasRole('admin')) {
        $assignedWarehouseIds = $user->warehouses()
            ->pluck('warehouses.id');

        if (! $assignedWarehouseIds->contains($validated['warehouse_id'])) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'No tienes acceso a este almacén.',
            ]);
        }
    }

    DB::transaction(function () use ($validated, $sale) {

        /*
        |--------------------------------------------------------------------------
        | 1. DEVOLVER EL STOCK DE LA VENTA ANTERIOR
        |--------------------------------------------------------------------------
        */

        $sale->load('items');

        foreach ($sale->items as $oldItem) {

            $oldStock = WarehouseStock::query()
                ->where('warehouse_id', $sale->warehouse_id)
                ->where('store_id', $oldItem->store_id)
                ->first();

            if ($oldStock) {

                if ($oldItem->unit === 'kilos') {

                    $oldStock->increment(
                        'kilos_available',
                        $oldItem->quantity
                    );

                } elseif ($oldItem->unit === 'metros') {

                    $oldStock->increment(
                        'metros_available',
                        $oldItem->quantity
                    );
                }
            }

            // Devolver también al stock del producto
            $product = Store::find($oldItem->store_id);

            if ($product) {

                if ($oldItem->unit === 'kilos') {

                    $product->increment(
                        'kilos',
                        $oldItem->quantity
                    );

                } elseif ($oldItem->unit === 'metros') {

                    $product->increment(
                        'metros',
                        $oldItem->quantity
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. ELIMINAR LOS ITEMS ANTERIORES
        |--------------------------------------------------------------------------
        */

        $sale->items()->delete();

        /*
        |--------------------------------------------------------------------------
        | 3. ACTUALIZAR DATOS DE LA VENTA
        |--------------------------------------------------------------------------
        */

        $sale->update([
            'customer_id' => $validated['customer_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'completo',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 4. CREAR LOS NUEVOS ITEMS Y DESCONTAR STOCK
        |--------------------------------------------------------------------------
        */

        $subtotal = 0;

        foreach ($validated['items'] as $index => $item) {

            $product = Store::query()
                ->where('is_active', true)
                ->find($item['store_id']);

            if (! $product) {
                throw ValidationException::withMessages([
                    "items.$index.store_id" => 'El producto seleccionado no existe o está inactivo.',
                ]);
            }

            $stock = WarehouseStock::query()
                ->where('warehouse_id', $validated['warehouse_id'])
                ->where('store_id', $item['store_id'])
                ->first();

            if (! $stock) {
                throw ValidationException::withMessages([
                    "items.$index.store_id" =>
                        'El producto no tiene stock registrado en el almacén seleccionado.',
                ]);
            }

            $quantity = (float) $item['quantity'];
            $unit = $item['unit'];

            /*
            |--------------------------------------------------------------------------
            | VERIFICAR STOCK
            |--------------------------------------------------------------------------
            */

            if (
                $unit === 'kilos' &&
                (float) $stock->kilos_available < $quantity
            ) {
                throw ValidationException::withMessages([
                    "items.$index.quantity" =>
                        'No hay kilos suficientes para este producto.',
                ]);
            }

            if (
                $unit === 'metros' &&
                (float) $stock->metros_available < $quantity
            ) {
                throw ValidationException::withMessages([
                    "items.$index.quantity" =>
                        'No hay metros suficientes para este producto.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | PRECIO
            |--------------------------------------------------------------------------
            */

            $priceType = $item['price_type'] ?? 'public';

            $unitPrice = match ($priceType) {

                'wholesale' => (float) $product->wholesale_price,

                'price_roll' => (float) $product->price_roll,

                'special' => (float) (
                    $product->special_price > 0
                        ? $product->special_price
                        : $product->public_price
                ),

                'price' => (float) $product->price,

                default => (float) $product->public_price,
            };

            $lineTotal = round(
                $quantity * $unitPrice,
                2
            );

            $subtotal += $lineTotal;

            /*
            |--------------------------------------------------------------------------
            | DESCONTAR STOCK
            |--------------------------------------------------------------------------
            */

            if ($unit === 'kilos') {

                $stock->decrement(
                    'kilos_available',
                    $quantity
                );

                $product->decrement(
                    'kilos',
                    $quantity
                );

            } elseif ($unit === 'metros') {

                $stock->decrement(
                    'metros_available',
                    $quantity
                );

                $product->decrement(
                    'metros',
                    $quantity
                );
            }

            /*
            |--------------------------------------------------------------------------
            | CREAR ITEM
            |--------------------------------------------------------------------------
            */

            $sale->items()->create([
                'store_id' => $product->id,
                'unit' => $unit,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. ACTUALIZAR TOTALES
        |--------------------------------------------------------------------------
        */

        $sale->update([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);
    });

    return redirect()
        ->route('sales.show', $sale)
        ->with('success', 'Venta actualizada correctamente.');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }
}
