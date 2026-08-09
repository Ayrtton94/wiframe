<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

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
        $sellerFilter = $request->integer('seller_id');
        $search = trim((string) $request->input('search', ''));

        $warehousesQuery = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($assignedWarehouseIds !== null) {
            $warehousesQuery->whereIn('id', $assignedWarehouseIds);
        }

        $warehouses = $warehousesQuery->get(['id', 'name', 'code']);

        $sellers = DB::table('users')
            ->join('sales', 'users.id', '=', 'sales.sold_by')
            ->when(
                $assignedWarehouseIds !== null,
                fn ($query) => $query->whereIn('sales.warehouse_id', $assignedWarehouseIds)
            )
            ->when(
                $warehouseFilter > 0,
                fn ($query) => $query->where('sales.warehouse_id', $warehouseFilter)
            )
            ->distinct()
            ->orderBy('users.name')
            ->get(['users.id', 'users.name']);

        $salesByWarehouseQuery = DB::table('sales')
            ->join('warehouses', 'warehouses.id', '=', 'sales.warehouse_id')
            ->join('users', 'users.id', '=', 'sales.sold_by')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('stores', 'stores.id', '=', 'sale_items.store_id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->when(
                $assignedWarehouseIds !== null,
                fn ($query) => $query->whereIn('sales.warehouse_id', $assignedWarehouseIds)
            )
            ->when(
                $warehouseFilter > 0,
                fn ($query) => $query->where('sales.warehouse_id', $warehouseFilter)
            )
            ->when(
                $sellerFilter > 0,
                fn ($query) => $query->where('sales.sold_by', $sellerFilter)
            )
            ->when(
                $search !== '',
                fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('sales.code', 'like', "%{$search}%")
                        ->orWhere('customers.name', 'like', "%{$search}%")
                        ->orWhere('stores.code_product', 'like', "%{$search}%")
                        ->orWhere('stores.name_product', 'like', "%{$search}%");
                })
            )
            ->groupBy(
                'sales.warehouse_id',
                'warehouses.name',
                'warehouses.code',
                DB::raw('DATE(sales.created_at)'),
                'users.name'
            )
            ->orderBy('warehouses.name')
            ->orderBy(DB::raw('DATE(sales.created_at)'))
            ->orderBy('users.name');

        $salesByWarehouse = $salesByWarehouseQuery->get([
            'sales.warehouse_id',
            'warehouses.name as warehouse_name',
            'warehouses.code as warehouse_code',
            'users.name as seller_name',
            DB::raw('DATE(sales.created_at) as sale_date'),
            DB::raw('COUNT(DISTINCT sales.id) as sales_count'),
            DB::raw('COALESCE(SUM(sales.total), 0) as total_sales'),
            DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_units'),
        ]);

        $inventoryByWarehouse = DB::table('warehouse_stocks')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_stocks.warehouse_id')
            ->join('stores', 'stores.id', '=', 'warehouse_stocks.store_id')
            ->where('stores.is_active', true)
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
                'seller_id' => $sellerFilter > 0 ? $sellerFilter : null,
                'search' => $search,
            ],
            'warehouses' => $warehouses,
            'sellers' => $sellers,
            'sales_by_warehouse' => $salesByWarehouse,
            'inventory_by_warehouse' => $inventoryByWarehouse,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function export(Request $request)
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
        $sellerFilter = $request->integer('seller_id');
        $search = trim((string) $request->input('search', ''));

        $salesByWarehouse = DB::table('sales')
            ->join('warehouses', 'warehouses.id', '=', 'sales.warehouse_id')
            ->join('users', 'users.id', '=', 'sales.sold_by')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('stores', 'stores.id', '=', 'sale_items.store_id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->when(
                $assignedWarehouseIds !== null,
                fn ($query) => $query->whereIn('sales.warehouse_id', $assignedWarehouseIds)
            )
            ->when(
                $warehouseFilter > 0,
                fn ($query) => $query->where('sales.warehouse_id', $warehouseFilter)
            )
            ->when(
                $sellerFilter > 0,
                fn ($query) => $query->where('sales.sold_by', $sellerFilter)
            )
            ->when(
                $search !== '',
                fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('sales.code', 'like', "%{$search}%")
                        ->orWhere('customers.name', 'like', "%{$search}%")
                        ->orWhere('stores.code_product', 'like', "%{$search}%")
                        ->orWhere('stores.name_product', 'like', "%{$search}%");
                })
            )
            ->groupBy('sales.warehouse_id', 'warehouses.name', 'warehouses.code', DB::raw('DATE(sales.created_at)'), 'users.name')
            ->orderBy('warehouses.name')
            ->orderBy(DB::raw('DATE(sales.created_at)'))
            ->orderBy('users.name')
            ->get([
                'sales.warehouse_id',
                'warehouses.name as warehouse_name',
                'warehouses.code as warehouse_code',
                DB::raw('DATE(sales.created_at) as sale_date'),
                DB::raw('COUNT(DISTINCT sales.id) as sales_count'),
                DB::raw('COALESCE(SUM(sales.total), 0) as total_sales'),
                DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_units'),
            ]);

        $inventoryByWarehouse = DB::table('warehouse_stocks')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_stocks.warehouse_id')
            ->join('stores', 'stores.id', '=', 'warehouse_stocks.store_id')
            ->where('stores.is_active', true)
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

        return Excel::download(
            new ReportsExport($salesByWarehouse, $inventoryByWarehouse, [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'warehouse_id' => $warehouseFilter > 0 ? $warehouseFilter : null,
                'seller_id' => $sellerFilter > 0 ? $sellerFilter : null,
                'search' => $search,
            ]),
            'reporte-'.now()->format('Ymd_His').'.xlsx'
        );
    }

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
