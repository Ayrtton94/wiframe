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
            ->with(['customer:id,name', 'warehouse:id,name,code', 'seller:id,name'])
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

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name', 'dni']),
            'warehouses' => $warehousesQuery->get(['id', 'name', 'code']),
            'products' => Store::query()
                ->orderBy('name_product')
                ->get(['id', 'code_product', 'name_product', 'public_price']),
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
                    'status' => 'completed',
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($validated['items'] as $index => $item) {
                    $product = Store::query()->findOrFail($item['store_id']);
                    $stock = WarehouseStock::query()
                        ->where('warehouse_id', $validated['warehouse_id'])
                        ->where('store_id', $item['store_id'])
                        ->lockForUpdate()
                        ->first();

                    if (! $stock) {
                        throw ValidationException::withMessages([
                            "items.$index.store_id" => 'El producto no tiene stock configurado en la ubicación seleccionada.',
                        ]);
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

                    $unitPrice = (float) $product->public_price;
                    $lineTotal = round($quantity * $unitPrice, 2);
                    $subtotal += $lineTotal;

                    if ($unit === 'kilos') {
                        $stock->decrement('kilos_available', $quantity);
                    } else {
                        $stock->decrement('metros_available', $quantity);
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
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }
}
