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
        <div
            class="space-y-6 bg-slate-50 p-4
                   dark:bg-slate-950"
        >
            <!-- ENCABEZADO + FILTROS -->
            <section
                class="rounded-xl border
                       border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div>
                    <h1
                        class="text-xl font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        Reportes de salidas e inventario
                    </h1>

                    <p
                        class="mt-1 text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Consulta salidas y stock por tienda/almacén
                        en un solo lugar.
                    </p>
                </div>

                <form
                    class="mt-5 grid gap-4
                           md:grid-cols-4"
                    @submit.prevent="applyFilters"
                >
                    <!-- FECHA INICIO -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Fecha inicio
                        </label>

                        <input
                            v-model="form.start_date"
                            type="date"
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-slate-900
                                   shadow-sm outline-none
                                   focus:border-blue-500
                                   focus:ring-2
                                   focus:ring-blue-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-100"
                        />
                    </div>

                    <!-- FECHA FIN -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Fecha fin
                        </label>

                        <input
                            v-model="form.end_date"
                            type="date"
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-slate-900
                                   shadow-sm outline-none
                                   focus:border-blue-500
                                   focus:ring-2
                                   focus:ring-blue-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-100"
                        />
                    </div>

                    <!-- ALMACÉN -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Tienda / Almacén
                        </label>

                        <select
                            v-model="form.warehouse_id"
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-slate-900
                                   shadow-sm outline-none
                                   focus:border-blue-500
                                   focus:ring-2
                                   focus:ring-blue-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-100"
                        >
                            <option value="">
                                Todos
                            </option>

                            <option
                                v-for="warehouse in props.warehouses"
                                :key="warehouse.id"
                                :value="
                                    String(
                                        warehouse.id,
                                    )
                                "
                            >
                                {{ warehouse.code }} -
                                {{ warehouse.name }}
                            </option>
                        </select>
                    </div>

                    <!-- RESPONSABLE -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Responsable
                        </label>

                        <select
                            v-model="form.seller_id"
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-slate-900
                                   shadow-sm outline-none
                                   focus:border-blue-500
                                   focus:ring-2
                                   focus:ring-blue-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-100"
                        >
                            <option value="">
                                Todos
                            </option>

                            <option
                                v-for="seller in props.sellers"
                                :key="seller.id"
                                :value="
                                    String(
                                        seller.id,
                                    )
                                "
                            >
                                {{ seller.name }}
                            </option>
                        </select>
                    </div>

                    <!-- BÚSQUEDA -->
                    <div
                        class="md:col-span-2"
                    >
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Buscar
                        </label>

                        <input
                            v-model="form.search"
                            type="search"
                            placeholder="N° Salida, cliente, producto..."
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-slate-900
                                   placeholder:text-slate-400
                                   shadow-sm outline-none
                                   focus:border-blue-500
                                   focus:ring-2
                                   focus:ring-blue-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-100
                                   dark:placeholder:text-slate-500"
                        />
                    </div>

                    <!-- FILTROS -->
                     <div
                        class="flex flex-col gap-2
                            md:col-span-2
                            sm:flex-row"
                    >
                        <Button
                            type="submit"
                            class="w-full sm:flex-1
                                bg-blue-600 text-white
                                hover:bg-blue-700
                                dark:hover:bg-blue-500"
                        >
                            Aplicar filtros
                        </Button>

                        <Button
                            type="button"
                            variant="outline"
                            class="w-full sm:flex-1
                                border-slate-300
                                bg-white
                                text-slate-700
                                hover:bg-slate-50
                                dark:border-slate-600
                                dark:bg-slate-800
                                dark:text-slate-300
                                dark:hover:bg-slate-700"
                            @click="clearFilters"
                        >
                            Limpiar filtros
                        </Button>
                    </div>
                    
                    <!-- EXPORTAR -->
                    <div
                        class="flex flex-col gap-2
                               md:col-span-2
                               sm:flex-row"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            class="w-full
                                   border-emerald-300
                                   bg-white
                                   text-emerald-700
                                   hover:bg-emerald-50
                                   dark:border-emerald-500/40
                                   dark:bg-slate-800
                                   dark:text-emerald-400
                                   dark:hover:bg-emerald-500/10"
                            @click="exportExcel"
                        >
                            Exportar Excel
                        </Button>
                    </div>
                </form>

                <!-- RESUMEN -->
                <div
                    class="mt-6 grid gap-4
                           sm:grid-cols-2
                           xl:grid-cols-4"
                >
                    <!-- TOTAL VENTAS -->
                    <div
                        class="rounded-2xl border
                               border-slate-200
                               bg-slate-50 p-4
                               dark:border-slate-700
                               dark:bg-slate-800"
                    >
                        <p
                            class="text-sm font-medium
                                   text-slate-600
                                   dark:text-slate-300"
                        >
                            Total ventas
                        </p>

                        <p
                            class="mt-3 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ formatNumber(totalSalesCount) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            salidas
                        </p>
                    </div>

                    <!-- TOTAL VENDIDO -->
                    <div
                        class="rounded-2xl border
                               border-emerald-200
                               bg-emerald-50 p-4
                               dark:border-emerald-500/30
                               dark:bg-emerald-500/10"
                    >
                        <p
                            class="text-sm font-medium
                                   text-emerald-700
                                   dark:text-emerald-400"
                        >
                            Total vendido
                        </p>

                        <p
                            class="mt-3 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ currency(totalSalesAmount) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            en el rango
                        </p>
                    </div>

                    <!-- TOTAL PRODUCTOS -->
                    <div
                        class="rounded-2xl border
                               border-yellow-200
                               bg-yellow-50 p-4
                               dark:border-yellow-500/30
                               dark:bg-yellow-500/10"
                    >
                        <p
                            class="text-sm font-medium
                                   text-yellow-700
                                   dark:text-yellow-400"
                        >
                            Total productos
                        </p>

                        <p
                            class="mt-3 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ formatNumber(totalProductsCount) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            productos
                        </p>
                    </div>

                    <!-- RESPONSABLES -->
                    <div
                        class="rounded-2xl border
                               border-violet-200
                               bg-violet-50 p-4
                               dark:border-violet-500/30
                               dark:bg-violet-500/10"
                    >
                        <p
                            class="text-sm font-medium
                                   text-violet-700
                                   dark:text-violet-400"
                        >
                            Responsables
                        </p>

                        <p
                            class="mt-3 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ formatNumber(totalResponsiblesCount) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            vendedores
                        </p>
                    </div>
                </div>
            </section>

            <!-- REPORTE DE VENTAS -->
            <section
                class="rounded-xl border
                       border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div class="mb-4">
                    <h2
                        class="text-lg font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        Reporte de ventas por tienda
                    </h2>
                </div>

                <div
                    class="overflow-x-auto"
                >
                    <table
                        class="w-full min-w-[700px]
                               divide-y
                               divide-slate-200
                               dark:divide-slate-700"
                    >
                        <thead
                            class="bg-slate-50
                                   dark:bg-slate-800"
                        >
                            <tr>
                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Código
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Nombre
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Fecha
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Vendedor
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    N° salida
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Unidades
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Total vendido
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y
                                   divide-slate-100
                                   dark:divide-slate-700"
                        >
                            <tr
                                v-for="row in props.sales_by_warehouse"
                                :key="
                                    `${row.warehouse_id}-${row.sale_date}-${row.seller_name}`
                                "
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
                                <td
                                    class="px-4 py-2 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.warehouse_code }}
                                </td>

                                <td
                                    class="px-4 py-2 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.warehouse_name }}
                                </td>

                                <td
                                    class="px-4 py-2 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.sale_date }}
                                </td>

                                <td
                                    class="px-4 py-2 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.seller_name }}
                                </td>

                                <td
                                    class="px-4 py-2 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ formatNumber(row.sales_count) }}
                                </td>

                                <td
                                    class="px-4 py-2 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ formatNumber(row.total_units, 2) }}
                                </td>

                                <td
                                    class="px-4 py-2 text-right
                                           text-sm font-medium
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    {{ currency(row.total_sales) }}
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    props.sales_by_warehouse
                                        .length === 0
                                "
                            >
                                <td
                                    colspan="7"
                                    class="px-4 py-6
                                           text-center text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay ventas en el rango
                                    seleccionado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- REPORTE DE INVENTARIO -->
            <section
                class="rounded-xl border
                       border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div class="mb-4">
                    <h2
                        class="text-lg font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        Reporte de inventario por producto
                    </h2>
                </div>

                <div
                    class="overflow-x-auto"
                >
                    <table
                        class="w-full min-w-[900px]
                               divide-y
                               divide-slate-200
                               dark:divide-slate-700"
                    >
                        <thead
                            class="bg-slate-50
                                   dark:bg-slate-800"
                        >
                            <tr>
                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Almacén
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Código prod.
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Producto
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Rollos
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Metros disp.
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y
                                   divide-slate-100
                                   dark:divide-slate-700"
                        >
                            <tr
                                v-for="row in props.inventory_by_warehouse"
                                :key="row.id"
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
                                <td
                                    class="px-4 py-2 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.warehouse_code }} -
                                    {{ row.warehouse_name }}
                                </td>

                                <td
                                    class="px-4 py-2 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.code_product }}
                                </td>

                                <td
                                    class="px-4 py-2 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.name_product }}
                                </td>

                                <td
                                    class="px-4 py-2 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ formatNumber(row.kilos_available, 3) }}
                                </td>

                                <td
                                    class="px-4 py-2 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ formatNumber(row.metros_available, 3) }}
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    props.inventory_by_warehouse
                                        .length === 0
                                "
                            >
                                <td
                                    colspan="5"
                                    class="px-4 py-6
                                           text-center text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay registros de stock para los
                                    filtros seleccionados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>