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

        $assignedWarehouseIds = $user->hasRole('admin')
            ? null
            : $user->warehouses()->pluck('warehouses.id');

        $salesBaseQuery = Sale::query();

        if ($assignedWarehouseIds !== null) {
            $salesBaseQuery->whereIn('warehouse_id', $assignedWarehouseIds);
        }

        $salesCount = (clone $salesBaseQuery)->count();
        $totalRevenue = (float) ((clone $salesBaseQuery)->sum('total'));
        $averageTicket = $salesCount > 0
            ? round($totalRevenue / $salesCount, 2)
            : 0.0;

        $topProducts = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('stores', 'stores.id', '=', 'sale_items.store_id')
            ->when(
                $assignedWarehouseIds !== null,
                fn ($query) => $query->whereIn('sales.warehouse_id', $assignedWarehouseIds)
            )
            ->groupBy('sale_items.store_id', 'stores.code_product', 'stores.name_product')
            ->orderByDesc(DB::raw('SUM(sale_items.quantity)'))
            ->limit(5)
            ->get([
                'sale_items.store_id',
                'stores.code_product',
                'stores.name_product',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.line_total) as total_amount'),
            ]);

        $bestSeller = User::query()
            ->join('sales', 'sales.sold_by', '=', 'users.id')
            ->when(
                $assignedWarehouseIds !== null,
                fn ($query) => $query->whereIn('sales.warehouse_id', $assignedWarehouseIds)
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc(DB::raw('SUM(sales.total)'))
            ->first([
                'users.id',
                'users.name',
                DB::raw('COUNT(sales.id) as sales_count'),
                DB::raw('SUM(sales.total) as total_sold'),
            ]);

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $rawDailySales = Sale::query()
            ->selectRaw('DATE(created_at) as sale_date, SUM(total) as total_amount')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->when(
                $assignedWarehouseIds !== null,
                fn ($query) => $query->whereIn('warehouse_id', $assignedWarehouseIds)
            )
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->keyBy('sale_date');

        $monthSalesTrend = [];
        $cursorDate = $monthStart->copy();
        while ($cursorDate->lte($monthEnd)) {
            $key = $cursorDate->toDateString();
            $dayTotal = (float) ($rawDailySales[$key]->total_amount ?? 0);

            $monthSalesTrend[] = [
                'date' => $key,
                'day' => (int) $cursorDate->format('d'),
                'total' => round($dayTotal, 2),
            ];

            $cursorDate->addDay();
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'sales_count' => $salesCount,
                'total_revenue' => round($totalRevenue, 2),
                'average_ticket' => $averageTicket,
                'products_count' => Store::count(),
            ],
            'top_products' => $topProducts,
            'best_seller' => $bestSeller,
            'month_sales_trend' => $monthSalesTrend,
        ]);
    }
}
