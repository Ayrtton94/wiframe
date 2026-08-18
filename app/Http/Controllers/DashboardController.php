<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
{
    $user = $request->user();

    /*
    |--------------------------------------------------------------------------
    | ALMACENES ASIGNADOS
    |--------------------------------------------------------------------------
    */

    $assignedWarehouseIds = $user->hasRole('admin')
        ? null
        : $user->warehouses()->pluck('warehouses.id');

    /*
    |--------------------------------------------------------------------------
    | VENTAS
    |--------------------------------------------------------------------------
    */

    $salesBaseQuery = Sale::query();

    if ($assignedWarehouseIds !== null) {
        $salesBaseQuery->whereIn(
            'warehouse_id',
            $assignedWarehouseIds
        );
    }

    $salesCount = (clone $salesBaseQuery)->count();

    $totalRevenue = (float) (
        (clone $salesBaseQuery)->sum('total')
    );

    $averageTicket = $salesCount > 0
        ? round(
            $totalRevenue / $salesCount,
            2
        )
        : 0.0;

    /*
    |--------------------------------------------------------------------------
    | PRODUCTOS MÁS VENDIDOS
    |--------------------------------------------------------------------------
    */

    $topProducts = SaleItem::query()
        ->join(
            'sales',
            'sales.id',
            '=',
            'sale_items.sale_id'
        )
        ->join(
            'stores',
            'stores.id',
            '=',
            'sale_items.store_id'
        )
        ->when(
            $assignedWarehouseIds !== null,
            fn ($query) =>
                $query->whereIn(
                    'sales.warehouse_id',
                    $assignedWarehouseIds
                )
        )
        ->groupBy(
            'sale_items.store_id',
            'stores.code_product',
            'stores.name_product'
        )
        ->orderByDesc(
            DB::raw(
                'SUM(sale_items.quantity)'
            )
        )
        ->limit(5)
        ->get([
            'sale_items.store_id',
            'stores.code_product',
            'stores.name_product',
            DB::raw(
                'SUM(sale_items.quantity) as total_quantity'
            ),
            DB::raw(
                'SUM(sale_items.line_total) as total_amount'
            ),
        ]);

    /*
    |--------------------------------------------------------------------------
    | MEJOR VENDEDOR
    |--------------------------------------------------------------------------
    */

    $bestSeller = User::query()
        ->join(
            'sales',
            'sales.sold_by',
            '=',
            'users.id'
        )
        ->when(
            $assignedWarehouseIds !== null,
            fn ($query) =>
                $query->whereIn(
                    'sales.warehouse_id',
                    $assignedWarehouseIds
                )
        )
        ->groupBy(
            'users.id',
            'users.name'
        )
        ->orderByDesc(
            DB::raw(
                'SUM(sales.total)'
            )
        )
        ->first([
            'users.id',
            'users.name',
            DB::raw(
                'COUNT(sales.id) as sales_count'
            ),
            DB::raw(
                'SUM(sales.total) as total_sold'
            ),
        ]);

    /*
    |--------------------------------------------------------------------------
    | TRANSFERENCIAS
    |--------------------------------------------------------------------------
    */

    $transfersCount = DB::table('transfers')
        ->when(
            $assignedWarehouseIds !== null,
            function ($query) use ($assignedWarehouseIds) {
                $query->where(function ($query) use ($assignedWarehouseIds) {
                    $query->whereIn(
                        'from_warehouse_id',
                        $assignedWarehouseIds
                    )
                    ->orWhereIn(
                        'to_warehouse_id',
                        $assignedWarehouseIds
                    );
                });
            }
        )
        ->count();

    /*
    |--------------------------------------------------------------------------
    | CLIENTES
    |--------------------------------------------------------------------------
    */

    $customersCount = DB::table('customers')
        ->count();

    /*
    |--------------------------------------------------------------------------
    | PROVEEDORES
    |--------------------------------------------------------------------------
    */

    $suppliersCount = DB::table('suppliers')
        ->count();

    /*
    |--------------------------------------------------------------------------
    | INVENTARIO
    |--------------------------------------------------------------------------
    */

    $warehouseStockQuery = DB::table(
        'warehouse_stocks'
    );

    if ($assignedWarehouseIds !== null) {
        $warehouseStockQuery->whereIn(
            'warehouse_id',
            $assignedWarehouseIds
        );
    }

    $totalRolls = (float) (
        (clone $warehouseStockQuery)
            ->sum('kilos_available')
    );

    $totalMeters = (float) (
        (clone $warehouseStockQuery)
            ->sum('metros_available')
    );

    /*
    |--------------------------------------------------------------------------
    | TENDENCIA DE VENTAS DEL MES
    |--------------------------------------------------------------------------
    */

    $monthStart = Carbon::now()->startOfMonth();
    $monthEnd = Carbon::now()->endOfMonth();

    $rawDailySales = Sale::query()
        ->selectRaw(
            'DATE(created_at) as sale_date, SUM(total) as total_amount'
        )
        ->whereBetween(
            'created_at',
            [$monthStart, $monthEnd]
        )
        ->when(
            $assignedWarehouseIds !== null,
            fn ($query) =>
                $query->whereIn(
                    'warehouse_id',
                    $assignedWarehouseIds
                )
        )
        ->groupByRaw(
            'DATE(created_at)'
        )
        ->orderByRaw(
            'DATE(created_at)'
        )
        ->get()
        ->keyBy('sale_date');

    $monthSalesTrend = [];

    $cursorDate = $monthStart->copy();

    while ($cursorDate->lte($monthEnd)) {
        $key = $cursorDate->toDateString();

        $dayTotal = (float) (
            $rawDailySales[$key]->total_amount ?? 0
        );

        $monthSalesTrend[] = [
            'date' => $key,
            'day' => (int) $cursorDate->format('d'),
            'total' => round(
                $dayTotal,
                2
            ),
        ];

        $cursorDate->addDay();
    }

    /*
|--------------------------------------------------------------------------
| HISTÓRICO DE TRANSFERENCIAS
|--------------------------------------------------------------------------
*/

$transfers = DB::table('transfers as t')
    ->join(
        'warehouses as wo',
        'wo.id',
        '=',
        't.from_warehouse_id'
    )
    ->join(
        'warehouses as wd',
        'wd.id',
        '=',
        't.to_warehouse_id'
    )
    ->leftJoin(
        DB::raw('(
            SELECT
                transfer_id,
                COUNT(*) as products_count
            FROM transfer_items
            GROUP BY transfer_id
        ) as ti'),
        'ti.transfer_id',
        '=',
        't.id'
    )
    ->when(
        $assignedWarehouseIds !== null,
        function ($query) use ($assignedWarehouseIds) {
            $query->where(function ($query) use ($assignedWarehouseIds) {
                $query->whereIn(
                    't.from_warehouse_id',
                    $assignedWarehouseIds
                )->orWhereIn(
                    't.to_warehouse_id',
                    $assignedWarehouseIds
                );
            });
        }
    )
    ->orderByDesc('t.created_at')
    ->limit(5)
    ->get([
        't.id',
        't.code',
        DB::raw(
            'DATE_FORMAT(t.created_at, "%d/%m/%Y") as date'
        ),
        'wo.name as origin',
        'wd.name as destination',
        DB::raw(
            'COALESCE(ti.products_count, 0) as products_count'
        ),
        't.status',
    ]);


/*
|--------------------------------------------------------------------------
| ALERTAS
|--------------------------------------------------------------------------
*/

$alerts = collect();

/*
|--------------------------------------------------------------------------
| STOCK BAJO
|--------------------------------------------------------------------------
*/

$lowStockQuery = DB::table('warehouse_stocks as ws')
    ->join(
        'stores as s',
        's.id',
        '=',
        'ws.store_id'
    )
    ->join(
        'warehouses as w',
        'w.id',
        '=',
        'ws.warehouse_id'
    )
    ->where('s.is_active', true)
    ->where('w.is_active', true)
    ->whereColumn(
        'ws.metros_available',
        '<=',
        's.minimum_stock'
    )
    ->when(
        $assignedWarehouseIds !== null,
        fn ($query) =>
            $query->whereIn(
                'ws.warehouse_id',
                $assignedWarehouseIds
            )
    )
    ->count();

if ($lowStockQuery > 0) {
    $alerts->push([
        'type' => 'critical',
        'title' => 'productos con stock bajo',
        'description' => 'Hay productos que están por debajo del stock mínimo.',
        'count' => $lowStockQuery,
        'action_url' => '/reports/inventario',
    ]);
}

/*
|--------------------------------------------------------------------------
| TRANSFERENCIAS PENDIENTES
|--------------------------------------------------------------------------
*/

$pendingTransfersQuery = DB::table('transfers')
    ->whereIn(
        'status',
        ['requested', 'shipped']
    )
    ->when(
        $assignedWarehouseIds !== null,
        function ($query) use ($assignedWarehouseIds) {
            $query->where(function ($query) use ($assignedWarehouseIds) {
                $query->whereIn(
                    'from_warehouse_id',
                    $assignedWarehouseIds
                )->orWhereIn(
                    'to_warehouse_id',
                    $assignedWarehouseIds
                );
            });
        }
    )
    ->count();

if ($pendingTransfersQuery > 0) {
    $alerts->push([
        'type' => 'warning',
        'title' => 'transferencias pendientes',
        'description' => 'Hay transferencias que requieren seguimiento.',
        'count' => $pendingTransfersQuery,
        'action_url' => '/reports/transferencias',
    ]);
}

$alerts = $alerts->values();


    /*
    |--------------------------------------------------------------------------
    | RESPUESTA
    |--------------------------------------------------------------------------
    */
return Inertia::render(
    'Dashboard',
    [
        'stats' => [
            'sales_count' => $salesCount,
            'total_revenue' => round($totalRevenue, 2),
            'average_ticket' => $averageTicket,

            'products_count' => Store::query()
                ->where('is_active', true)
                ->count(),

            'transfers_count' => $transfersCount,
            'customers_count' => $customersCount,
            'suppliers_count' => $suppliersCount,

            'total_rolls' => $totalRolls,
            'total_meters' => $totalMeters,
        ],

        'top_products' => $topProducts,

        'best_seller' => $bestSeller,

        'month_sales_trend' => $monthSalesTrend,

        // ✅ Histórico de transferencias
        'transfers' => $transfers,

        // ✅ Alertas
        'alerts' => $alerts,
    ]
);
}
}
