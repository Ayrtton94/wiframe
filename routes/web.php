<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseStockController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    } else {
        return redirect('/login');
    }
})->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // ADMIN: Acceso a proveedores y empleados
    Route::middleware('role:admin')->group(function () {
        Route::resource('suppliers', SuppliersController::class);
        Route::resource('employees', EmployeesController::class);

        Route::get('users/roles', [UserRoleController::class, 'index'])->name('users.roles.index');
        Route::patch('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
    });


    // ADMIN: Gestión completa de almacenes y traslados
    Route::middleware('role:admin')->group(function () {
        Route::resource('warehouses', WarehouseController::class)
            ->except(['show', 'create', 'edit']);

        Route::post('warehouse-stocks', [WarehouseStockController::class, 'store'])->name('warehouse-stocks.store');
        Route::patch('warehouse-stocks/{warehouseStock}', [WarehouseStockController::class, 'update'])->name('warehouse-stocks.update');
        Route::delete('warehouse-stocks/{warehouseStock}', [WarehouseStockController::class, 'destroy'])->name('warehouse-stocks.destroy');
    });

    // ADMIN + ALMACEN: gestión de traslados
    Route::middleware('role:admin,almacen')->group(function () {
        Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');
        Route::post('transfers', [TransferController::class, 'store'])->name('transfers.store');
        Route::get('transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show');
        Route::post('transfers/{transfer}/ship', [TransferController::class, 'ship'])->name('transfers.ship');
        Route::post('transfers/{transfer}/receive', [TransferController::class, 'receive'])->name('transfers.receive');
    });



    // ADMIN + ALMACEN: visualizar stock
    Route::middleware('role:admin,almacen')->group(function () {
        Route::get('warehouse-stocks', [WarehouseStockController::class, 'index'])->name('warehouse-stocks.index');
    });

    // VENDEDOR: Acceso a clientes y productos
    Route::middleware('role:vendedor')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::resource('stores', StoreController::class);
    });

    // ADMIN: Todos pueden ver clientes (con control de permisos específicos)
    Route::resource('customers', CustomerController::class)
        ->middleware('permission:view_customers');

    // ADMIN: Todos pueden ver productos (con control de permisos específicos)
    Route::resource('stores', StoreController::class)
        ->middleware('permission:view_products');   
    
    Route::resource('catalog', CatalogController::class);
    Route::get('/catalog/{id}', [CatalogController::class, 'show']);

    Route::get('reports', [ReportController::class, 'index'])
        ->middleware('role:admin,almacen,tienda,vendedor')
        ->name('reports.index');

    Route::middleware('role:admin,vendedor,tienda')->group(function () {
        Route::resource('sales', SaleController::class)->only(['index', 'store', 'show']);
    });



});

require __DIR__.'/settings.php';