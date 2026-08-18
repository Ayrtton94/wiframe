<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
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

public function salidas(Request $request)
{
    $user = $request->user();

    /*
    |--------------------------------------------------------------------------
    | ALMACENES ASIGNADOS AL USUARIO
    |--------------------------------------------------------------------------
    */

    $assignedWarehouseIds = $user->hasRole('admin')
        ? null
        : $user->warehouses()->pluck('warehouses.id');

    /*
    |--------------------------------------------------------------------------
    | FILTROS
    |--------------------------------------------------------------------------
    */

    $from = $request->input('from')
        ? Carbon::parse($request->input('from'))->startOfDay()
        : now()->startOfMonth()->startOfDay();

    $to = $request->input('to')
        ? Carbon::parse($request->input('to'))->endOfDay()
        : now()->endOfDay();

    $warehouseFilter = $request->integer('warehouse_id');
    $responsibleFilter = $request->integer('responsible_id');
    $customerFilter = $request->integer('customer_id');

    $search = trim((string) $request->input('search', ''));

    $perPage = max(
        10,
        min(
            100,
            $request->integer('per_page', 25)
        )
    );

    /*
    |--------------------------------------------------------------------------
    | ALMACENES
    |--------------------------------------------------------------------------
    */

    $warehousesQuery = Warehouse::query()
        ->where('is_active', true)
        ->orderBy('name');

    if ($assignedWarehouseIds !== null) {
        $warehousesQuery->whereIn('id', $assignedWarehouseIds);
    }

    $warehouses = $warehousesQuery->get([
        'id',
        'name',
        'code',
    ]);

    /*
    |--------------------------------------------------------------------------
    | RESPONSABLES
    |--------------------------------------------------------------------------
    */

    $responsibles = DB::table('users')
        ->join('sales', 'users.id', '=', 'sales.sold_by')
        ->when(
            $assignedWarehouseIds !== null,
            fn ($query) =>
                $query->whereIn(
                    'sales.warehouse_id',
                    $assignedWarehouseIds
                )
        )
        ->when(
            $warehouseFilter > 0,
            fn ($query) =>
                $query->where(
                    'sales.warehouse_id',
                    $warehouseFilter
                )
        )
        ->distinct()
        ->orderBy('users.name')
        ->get([
            'users.id',
            'users.name',
        ]);

    /*
    |--------------------------------------------------------------------------
    | CLIENTES
    |--------------------------------------------------------------------------
    */

    $customers = DB::table('customers')
        ->join('sales', 'customers.id', '=', 'sales.customer_id')
        ->when(
            $assignedWarehouseIds !== null,
            fn ($query) =>
                $query->whereIn(
                    'sales.warehouse_id',
                    $assignedWarehouseIds
                )
        )
        ->when(
            $warehouseFilter > 0,
            fn ($query) =>
                $query->where(
                    'sales.warehouse_id',
                    $warehouseFilter
                )
        )
        ->distinct()
        ->orderBy('customers.name')
        ->get([
            'customers.id',
            'customers.name',
        ]);

    /*
    |--------------------------------------------------------------------------
    | DETALLE DE SALIDAS
    |--------------------------------------------------------------------------
    */

    $query = DB::table('sales as s')

        ->join(
            'sale_items as si',
            'si.sale_id',
            '=',
            's.id'
        )

        ->join(
            'warehouses as w',
            'w.id',
            '=',
            's.warehouse_id'
        )

        ->join(
            'users as u',
            'u.id',
            '=',
            's.sold_by'
        )

        /*
        |--------------------------------------------------------------------------
        | LEFT JOIN
        |--------------------------------------------------------------------------
        | Usamos LEFT JOIN para que una salida no desaparezca
        | si por algún motivo falta cliente o producto.
        */

        ->leftJoin(
            'customers as c',
            'c.id',
            '=',
            's.customer_id'
        )

        ->leftJoin(
            'stores as p',
            'p.id',
            '=',
            'si.store_id'
        )

        ->leftJoin('inventory_movements as im', function ($join) {

            $join->on(
                'im.reference_id',
                '=',
                's.id'
            )

            ->where(
                'im.reference_type',
                '=',
                'sale'
            )

            ->on(
                'im.store_id',
                '=',
                'si.store_id'
            )

            ->where(
                'im.type',
                '=',
                'SALIDA'
            );
        })

        /*
        |--------------------------------------------------------------------------
        | CAMPOS
        |--------------------------------------------------------------------------
        */

        ->select([
            's.id',

            's.code as salida_code',

            's.created_at as fecha_hora',

            'w.id as warehouse_id',
            'w.name as almacen',

            'c.id as customer_id',
            'c.name as cliente',

            'u.id as responsible_id',
            'u.name as responsable',

            'p.id as product_id',
            'p.code_product as codigo_producto',
            'p.name_product as producto',

            'si.quantity as cantidad',
            'si.unit as unidad',
            'si.unit_price as precio',
            'si.line_total as total',

            'im.reason as motivo',
        ]);

         /*
    |--------------------------------------------------------------------------
    | VENTAS REGISTRADAS COMO SALIDAS
    |--------------------------------------------------------------------------
    | El módulo de ventas del sistema guarda las salidas con el estado
    | vigente que corresponda en cada instalación. No filtramos por status
    | para evitar ocultar ventas registradas con variantes como
    | completed/completo.
    */


    /*
    |--------------------------------------------------------------------------
    | FECHAS
    |--------------------------------------------------------------------------
    */

    $query->whereBetween(
        's.created_at',
        [$from, $to]
    );

    /*
    |--------------------------------------------------------------------------
    | RESTRICCIÓN POR ALMACEN
    |--------------------------------------------------------------------------
    */

    if ($assignedWarehouseIds !== null) {

        $query->whereIn(
            's.warehouse_id',
            $assignedWarehouseIds
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRO ALMACEN
    |--------------------------------------------------------------------------
    */

    if ($warehouseFilter > 0) {

        $query->where(
            's.warehouse_id',
            $warehouseFilter
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRO RESPONSABLE
    |--------------------------------------------------------------------------
    */

    if ($responsibleFilter > 0) {

        $query->where(
            's.sold_by',
            $responsibleFilter
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRO CLIENTE
    |--------------------------------------------------------------------------
    */

    if ($customerFilter > 0) {

        $query->where(
            's.customer_id',
            $customerFilter
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BUSCADOR
    |--------------------------------------------------------------------------
    */

    if ($search !== '') {

        $query->where(function ($query) use ($search) {

            $query
                ->where(
                    's.code',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'c.name',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'p.code_product',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'p.name_product',
                    'like',
                    "%{$search}%"
                );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ORDEN
    |--------------------------------------------------------------------------
    */

    $query
        ->orderByDesc('s.created_at')
        ->orderByDesc('s.id')
        ->orderBy('si.id');

    /*
    |--------------------------------------------------------------------------
    | PAGINACIÓN
    |--------------------------------------------------------------------------
    */

    $rows = $query
        ->paginate($perPage)
        ->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | INERTIA
    |--------------------------------------------------------------------------
    */

    return Inertia::render('Reports/Salidas', [

        'filters' => [

            'from' => $from->toDateString(),

            'to' => $to->toDateString(),

            'warehouse_id' =>
                $warehouseFilter > 0
                    ? $warehouseFilter
                    : null,

            'responsible_id' =>
                $responsibleFilter > 0
                    ? $responsibleFilter
                    : null,

            'customer_id' =>
                $customerFilter > 0
                    ? $customerFilter
                    : null,

            'search' => $search,

            'per_page' => $perPage,
        ],

        'warehouses' => $warehouses,

        'responsibles' => $responsibles,

        'customers' => $customers,

        'rows' => $rows,
    ]);
}

 private function assignedWarehouseIds(Request $request)
    {
        $user = $request->user();

        return $user->hasRole('admin')
            ? null
            : $user->warehouses()->pluck('warehouses.id');
    }

    private function reportPerPage(Request $request): int
    {
        return max(10, min(100, $request->integer('per_page', 25)));
    }

    private function reportWarehouses($assignedWarehouseIds)
    {
        return Warehouse::query()
            ->where('is_active', true)
            ->when($assignedWarehouseIds !== null, fn ($query) => $query->whereIn('id', $assignedWarehouseIds))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    private function reportProducts()
    {
        return DB::table('stores')
            ->where('is_active', true)
            ->orderBy('name_product')
            ->get(['id', 'code_product', 'name_product']);
    }


    /**
     * Reporte 2: Movimiento / saldo por producto.
     *
     * Regla:
     * - INGRESO = positivo
     * - TRANSFERENCIA_ENTRADA = positivo
     * - TRANSFERENCIA_SALIDA = negativo
     * - SALIDA = negativo
     *
     * IMPORTANTE:
     * inventory_movements debe ser alimentada por los módulos
     * de ingreso, salida y transferencia.
     */
    public function movimientoProductos(Request $request)
{
    $data = $request->validate([
        'from' => ['nullable', 'date'],
        'to' => ['nullable', 'date'],
        'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
        'product_id' => ['nullable', 'integer', 'exists:stores,id'],
        'unit' => ['nullable', Rule::in(['ROLLOS', 'METROS'])],
    ]);

    $assignedWarehouseIds = $this->assignedWarehouseIds($request);
    $perPage = $this->reportPerPage($request);
    $warehouses = $this->reportWarehouses($assignedWarehouseIds);
    $productsCatalog = $this->reportProducts();

    $from = !empty($data['from'])
        ? Carbon::parse($data['from'])->startOfDay()
        : null;

    $to = !empty($data['to'])
        ? Carbon::parse($data['to'])->endOfDay()
        : null;

    $unitFilter = $data['unit'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | STOCK ACTUAL
    |--------------------------------------------------------------------------
    */

    $stockRows = DB::table('warehouse_stocks as ws')
        ->join('stores as p', 'p.id', '=', 'ws.store_id')
        ->join('warehouses as w', 'w.id', '=', 'ws.warehouse_id')
        ->where('p.is_active', true)
        ->where('w.is_active', true)
        ->when(
            $assignedWarehouseIds !== null,
            fn ($query) =>
                $query->whereIn(
                    'ws.warehouse_id',
                    $assignedWarehouseIds
                )
        )
        ->when(
            $data['warehouse_id'] ?? null,
            fn ($query, $id) =>
                $query->where(
                    'ws.warehouse_id',
                    $id
                )
        )
        ->when(
            $data['product_id'] ?? null,
            fn ($query, $id) =>
                $query->where(
                    'ws.store_id',
                    $id
                )
        )
        ->select([
            'p.id as product_id',
            'p.code_product as codigo_producto',
            'p.name_product as producto',
            'w.id as warehouse_id',
            'w.name as almacen',
            'ws.kilos_available',
            'ws.metros_available',
            'ws.created_at as stock_created_at',
        ])
        ->get()
        ->flatMap(function ($row) use ($unitFilter) {
            $rows = collect();

            if (
                $unitFilter === null ||
                $unitFilter === 'ROLLOS'
            ) {
                $rows->push((object) [
                    'product_id' => $row->product_id,
                    'codigo_producto' => $row->codigo_producto,
                    'producto' => $row->producto,
                    'warehouse_id' => $row->warehouse_id,
                    'almacen' => $row->almacen,
                    'unidad' => 'ROLLOS',
                ]);
            }

            if (
                $unitFilter === null ||
                $unitFilter === 'METROS'
            ) {
                $rows->push((object) [
                    'product_id' => $row->product_id,
                    'codigo_producto' => $row->codigo_producto,
                    'producto' => $row->producto,
                    'warehouse_id' => $row->warehouse_id,
                    'almacen' => $row->almacen,
                    'unidad' => 'METROS',
                ]);
            }

            return $rows;
        });

    /*
    |--------------------------------------------------------------------------
    | PRODUCTOS QUE SOLO APARECEN EN MOVIMIENTOS
    |--------------------------------------------------------------------------
    */

    $movementRows = DB::table('inventory_movements as im')
        ->join('stores as p', 'p.id', '=', 'im.store_id')
        ->join('warehouses as w', 'w.id', '=', 'im.warehouse_id')
        ->where('p.is_active', true)
        ->where('w.is_active', true)
        ->when(
            $assignedWarehouseIds !== null,
            fn ($query) =>
                $query->whereIn(
                    'im.warehouse_id',
                    $assignedWarehouseIds
                )
        )
        ->when(
            $data['warehouse_id'] ?? null,
            fn ($query, $id) =>
                $query->where(
                    'im.warehouse_id',
                    $id
                )
        )
        ->when(
            $data['product_id'] ?? null,
            fn ($query, $id) =>
                $query->where(
                    'im.store_id',
                    $id
                )
        )
        ->when(
            $data['unit'] ?? null,
            fn ($query, $unit) =>
                $query->whereIn(
                    'im.unit',
                    $unit === 'ROLLOS'
                        ? ['ROLLOS', 'rollos', 'kilos']
                        : ['METROS', 'metros']
                )
        )
        ->when(
            $from,
            fn ($query) =>
                $query->where(
                    'im.created_at',
                    '>=',
                    $from
                )
        )
        ->when(
            $to,
            fn ($query) =>
                $query->where(
                    'im.created_at',
                    '<=',
                    $to
                )
        )
        ->select([
            'p.id as product_id',
            'p.code_product as codigo_producto',
            'p.name_product as producto',
            'w.id as warehouse_id',
            'w.name as almacen',
            DB::raw("
                CASE
                    WHEN im.unit IN ('ROLLOS', 'rollos', 'kilos')
                        THEN 'ROLLOS'
                    ELSE 'METROS'
                END as unidad
            "),
        ])
        ->groupBy(
            'p.id',
            'p.code_product',
            'p.name_product',
            'w.id',
            'w.name',
            'im.unit'
        )
        ->get();

    /*
    |--------------------------------------------------------------------------
    | LLAVES ÚNICAS
    |--------------------------------------------------------------------------
    */

    $movementKeys = $stockRows
        ->merge($movementRows)
        ->unique(
            fn ($row) =>
                $row->product_id .
                '-' .
                $row->warehouse_id .
                '-' .
                $row->unidad
        )
        ->sortBy([
            ['producto', 'asc'],
            ['almacen', 'asc'],
            ['unidad', 'asc'],
        ])
        ->values();

    /*
    |--------------------------------------------------------------------------
    | CONSTRUIR REPORTE
    |--------------------------------------------------------------------------
    */

    $items = $movementKeys->map(function ($row) use ($from, $to) {

        $unitAliases = $row->unidad === 'ROLLOS'
            ? ['ROLLOS', 'rollos', 'kilos']
            : ['METROS', 'metros'];

        /*
        |--------------------------------------------------------------------------
        | MOVIMIENTOS BASE
        |--------------------------------------------------------------------------
        */

        $movementsBase = DB::table('inventory_movements as im')
            ->where('im.store_id', $row->product_id)
            ->where('im.warehouse_id', $row->warehouse_id)
            ->whereIn('im.unit', $unitAliases);

        /*
        |--------------------------------------------------------------------------
        | STOCK ACTUAL
        |--------------------------------------------------------------------------
        */

        $stock = DB::table('warehouse_stocks')
            ->where(
                'warehouse_id',
                $row->warehouse_id
            )
            ->where(
                'store_id',
                $row->product_id
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | SALDO INICIAL HISTÓRICO
        |--------------------------------------------------------------------------
        |
        | El saldo inicial se reconstruye desde el momento en que
        | se creó el registro warehouse_stock.
        |
        | Fórmula:
        |
        | saldo inicial =
        | saldo actual
        | - movimientos netos posteriores a la creación
        |
        | Esto incluye:
        | INGRESO                  -> +
        | TRANSFERENCIA_ENTRADA    -> +
        | SALIDA                   -> -
        | TRANSFERENCIA_SALIDA     -> -
        |
        */

        $saldoInicial = 0.0;

        if ($stock && $stock->created_at) {

            $stockCreatedAt = Carbon::parse(
                $stock->created_at
            );

            $saldoActualHistorico = (float) (
                $row->unidad === 'ROLLOS'
                    ? $stock->kilos_available
                    : $stock->metros_available
            );

            $movimientosNetos = (float) (clone $movementsBase)
                ->where(
                    'im.created_at',
                    '>=',
                    $stockCreatedAt
                )
                ->selectRaw("
                    COALESCE(
                        SUM(
                            CASE

                                WHEN im.type IN (
                                    'INGRESO',
                                    'TRANSFERENCIA_ENTRADA'
                                )
                                THEN im.quantity

                                WHEN im.type IN (
                                    'SALIDA',
                                    'TRANSFERENCIA_SALIDA'
                                )
                                THEN -im.quantity

                                ELSE 0

                            END
                        ),
                        0
                    ) as neto
                ")
                ->value('neto');

            $saldoInicial =
                $saldoActualHistorico
                - $movimientosNetos;
        }

        /*
        |--------------------------------------------------------------------------
        | MOVIMIENTOS DEL PERÍODO FILTRADO
        |--------------------------------------------------------------------------
        */

        $periodMovements = clone $movementsBase;

        $periodMovements
            ->when(
                $from,
                fn ($query) =>
                    $query->where(
                        'im.created_at',
                        '>=',
                        $from
                    )
            )
            ->when(
                $to,
                fn ($query) =>
                    $query->where(
                        'im.created_at',
                        '<=',
                        $to
                    )
            );

        /*
        |--------------------------------------------------------------------------
        | INGRESOS
        |--------------------------------------------------------------------------
        */

        $ingresos = (float) (clone $periodMovements)
            ->where(
                'im.type',
                'INGRESO'
            )
            ->sum('im.quantity');

        /*
        |--------------------------------------------------------------------------
        | SALIDAS
        |--------------------------------------------------------------------------
        |
        | Se toman de inventory_movements.
        |
        */

        $salidas = (float) (clone $periodMovements)
            ->where(
                'im.type',
                'SALIDA'
            )
            ->sum('im.quantity');

        /*
        |--------------------------------------------------------------------------
        | TRANSFERENCIAS RECIBIDAS
        |--------------------------------------------------------------------------
        */

        $transferColumn =
            $row->unidad === 'ROLLOS'
                ? 'kilos'
                : 'metros';

        $transferenciasRecibidas =
            (float) DB::table('transfer_items as ti')
                ->join(
                    'transfers as t',
                    't.id',
                    '=',
                    'ti.transfer_id'
                )
                ->where(
                    'ti.store_id',
                    $row->product_id
                )
                ->where(
                    't.to_warehouse_id',
                    $row->warehouse_id
                )
                ->when(
                    $from,
                    fn ($query) =>
                        $query->where(
                            't.created_at',
                            '>=',
                            $from
                        )
                )
                ->when(
                    $to,
                    fn ($query) =>
                        $query->where(
                            't.created_at',
                            '<=',
                            $to
                        )
                )
                ->sum(
                    "ti.{$transferColumn}_received"
                );

        /*
        |--------------------------------------------------------------------------
        | TRANSFERENCIAS ENVIADAS
        |--------------------------------------------------------------------------
        */

        $transferenciasEnviadas =
            (float) DB::table('transfer_items as ti')
                ->join(
                    'transfers as t',
                    't.id',
                    '=',
                    'ti.transfer_id'
                )
                ->where(
                    'ti.store_id',
                    $row->product_id
                )
                ->where(
                    't.from_warehouse_id',
                    $row->warehouse_id
                )
                ->when(
                    $from,
                    fn ($query) =>
                        $query->where(
                            't.created_at',
                            '>=',
                            $from
                        )
                )
                ->when(
                    $to,
                    fn ($query) =>
                        $query->where(
                            't.created_at',
                            '<=',
                            $to
                        )
                )
                ->sum(
                    "ti.{$transferColumn}_shipped"
                );

        /*
        |--------------------------------------------------------------------------
        | SALDO ACTUAL
        |--------------------------------------------------------------------------
        */

        $saldoActual = $stock
            ? (
                $row->unidad === 'ROLLOS'
                    ? (float) $stock->kilos_available
                    : (float) $stock->metros_available
            )
            : 0.0;

        /*
        |--------------------------------------------------------------------------
        | RESULTADO
        |--------------------------------------------------------------------------
        */

        return [
            'product_id' =>
                $row->product_id,

            'codigo_producto' =>
                $row->codigo_producto,

            'producto' =>
                $row->producto,

            'warehouse_id' =>
                $row->warehouse_id,

            'almacen' =>
                $row->almacen,

            'unidad' =>
                $row->unidad,

            'saldo_inicial' =>
                $saldoInicial,

            'ingresos' =>
                $ingresos,

            'salidas' =>
                $salidas,

            'transferencias_recibidas' =>
                $transferenciasRecibidas,

            'transferencias_enviadas' =>
                $transferenciasEnviadas,

            'saldo_actual' =>
                $saldoActual,
        ];
    });

    /*
    |--------------------------------------------------------------------------
    | PAGINACIÓN
    |--------------------------------------------------------------------------
    */

    $page = LengthAwarePaginator::resolveCurrentPage();

    $rows = new LengthAwarePaginator(
        $items
            ->forPage($page, $perPage)
            ->values(),

        $items->count(),

        $perPage,

        $page,

        [
            'path' => $request->url(),
            'query' => $request->query(),
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | VISTA
    |--------------------------------------------------------------------------
    */

    return Inertia::render(
        'Reports/MovimientoProductos',
        [
            'rows' => $rows,

            'filters' => [
                'from' =>
                    $data['from'] ?? '',

                'to' =>
                    $data['to'] ?? '',

                'warehouse_id' =>
                    $data['warehouse_id'] ?? null,

                'product_id' =>
                    $data['product_id'] ?? null,

                'unit' =>
                    $data['unit'] ?? '',

                'per_page' =>
                    $perPage,
            ],

            'warehouses' =>
                $warehouses,

            'products' =>
                $productsCatalog,
        ]
    );
}

    /**
     * Reporte 3: Transferencias.
     */
    public function transferencias(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'from_warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'to_warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'status' => ['nullable', 'string', 'max:30'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $assignedWarehouseIds = $this->assignedWarehouseIds($request);
        $perPage = $this->reportPerPage($request);
        $warehouses = $this->reportWarehouses($assignedWarehouseIds);


        $query = DB::table('transfers as t')
            ->join('warehouses as wo', 'wo.id', '=', 't.from_warehouse_id')
            ->join('warehouses as wd', 'wd.id', '=', 't.to_warehouse_id')
            ->join('users as ur', 'ur.id', '=', 't.requested_by')
            ->leftJoin('users as us', 'us.id', '=', 't.shipped_by')
            ->leftJoin('users as ue', 'ue.id', '=', 't.received_by')
            ->join('transfer_items as ti', 'ti.transfer_id', '=', 't.id')
            ->join('stores as p', 'p.id', '=', 'ti.store_id')
            ->where('p.is_active', true)
            ->when($assignedWarehouseIds !== null, function ($query) use ($assignedWarehouseIds) {
                $query->where(function ($query) use ($assignedWarehouseIds) {
                    $query->whereIn('t.from_warehouse_id', $assignedWarehouseIds)
                        ->orWhereIn('t.to_warehouse_id', $assignedWarehouseIds);
                });
            })
            ->select([
                't.id',
                't.code as transferencia_code',
                't.status',
                't.created_at as fecha_solicitud',
                't.shipped_at as fecha_despacho',
                't.received_at as fecha_recepcion',
                'wo.name as almacen_origen',
                'wd.name as almacen_destino',
                'ur.name as solicitado_por',
                'us.name as despachado_por',
                'ue.name as recibido_por',
                'p.code_product as codigo_producto',
                'p.name_product as producto',
                'ti.kilos_requested as rollos_solicitados',
                'ti.metros_requested as metros_solicitados',
                'ti.kilos_shipped as rollos_despachados',
                'ti.metros_shipped as metros_despachados',
                'ti.kilos_received as rollos_recibidos',
                'ti.metros_received as metros_recibidos',
            ])
            ->when(!empty($data['from']), fn ($query) => $query->where('t.created_at', '>=', Carbon::parse($data['from'])->startOfDay()))
            ->when(!empty($data['to']), fn ($query) => $query->where('t.created_at', '<=', Carbon::parse($data['to'])->endOfDay()))
            ->when($data['from_warehouse_id'] ?? null, fn ($query, $id) => $query->where('t.from_warehouse_id', $id))
            ->when($data['to_warehouse_id'] ?? null, fn ($query, $id) => $query->where('t.to_warehouse_id', $id))
            ->when(!empty($data['status']), fn ($query) => $query->where('t.status', $data['status']))
            ->when(!empty($data['search']), function ($query) use ($data) {
                $search = trim($data['search']);

                $query->where(function ($query) use ($search) {
                    $query->where('t.code', 'like', "%{$search}%")
                        ->orWhere('p.code_product', 'like', "%{$search}%")
                        ->orWhere('p.name_product', 'like', "%{$search}%")
                        ->orWhere('wo.name', 'like', "%{$search}%")
                        ->orWhere('wd.name', 'like', "%{$search}%");
                });
            });
 
        $rows = $query
            ->orderByDesc('t.created_at')
            ->orderBy('t.id')
            ->orderBy('ti.id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Reports/Transferencias', [
            'rows' => $rows,
            'filters' => [
                'from' => $data['from'] ?? '',
                'to' => $data['to'] ?? '',
                'from_warehouse_id' => $data['from_warehouse_id'] ?? null,
                'to_warehouse_id' => $data['to_warehouse_id'] ?? null,
                'status' => $data['status'] ?? '',
                'search' => $data['search'] ?? '',
                'per_page' => $perPage,
            ],
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Reporte 4: Inventario actual.
     */
    public function inventario(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'product_id' => ['nullable', 'integer', 'exists:stores,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $assignedWarehouseIds = $this->assignedWarehouseIds($request);
        $perPage = $this->reportPerPage($request);
        $warehouses = $this->reportWarehouses($assignedWarehouseIds);
        $products = $this->reportProducts();

        $query = DB::table('warehouse_stocks as ws')
            ->join('warehouses as w', 'w.id', '=', 'ws.warehouse_id')
            ->join('stores as p', 'p.id', '=', 'ws.store_id')
            ->where('p.is_active', true)
            ->where('w.is_active', true)
            ->when($assignedWarehouseIds !== null, fn ($query) => $query->whereIn('ws.warehouse_id', $assignedWarehouseIds))
            ->select([
                'ws.id',
                'w.id as warehouse_id',
                'w.name as almacen',
                'p.id as product_id',
                'p.code_product as codigo_producto',
                'p.name_product as producto',
                DB::raw('ws.kilos_available as rollos'),
                DB::raw('ws.metros_available as metros'),
                'p.minimum_stock as stock_minimo',
            ]) 
            ->when($data['warehouse_id'] ?? null, fn ($query, $id) => $query->where('ws.warehouse_id', $id))
            ->when($data['product_id'] ?? null, fn ($query, $id) => $query->where('ws.store_id', $id))
            ->when(!empty($data['search']), function ($query) use ($data) {
                $search = trim($data['search']);

                $query->where(function ($query) use ($search) {
                    $query->where('p.code_product', 'like', "%{$search}%")
                        ->orWhere('p.name_product', 'like', "%{$search}%")
                        ->orWhere('w.name', 'like', "%{$search}%");
                });
            });

        $rows = $query
            ->orderBy('w.name')
            ->orderBy('p.name_product')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Reports/Inventario', [
            'rows' => $rows,
            'filters' => [
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'product_id' => $data['product_id'] ?? null,
                'search' => $data['search'] ?? '',
                'per_page' => $perPage,
            ],
            'warehouses' => $warehouses,
            'products' => $products,
        ]);
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
