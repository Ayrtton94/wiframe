<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;


Route::get('/', function () {
    if (auth()->check()) {
        return Inertia::render('Dashboard');
    } else {
        return redirect('/login');
    }
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // ADMIN: Acceso a proveedores y empleados
    Route::middleware('role:admin')->group(function () {
        Route::resource('suppliers', SuppliersController::class);
        Route::resource('employees', EmployeesController::class);
        Route::resource('transfers', TransferController::class);

        Route::get('users/roles', [UserRoleController::class, 'index'])->name('users.roles.index');
        Route::patch('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');

    });

    // VENDEDOR, ALMACEN: Acceso a clientes y productos
    Route::middleware('role:vendedor,almacen')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::resource('stores', StoreController::class);
    });

    // ADMIN: Todos pueden ver clientes (con control de permisos específicos)
    Route::resource('customers', CustomerController::class)
        ->middleware('permission:view_customers');

    // ADMIN: Todos pueden ver productos (con control de permisos específicos)
    Route::resource('stores', StoreController::class)
        ->middleware('permission:view_products');
});

require __DIR__.'/settings.php';
