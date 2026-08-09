<script setup lang="ts">
import { computed } from 'vue';
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
        seller_id: number | null;
        search: string;
    };
    warehouses: Array<{
        id: number;
        name: string;
        code: string;
    }>;
    sellers: Array<{
        id: number;
        name: string;
    }>;
    sales_by_warehouse: Array<{
        warehouse_id: number;
        warehouse_name: string;
        warehouse_code: string;
        sale_date: string;
        seller_name: string;
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
    seller_id: props.filters.seller_id ? String(props.filters.seller_id) : '',
    search: props.filters.search ?? '',
});

const applyFilters = () => {
    router.get(
        '/reports',
        {
            start_date: form.start_date,
            end_date: form.end_date,
            warehouse_id: form.warehouse_id || undefined,
            seller_id: form.seller_id || undefined,
            search: form.search || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const exportExcel = () => {
    const params = new URLSearchParams({
        start_date: form.start_date,
        end_date: form.end_date,
        warehouse_id: form.warehouse_id || '',
        seller_id: form.seller_id || '',
        search: form.search || '',
    });

    window.location.href = `/reports/export?${params.toString()}`;
};

const clearFilters = () => {
    router.get('/reports', {}, { replace: true });
};

const totalSalesCount = computed(() =>
    props.sales_by_warehouse.reduce((sum, row) => sum + Number(row.sales_count || 0), 0),
);

const totalSalesAmount = computed(() =>
    props.sales_by_warehouse.reduce((sum, row) => sum + Number(row.total_sales || 0), 0),
);

const totalProductsCount = computed(() => {
    const productIds = new Set<string>();
    props.inventory_by_warehouse.forEach((row) => {
        productIds.add(String(row.product_id ?? `${row.code_product}-${row.name_product}`));
    });
    return productIds.size;
});

const totalResponsiblesCount = computed(() =>
    new Set(props.sales_by_warehouse.map((row) => row.seller_name)).size,
);

const currency = (value: number | string) => {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: 2,
    }).format(Number(value || 0));
};

const formatNumber = (value: number | string, fractionDigits = 0) => {
    const numberValue = Number(value);

    if (Number.isNaN(numberValue)) {
        return String(value);
    }

    if (fractionDigits <= 0 && Number.isInteger(numberValue)) {
        return numberValue.toString();
    }

    return numberValue.toFixed(fractionDigits).replace(/\.0+$/, '');
};
</script>

<template>
    <Head title="Reportes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <section class="rounded-xl border bg-white p-4">
                <h1 class="text-xl font-semibold">Reportes de salidas e inventario</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Consulta salidas y stock por tienda/almacén en un solo lugar.
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
                    <div>
                        <label class="mb-1 block text-sm font-medium">Responsable</label>
                        <select v-model="form.seller_id" class="w-full rounded border px-3 py-2">
                            <option value="">Todos</option>
                            <option v-for="seller in props.sellers" :key="seller.id" :value="String(seller.id)">
                                {{ seller.name }}
                            </option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Buscar</label>
                        <input
                            v-model="form.search"
                            type="search"
                            placeholder="N° Salida, cliente, producto..."
                            class="w-full rounded border px-3 py-2"
                        />
                    </div>
                    <div class="md:col-span-2 flex flex-col gap-2 sm:flex-row">
                        <Button type="submit" class="w-full">Aplicar filtros</Button>
                        <Button type="button" variant="outline" class="w-full" @click="clearFilters">
                            Limpiar filtros
                        </Button>
                    </div>
                    <div class="md:col-span-2 flex flex-col gap-2 sm:flex-row">
                        <Button type="button" variant="outline" class="w-full" @click="exportExcel">
                            Exportar Excel
                        </Button>
                    </div>
                </form>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-gray-200 bg-slate-50 p-4">
                        <p class="text-sm font-medium text-gray-600">Total ventas</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ formatNumber(totalSalesCount.value) }}</p>
                        <p class="text-sm text-gray-500">salidas</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-emerald-50 p-4">
                        <p class="text-sm font-medium text-gray-600">Total vendido</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ currency(totalSalesAmount.value) }}</p>
                        <p class="text-sm text-gray-500">en el rango</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-yellow-50 p-4">
                        <p class="text-sm font-medium text-gray-600">Total productos</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ formatNumber(totalProductsCount.value) }}</p>
                        <p class="text-sm text-gray-500">productos</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-violet-50 p-4">
                        <p class="text-sm font-medium text-gray-600">Responsables</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ formatNumber(totalResponsiblesCount.value) }}</p>
                        <p class="text-sm text-gray-500">vendedores</p>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border bg-white p-4">
                <h2 class="mb-3 text-lg font-semibold">Reporte de ventas por tienda</h2>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Código</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Nombre</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Fecha</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase">Vendedor</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase">N° salida</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase">Unidades</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase">Total vendido</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="row in props.sales_by_warehouse" :key="`${row.warehouse_id}-${row.sale_date}-${row.seller_name}`">
                                <td class="px-4 py-2 text-sm">{{ row.warehouse_code }}</td>
                                <td class="px-4 py-2 text-sm">{{ row.warehouse_name }}</td>
                                <td class="px-4 py-2 text-sm">{{ row.sale_date }}</td>
                                <td class="px-4 py-2 text-sm">{{ row.seller_name }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ formatNumber(row.sales_count) }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ formatNumber(row.total_units, 2) }}</td>
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
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase">Royos</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold uppercase">Metros disp.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="row in props.inventory_by_warehouse" :key="row.id">
                                <td class="px-4 py-2 text-sm">{{ row.warehouse_code }} - {{ row.warehouse_name }}</td>
                                <td class="px-4 py-2 text-sm">{{ row.code_product }}</td>
                                <td class="px-4 py-2 text-sm">{{ row.name_product }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ formatNumber(row.kilos_available, 3) }}</td>
                                <td class="px-4 py-2 text-right text-sm">{{ formatNumber(row.metros_available, 3) }}</td>
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