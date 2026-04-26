<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
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

        $startDate = $request->input('start_date')
            ? Carbon::parse($request->string('start_date'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->string('end_date'))->endOfDay()
            : now()->endOfMonth()->endOfDay();

        $warehouseFilter = $request->integer('warehouse_id');

        $warehousesQuery = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($assignedWarehouseIds !== null) {
            $warehousesQuery->whereIn('id', $assignedWarehouseIds);
        }

        $warehouses = $warehousesQuery->get(['id', 'name', 'code']);

        $salesByWarehouseQuery = DB::table('sales')
            ->join('warehouses', 'warehouses.id', '=', 'sales.warehouse_id')
            ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->when(
                $assignedWarehouseIds !== null,
                fn ($query) => $query->whereIn('sales.warehouse_id', $assignedWarehouseIds)
            )
            ->when(
                $warehouseFilter > 0,
                fn ($query) => $query->where('sales.warehouse_id', $warehouseFilter)
            )
            ->groupBy('sales.warehouse_id', 'warehouses.name', 'warehouses.code')
            ->orderBy('warehouses.name');

        $salesByWarehouse = $salesByWarehouseQuery->get([
            'sales.warehouse_id',
            'warehouses.name as warehouse_name',
            'warehouses.code as warehouse_code',
            DB::raw('COUNT(DISTINCT sales.id) as sales_count'),
            DB::raw('COALESCE(SUM(sales.total), 0) as total_sales'),
            DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_units'),
        ]);

        $inventoryByWarehouse = DB::table('warehouse_stocks')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_stocks.warehouse_id')
            ->join('stores', 'stores.id', '=', 'warehouse_stocks.store_id')
            ->when(
                $assignedWarehouseIds !== null,
                fn ($query) => $query->whereIn('warehouse_stocks.warehouse_id', $assignedWarehouseIds)
            )
            ->when(
                $warehouseFilter > 0,
                fn ($query) => $query->where('warehouse_stocks.warehouse_id', $warehouseFilter)
            )
            ->orderBy('warehouses.name')
            ->orderBy('stores.name_product')
            ->get([
                'warehouse_stocks.id',
                'warehouse_stocks.warehouse_id',
                'warehouses.name as warehouse_name',
                'warehouses.code as warehouse_code',
                'stores.id as product_id',
                'stores.code_product',
                'stores.name_product',
                'warehouse_stocks.kilos_available',
                'warehouse_stocks.metros_available',
            ]);

        return Inertia::render('Reports/Index', [
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'warehouse_id' => $warehouseFilter > 0 ? $warehouseFilter : null,
            ],
            'warehouses' => $warehouses,
            'sales_by_warehouse' => $salesByWarehouse,
            'inventory_by_warehouse' => $inventoryByWarehouse,
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
    public function show(string $id)
    {
        //
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
