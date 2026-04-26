<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reportes', href: '/reports' },
];

const props = defineProps<{
    filters: {
        start_date: string;
        end_date: string;
        warehouse_id: number | null;
    };
    warehouses: Array<{
        id: number;
        name: string;
        code: string;
    }>;
    sales_by_warehouse: Array<{
        warehouse_id: number;
        warehouse_name: string;
        warehouse_code: string;
        sales_count: number | string;
        total_sales: number | string;
        total_units: number | string;
    }>;
    inventory_by_warehouse: Array<{
        id: number;
        warehouse_id: number;
        warehouse_name: string;
        warehouse_code: string;
        product_id: number;
        code_product: string;
        name_product: string;
        kilos_available: number | string;
        metros_available: number | string;
    }>;
}>();

const form = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    warehouse_id: props.filters.warehouse_id ? String(props.filters.warehouse_id) : '',
});

const applyFilters = () => {
    router.get(
        '/reports',
        {
            start_date: form.start_date,
            end_date: form.end_date,
            warehouse_id: form.warehouse_id || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const currency = (value: number | string) => {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: 2,
    }).format(Number(value || 0));
};
</script>

<template>
    <Head title="Reportes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <section class="rounded-xl border bg-white p-4">
                <h1 class="text-xl font-semibold">Reportes de ventas e inventario</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Consulta ventas y stock por tienda/almacén en un solo lugar.
                </p>

                <form class="mt-4 grid gap-3 md:grid-cols-4" @submit.prevent="applyFilters">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Fecha inicio</label>
                        <input v-model="form.start_date" type="date" class="w-full rounded border px-3 py-2" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Fecha fin</label>
                        <input v-model="form.end_date" type="date" class="w-full rounded border px-3 py-2" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Tienda / Almacén</label>
                        <select v-model="form.warehouse_id" class="w-full rounded border px-3 py-2">
                            <option value="">Todos</option>
                            <option v-for="warehouse in props.warehouses" :key="warehouse.id" :value="String(warehouse.id)">
                                {{ warehouse.code }} - {{ warehouse.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <Button type="submit" class="w-full">Aplicar filtros</Button>
                    </div>
                </form>
            </section>

            <section class="rounded-xl border bg-white p-4">
                <h2 class="mb-3 text-lg font-semibold">Reporte de ventas por tienda/almacén</h2>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Código</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Nombre</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase">N° ventas</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase">Unidades</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase">Total vendido</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="row in props.sales_by_warehouse" :key="row.warehouse_id">
                                <td class="px-4 py-2 text-sm">{{ row.warehouse_code }}</td>
                                <td class="px-4 py-2 text-sm">{{ row.warehouse_name }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ Number(row.sales_count) }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ Number(row.total_units).toFixed(2) }}</td>
                                <td class="px-4 py-2 text-right text-sm font-medium">{{ currency(row.total_sales) }}</td>
                            </tr>
                            <tr v-if="props.sales_by_warehouse.length === 0">
                                <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">
                                    No hay ventas en el rango seleccionado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border bg-white p-4">
                <h2 class="mb-3 text-lg font-semibold">Reporte de inventario por producto</h2>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Almacén</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Código prod.</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Producto</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase">Kilos disp.</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase">Metros disp.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="row in props.inventory_by_warehouse" :key="row.id">
                                <td class="px-4 py-2 text-sm">{{ row.warehouse_code }} - {{ row.warehouse_name }}</td>
                                <td class="px-4 py-2 text-sm">{{ row.code_product }}</td>
                                <td class="px-4 py-2 text-sm">{{ row.name_product }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ Number(row.kilos_available).toFixed(3) }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ Number(row.metros_available).toFixed(3) }}</td>
                            </tr>
                            <tr v-if="props.inventory_by_warehouse.length === 0">
                                <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">
                                    No hay registros de stock para los filtros seleccionados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>