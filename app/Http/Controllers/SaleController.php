<?php

namespace App\Http\Controllers;

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

        $assignedWarehouseIds = ($user->hasRole('admin') || $user->hasRole('almacen'))
            ? null
            : $user->warehouses()->pluck('warehouses.id');

        // =========================================================
        // VENTAS
        // =========================================================

        $salesQuery = Sale::query()
            ->with([
                'customer:id,name',
                'warehouse:id,name,code',
                'seller:id,name',
                'items.store:id,code_product,name_product,color',
            ])
            ->latest();

        if ($assignedWarehouseIds !== null) {
            $salesQuery->whereIn(
                'warehouse_id',
                $assignedWarehouseIds
            );
        }

        $sales = $salesQuery
            ->paginate(10)
            ->withQueryString();

        // =========================================================
        // ALMACENES
        // =========================================================

        $warehousesQuery = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($assignedWarehouseIds !== null) {
            $warehousesQuery->whereIn(
                'id',
                $assignedWarehouseIds
            );
        }

        $warehouses = $warehousesQuery->get([
            'id',
            'name',
            'code',
        ]);

        $defaultWarehouseId = $warehouses->count() === 1
            ? $warehouses->first()->id
            : null;

        // =========================================================
        // STOCK
        // =========================================================

        $warehouseStocks = WarehouseStock::query()
            ->with('warehouse:id,name,code')
            ->whereHas(
                'warehouse',
                fn ($query) =>
                    $query->where('is_active', true)
            );

        if ($assignedWarehouseIds !== null) {
            $warehouseStocks->whereIn(
                'warehouse_id',
                $assignedWarehouseIds
            );
        }

        // =========================================================
        // CLIENTE POR DEFECTO
        // =========================================================

        $defaultCustomer = Customer::query()
            ->where('dni', '00000000')
            ->where('is_active', true)
            ->first([
                'id',
                'name',
                'dni',
            ]);

        // =========================================================
        // RESPUESTA
        // =========================================================

        return Inertia::render('Sales/Index', [
            'sales' => $sales,

            'customers' => Customer::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'dni',
                ]),

            'defaultCustomer' => $defaultCustomer,

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
                    'color',
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * La interfaz ahora envía por producto:
     * - rollos
     * - metros
     * - price_type
     *
     * No se utiliza un selector de unidad.
     */
    public function store(Request $request)
    {
        $items = $request->input('items', []);

        $normalizedItems = collect($items)->map(function ($item) {
            if (array_key_exists('rollos', $item) || array_key_exists('metros', $item)) {
                return $item;
            }

            $unit = $item['unit'] ?? null;
            $quantity = (float) ($item['quantity'] ?? 0);

            $mapped = $item;

            if ($unit === 'rollos' || $unit === 'kilos') {
                $mapped['rollos'] = $quantity;
                $mapped['metros'] = 0;
            } elseif ($unit === 'metros') {
                $mapped['metros'] = $quantity;
                $mapped['rollos'] = 0;
            }

            return $mapped;
        })->all();

        $request->merge(['items' => $normalizedItems]);

        $validated = $request->validate([
            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.store_id' => [
                'required',
                'integer',
                'exists:stores,id',
            ],

            'items.*.rollos' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'items.*.metros' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.price_type' => [
                'required',
                'in:price,public,wholesale,price_roll,special',
            ],
        ]);

        // El cliente debe estar activo.
        $customer = Customer::query()
            ->where('id', $validated['customer_id'])
            ->where('is_active', true)
            ->first();

        if (! $customer) {
            throw ValidationException::withMessages([
                'customer_id' => 'El cliente seleccionado no existe o está inactivo.',
            ]);
        }

        // Cada producto debe tener al menos rollos o metros.
        foreach ($validated['items'] as $index => $item) {
            $rollos = (int) ($item['rollos'] ?? 0);
            $metros = (float) ($item['metros'] ?? 0);

            if ($rollos <= 0 && $metros <= 0) {
                throw ValidationException::withMessages([
                    "items.$index.rollos" =>
                        'Debes ingresar al menos una cantidad de rollos o metros.',
                ]);
            }
        }

        $user = $request->user();

        // Verificar acceso al almacén.
        if (! $user->hasRole('admin')) {
            $assignedWarehouseIds = $user->warehouses()
                ->pluck('warehouses.id');

            if (! $assignedWarehouseIds->contains($validated['warehouse_id'])) {
                throw ValidationException::withMessages([
                    'warehouse_id' =>
                        'Solo puedes vender con almacenes o tiendas asignadas a tu usuario.',
                ]);
            }
        }

        $sale = DB::transaction(function () use ($validated, $user) {
            $subtotal = 0;

            $sale = Sale::create([
                'code' => 'SAL-' . strtoupper((string) str()->ulid()),
                'customer_id' => $validated['customer_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'sold_by' => $user->id,
                'status' => 'completo',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $index => $item) {
                $product = Store::query()
                    ->where('is_active', true)
                    ->find($item['store_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        "items.$index.store_id" =>
                            'El producto seleccionado no existe o está inactivo.',
                    ]);
                }

                $stock = WarehouseStock::query()
                    ->where('warehouse_id', $validated['warehouse_id'])
                    ->where('store_id', $item['store_id'])
                    ->first();

                // Mantener el comportamiento actual:
                // si no existe registro de stock para el almacén,
                // se crea con el stock general del producto.
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

                $rollos = (int) ($item['rollos'] ?? 0);
                $metros = (float) ($item['metros'] ?? 0);

                // =================================================
                // VERIFICAR STOCK
                // =================================================

                if (
                    $rollos > 0 &&
                    (float) $stock->kilos_available < $rollos
                ) {
                    throw ValidationException::withMessages([
                        "items.$index.rollos" =>
                            'No hay rollos suficientes para este producto en la ubicación seleccionada.',
                    ]);
                }

                if (
                    $metros > 0 &&
                    (float) $stock->metros_available < $metros
                ) {
                    throw ValidationException::withMessages([
                        "items.$index.metros" =>
                            'No hay metros suficientes para este producto en la ubicación seleccionada.',
                    ]);
                }

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

                // =================================================
                // ROLLOS
                // =================================================

                if ($rollos > 0) {
                    $lineTotal = round(
                        $rollos * $unitPrice,
                        2
                    );

                    $subtotal += $lineTotal;

                    $stock->decrement(
                        'kilos_available',
                        $rollos
                    );

                    $product->decrement(
                        'kilos',
                        $rollos
                    );

                    $sale->items()->create([
                        'store_id' => $product->id,
                        'unit' => 'kilos',
                        'quantity' => $rollos,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ]);

                    DB::table('inventory_movements')->insert([
                        'warehouse_id' => $validated['warehouse_id'],
                        'store_id' => $product->id,
                        'unit' => 'kilos',
                        'type' => 'SALIDA',
                        'quantity' => $rollos,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'reason' => $validated['notes'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // =================================================
                // METROS
                // =================================================

                if ($metros > 0) {
                    $lineTotal = round(
                        $metros * $unitPrice,
                        2
                    );

                    $subtotal += $lineTotal;

                    // IMPORTANTE:
                    // no se convierte a entero.
                    // Ejemplo: 15.50 debe descontar 15.50.
                    $stock->decrement(
                        'metros_available',
                        $metros
                    );

                    $product->decrement(
                        'metros',
                        $metros
                    );

                    $sale->items()->create([
                        'store_id' => $product->id,
                        'unit' => 'metros',
                        'quantity' => $metros,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ]);

                    DB::table('inventory_movements')->insert([
                        'warehouse_id' => $validated['warehouse_id'],
                        'store_id' => $product->id,
                        'unit' => 'metros',
                        'type' => 'SALIDA',
                        'quantity' => $metros,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'reason' => $validated['notes'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
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
            'items.store:id,code_product,name_product,color',
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

        // Verificar acceso al almacén.
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
            'items.store:id,code_product,name_product,color,price,public_price,wholesale_price,price_roll,special_price',
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

        $warehouseStocks = WarehouseStock::query()
            ->whereHas(
                'warehouse',
                fn ($query) => $query->where('is_active', true)
            );

        if (! $user->hasRole('admin')) {
            $warehouseStocks->whereIn(
                'warehouse_id',
                $user->warehouses()->pluck('warehouses.id')
            );
        }

        $products = Store::query()
            ->where('is_active', true)
            ->orderBy('name_product')
            ->get([
                'id',
                'code_product',
                'name_product',
                'color',
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
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'dni',
                ]),

            'warehouses' => $warehouses,

            'warehouseStocks' => $warehouseStocks->get([
                'warehouse_id',
                'store_id',
                'kilos_available',
                'metros_available',
            ]),

            'products' => $products,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        $items = $request->input('items', []);

        $normalizedItems = collect($items)->map(function ($item) {
            if (array_key_exists('rollos', $item) || array_key_exists('metros', $item)) {
                return $item;
            }

            $unit = $item['unit'] ?? null;
            $quantity = (float) ($item['quantity'] ?? 0);

            $mapped = $item;

            if ($unit === 'rollos' || $unit === 'kilos') {
                $mapped['rollos'] = $quantity;
                $mapped['metros'] = 0;
            } elseif ($unit === 'metros') {
                $mapped['metros'] = $quantity;
                $mapped['rollos'] = 0;
            }

            return $mapped;
        })->all();

        $request->merge(['items' => $normalizedItems]);

        $validated = $request->validate([
            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.store_id' => [
                'required',
                'integer',
                'exists:stores,id',
            ],

            'items.*.rollos' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'items.*.metros' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.price_type' => [
                'required',
                'in:price,public,wholesale,price_roll,special',
            ],
        ]);

        // El cliente debe estar activo.
        $customer = Customer::query()
            ->where('id', $validated['customer_id'])
            ->where('is_active', true)
            ->first();

        if (! $customer) {
            throw ValidationException::withMessages([
                'customer_id' =>
                    'El cliente seleccionado no existe o está inactivo.',
            ]);
        }

        // Cada producto debe tener al menos rollos o metros.
        foreach ($validated['items'] as $index => $item) {
            $rollos = (int) ($item['rollos'] ?? 0);
            $metros = (float) ($item['metros'] ?? 0);

            if ($rollos <= 0 && $metros <= 0) {
                throw ValidationException::withMessages([
                    "items.$index.rollos" =>
                        'Debes ingresar al menos una cantidad de rollos o metros.',
                ]);
            }
        }

        $user = $request->user();

        // Verificar que el usuario pueda trabajar con el almacén seleccionado.
        if (! $user->hasRole('admin')) {
            $assignedWarehouseIds = $user->warehouses()
                ->pluck('warehouses.id');

            if (! $assignedWarehouseIds->contains($validated['warehouse_id'])) {
                throw ValidationException::withMessages([
                    'warehouse_id' =>
                        'No tienes acceso a este almacén.',
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
                    if (
                        in_array(
                            $oldItem->unit,
                            ['kilos', 'rollos'],
                            true
                        )
                    ) {
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

                // Devolver también al stock general del producto.
                $product = Store::find($oldItem->store_id);

                if ($product) {
                    if (
                        in_array(
                            $oldItem->unit,
                            ['kilos', 'rollos'],
                            true
                        )
                    ) {
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
            | 2. ELIMINAR MOVIMIENTOS DE INVENTARIO DE LA VENTA ANTERIOR
            |--------------------------------------------------------------------------
            |
            | Esto evita duplicar movimientos cuando una venta se edita.
            |
            */

            DB::table('inventory_movements')
                ->where('reference_type', 'sale')
                ->where('reference_id', $sale->id)
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | 3. ELIMINAR LOS ITEMS ANTERIORES
            |--------------------------------------------------------------------------
            */

            $sale->items()->delete();

            /*
            |--------------------------------------------------------------------------
            | 4. ACTUALIZAR DATOS DE LA VENTA
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
            | 5. CREAR LOS NUEVOS ITEMS Y DESCONTAR STOCK
            |--------------------------------------------------------------------------
            */

            $subtotal = 0;

            foreach ($validated['items'] as $index => $item) {

                $product = Store::query()
                    ->where('is_active', true)
                    ->find($item['store_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        "items.$index.store_id" =>
                            'El producto seleccionado no existe o está inactivo.',
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

                $rollos = (int) ($item['rollos'] ?? 0);
                $metros = (float) ($item['metros'] ?? 0);

                /*
                |--------------------------------------------------------------------------
                | VERIFICAR STOCK
                |--------------------------------------------------------------------------
                */

                if (
                    $rollos > 0 &&
                    (float) $stock->kilos_available < $rollos
                ) {
                    throw ValidationException::withMessages([
                        "items.$index.rollos" =>
                            'No hay rollos suficientes para este producto.',
                    ]);
                }

                if (
                    $metros > 0 &&
                    (float) $stock->metros_available < $metros
                ) {
                    throw ValidationException::withMessages([
                        "items.$index.metros" =>
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

                    'wholesale' =>
                        (float) $product->wholesale_price,

                    'price_roll' =>
                        (float) $product->price_roll,

                    'special' =>
                        (float) (
                            $product->special_price > 0
                                ? $product->special_price
                                : $product->public_price
                        ),

                    'price' =>
                        (float) $product->price,

                    default =>
                        (float) $product->public_price,
                };

                /*
                |--------------------------------------------------------------------------
                | ROLLOS
                |--------------------------------------------------------------------------
                */

                if ($rollos > 0) {

                    $lineTotal = round(
                        $rollos * $unitPrice,
                        2
                    );

                    $subtotal += $lineTotal;

                    $stock->decrement(
                        'kilos_available',
                        $rollos
                    );

                    $product->decrement(
                        'kilos',
                        $rollos
                    );

                    $sale->items()->create([
                        'store_id' => $product->id,
                        'unit' => 'kilos',
                        'quantity' => $rollos,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ]);

                    DB::table('inventory_movements')->insert([
                        'warehouse_id' => $validated['warehouse_id'],
                        'store_id' => $product->id,
                        'unit' => 'kilos',
                        'type' => 'SALIDA',
                        'quantity' => $rollos,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'reason' => $validated['notes'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | METROS
                |--------------------------------------------------------------------------
                */

                if ($metros > 0) {

                    $lineTotal = round(
                        $metros * $unitPrice,
                        2
                    );

                    $subtotal += $lineTotal;

                    // Se conserva el decimal.
                    // Ejemplo: 15.50 -> 15.50
                    $stock->decrement(
                        'metros_available',
                        $metros
                    );

                    $product->decrement(
                        'metros',
                        $metros
                    );

                    $sale->items()->create([
                        'store_id' => $product->id,
                        'unit' => 'metros',
                        'quantity' => $metros,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ]);

                    DB::table('inventory_movements')->insert([
                        'warehouse_id' => $validated['warehouse_id'],
                        'store_id' => $product->id,
                        'unit' => 'metros',
                        'type' => 'SALIDA',
                        'quantity' => $metros,
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'reason' => $validated['notes'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 6. ACTUALIZAR TOTALES
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
    public function destroy(Request $request, Sale $sale)
    {
        $user = $request->user();

        if (! $user->hasRole('admin')) {
            $assignedWarehouseIds = $user->warehouses()
                ->pluck('warehouses.id');

            if (! $assignedWarehouseIds->contains($sale->warehouse_id)) {
                abort(403);
            }
        }

        DB::transaction(function () use ($sale) {
            $sale->load('items');

            foreach ($sale->items as $item) {
                $stock = WarehouseStock::query()
                    ->where('warehouse_id', $sale->warehouse_id)
                    ->where('store_id', $item->store_id)
                    ->first();

                if ($stock) {
                    if (
                        in_array(
                            $item->unit,
                            ['kilos', 'rollos'],
                            true
                        )
                    ) {
                        $stock->increment(
                            'kilos_available',
                            $item->quantity
                        );
                    } elseif ($item->unit === 'metros') {
                        $stock->increment(
                            'metros_available',
                            $item->quantity
                        );
                    }
                }

                $product = Store::find($item->store_id);

                if ($product) {
                    if (
                        in_array(
                            $item->unit,
                            ['kilos', 'rollos'],
                            true
                        )
                    ) {
                        $product->increment(
                            'kilos',
                            $item->quantity
                        );
                    } elseif ($item->unit === 'metros') {
                        $product->increment(
                            'metros',
                            $item->quantity
                        );
                    }
                }
            }

            DB::table('inventory_movements')
                ->where('reference_type', 'sale')
                ->where('reference_id', $sale->id)
                ->delete();

            $sale->items()->delete();
            $sale->delete();
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Venta eliminada correctamente.');
    }
}
