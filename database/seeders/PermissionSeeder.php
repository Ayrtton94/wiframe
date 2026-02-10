<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear Roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $vendedor = Role::firstOrCreate(['name' => 'vendedor', 'guard_name' => 'web']);
        $almacen = Role::firstOrCreate(['name' => 'almacen', 'guard_name' => 'web']);
        $contador = Role::firstOrCreate(['name' => 'contador', 'guard_name' => 'web']);

        // ==================== PERMISOS PROVEEDORES ====================
        $supplier_permissions = [
            'view_suppliers',
            'create_supplier',
            'edit_supplier',
            'delete_supplier',
        ];

        $supplier_ids = [];
        foreach ($supplier_permissions as $permission) {
            $p = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $supplier_ids[] = $p->id;
        }

        // ==================== PERMISOS CLIENTES ====================
        $customer_permissions = [
            'view_customers',
            'create_customer',
            'edit_customer',
            'delete_customer',
        ];

        $customer_ids = [];
        foreach ($customer_permissions as $permission) {
            $p = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $customer_ids[] = $p->id;
        }

        // ==================== PERMISOS PRODUCTOS ====================
        $store_permissions = [
            'view_products',
            'create_product',
            'edit_product',
            'delete_product',
        ];

        $store_ids = [];
        foreach ($store_permissions as $permission) {
            $p = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $store_ids[] = $p->id;
        }

        // ==================== PERMISOS EMPLEADOS ====================
        $employee_permissions = [
            'view_employees',
            'create_employee',
            'edit_employee',
            'delete_employee',
        ];

        $employee_ids = [];
        foreach ($employee_permissions as $permission) {
            $p = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $employee_ids[] = $p->id;
        }

        // ==================== PERMISOS REPORTES ====================
        $report_permissions = [
            'view_reports',
            'export_reports',
        ];

        $report_ids = [];
        foreach ($report_permissions as $permission) {
            $p = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $report_ids[] = $p->id;
        }

        // ==================== ASIGNAR PERMISOS A ROLES ====================

        // ADMIN: Acceso total
        $all_permissions = array_merge($supplier_ids, $customer_ids, $store_ids, $employee_ids, $report_ids);
        $admin->permissions()->sync($all_permissions);

        // VENDEDOR: Clientes, Productos, Reportes
        $vendedor_permissions = array_merge(
            array_slice($customer_ids, 0, 2), // view, create customer
            array_slice($store_ids, 0, 1),    // view products
            $report_ids                        // all reports
        );
        $vendedor->permissions()->sync($vendedor_permissions);

        // ALMACEN: Productos, Empleados, Reportes
        $almacen_permissions = array_merge(
            $store_ids,       // all products
            array_slice($employee_ids, 0, 1), // view employees
            $report_ids       // all reports
        );
        $almacen->permissions()->sync($almacen_permissions);

        // CONTADOR: Proveedores, Productos, Reportes
        $contador_permissions = array_merge(
            array_slice($supplier_ids, 0, 1), // view suppliers
            $store_ids,                        // all products
            $report_ids                        // all reports
        );
        $contador->permissions()->sync($contador_permissions);
    }
}
