<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reportes', href: '/reports' },
    {
        title: 'Movimiento de Productos',
        href: '/reports/movimiento-productos',
    },
];

const props = defineProps<{
    rows: {
        data: Array<{
            product_id: number;
            warehouse_id: number;
            codigo_producto: string;
            producto: string;
            almacen: string;
            unidad: string;
            saldo_inicial: number | string;
            ingresos: number | string;
            salidas: number | string;
            transferencias_recibidas: number | string;
            transferencias_enviadas: number | string;
            saldo_actual: number | string;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };

    filters: {
        from: string;
        to: string;
        warehouse_id: number | string | null;
        product_id: number | string | null;
        unit: string;
        per_page: number;
    };

    warehouses: Array<{
        id: number;
        name: string;
    }>;

    products: Array<{
        id: number;
        code_product: string;
        name_product: string;
    }>;
}>();

const filters = {
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',

    warehouse_id: props.filters.warehouse_id
        ? String(props.filters.warehouse_id)
        : '',

    product_id: props.filters.product_id
        ? String(props.filters.product_id)
        : '',

    unit: props.filters.unit ?? '',

    per_page: props.filters.per_page ?? 25,
};

const totalIngresos = computed(() =>
    props.rows.data.reduce(
        (sum, row) => sum + Number(row.ingresos || 0),
        0,
    ),
);

const totalSalidas = computed(() =>
    props.rows.data.reduce(
        (sum, row) => sum + Number(row.salidas || 0),
        0,
    ),
);

const totalTransferenciasRecibidas = computed(() =>
    props.rows.data.reduce(
        (sum, row) =>
            sum + Number(row.transferencias_recibidas || 0),
        0,
    ),
);

const totalTransferenciasEnviadas = computed(() =>
    props.rows.data.reduce(
        (sum, row) =>
            sum + Number(row.transferencias_enviadas || 0),
        0,
    ),
);

const number = (
    value: number | string,
    fractionDigits = 3,
) => {
    const numberValue = Number(value);

    if (Number.isNaN(numberValue)) {
        return String(value);
    }

    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: fractionDigits,
    }).format(numberValue);
};

const applyFilters = () => {
    router.get(
        '/reports/movimiento-productos',
        {
            from: filters.from || undefined,
            to: filters.to || undefined,
            warehouse_id: filters.warehouse_id || undefined,
            product_id: filters.product_id || undefined,
            unit: filters.unit || undefined,
            per_page: filters.per_page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    router.get(
        '/reports/movimiento-productos',
        {},
        {
            replace: true,
        },
    );
};

const changePage = (page: number) => {
    if (
        page < 1 ||
        page > props.rows.last_page
    ) {
        return;
    }

    router.get(
        '/reports/movimiento-productos',
        {
            from: filters.from || undefined,
            to: filters.to || undefined,
            warehouse_id: filters.warehouse_id || undefined,
            product_id: filters.product_id || undefined,
            unit: filters.unit || undefined,
            per_page: filters.per_page,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};
</script>

<template>
    <Head title="Movimiento de Productos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">

            <!-- ENCABEZADO -->
            <section class="rounded-xl border bg-white p-4">
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1 class="text-xl font-semibold">
                            Movimiento de Productos
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Saldo inicial, movimientos y saldo actual por producto.
                        </p>
                    </div>

                    <Button
                        type="button"
                        variant="outline"
                        @click="applyFilters"
                    >
                        Actualizar
                    </Button>
                </div>

                <!-- FILTROS -->
                <form
                    class="mt-5 grid gap-3 md:grid-cols-2 lg:grid-cols-4"
                    @submit.prevent="applyFilters"
                >
                    <!-- FECHA INICIO -->
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Fecha inicio
                        </label>

                        <input
                            v-model="filters.from"
                            type="date"
                            class="w-full rounded border px-3 py-2"
                        />
                    </div>

                    <!-- FECHA FIN -->
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Fecha fin
                        </label>

                        <input
                            v-model="filters.to"
                            type="date"
                            class="w-full rounded border px-3 py-2"
                        />
                    </div>

                    <!-- ALMACÉN -->
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Almacén
                        </label>

                        <select
                            v-model="filters.warehouse_id"
                            class="w-full rounded border px-3 py-2"
                        >
                            <option value="">
                                Todos
                            </option>

                            <option
                                v-for="warehouse in props.warehouses"
                                :key="warehouse.id"
                                :value="String(warehouse.id)"
                            >
                                {{ warehouse.name }}
                            </option>
                        </select>
                    </div>

                    <!-- PRODUCTO -->
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Producto
                        </label>

                        <select
                            v-model="filters.product_id"
                            class="w-full rounded border px-3 py-2"
                        >
                            <option value="">
                                Todos
                            </option>

                            <option
                                v-for="product in props.products"
                                :key="product.id"
                                :value="String(product.id)"
                            >
                                {{ product.code_product }} -
                                {{ product.name_product }}
                            </option>
                        </select>
                    </div>

                    <!-- UNIDAD -->
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Unidad
                        </label>

                        <select
                            v-model="filters.unit"
                            class="w-full rounded border px-3 py-2"
                        >
                            <option value="">
                                Todas las unidades
                            </option>

                            <option value="ROLLOS">
                                Rollos
                            </option>

                            <option value="METROS">
                                Metros
                            </option>
                        </select>
                    </div>
                    <!-- REGISTROS POR PÁGINA -->
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Registros por página
                        </label>

                        <select
                            v-model.number="filters.per_page"
                            class="w-full rounded border px-3 py-2">
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                    </div>
                    <!-- BOTONES -->
                    <div
                        class="flex gap-2 md:col-span-2 lg:col-span-2">
                        <Button
                            type="submit"
                            class="flex-1"
                        >
                            Aplicar filtros
                        </Button>

                        <Button
                            type="button"
                            variant="outline"
                            class="flex-1"
                            @click="clearFilters"
                        >
                            Limpiar filtros
                        </Button>
                    </div>
                </form>

                <!-- RESUMEN -->
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                    <!-- INGRESOS -->
                    <div
                        class="rounded-2xl border border-gray-200 bg-emerald-50 p-4"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Ingresos
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ number(totalIngresos, 3) }}
                        </p>

                        <p class="text-sm text-gray-500">
                            página actual
                        </p>
                    </div>

                    <!-- SALIDAS -->
                    <div
                        class="rounded-2xl border border-gray-200 bg-red-50 p-4"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Salidas
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ number(totalSalidas, 3) }}
                        </p>

                        <p class="text-sm text-gray-500">
                            página actual
                        </p>
                    </div>

                    <!-- TRANSFERENCIAS RECIBIDAS -->
                    <div
                        class="rounded-2xl border border-gray-200 bg-blue-50 p-4"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Transferencias recibidas
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ number(totalTransferenciasRecibidas, 3) }}
                        </p>

                        <p class="text-sm text-gray-500">
                            página actual
                        </p>
                    </div>

                    <!-- TRANSFERENCIAS ENVIADAS -->
                    <div
                        class="rounded-2xl border border-gray-200 bg-orange-50 p-4"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Transferencias enviadas
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ number(totalTransferenciasEnviadas, 3) }}
                        </p>

                        <p class="text-sm text-gray-500">
                            página actual
                        </p>
                    </div>

                </div>
            </section>

            <!-- TABLA -->
            <section class="rounded-xl border bg-white p-4">

                <div class="mb-4">
                    <h2 class="text-lg font-semibold">
                        Detalle de movimientos
                    </h2>

                    <p class="text-sm text-gray-500">
                        Mostrando
                        {{ props.rows.from ?? 0 }}
                        -
                        {{ props.rows.to ?? 0 }}
                        de
                        {{ props.rows.total }}
                        registros.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[1300px] divide-y divide-gray-200"
                    >
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Código
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Producto
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Almacén
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Unidad
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Saldo inicial
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Ingresos
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Salidas
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Transferencias recibidas
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Transferencias enviadas
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Saldo actual
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">

                            <tr
                                v-for="row in props.rows.data"
                                :key="`${row.product_id}-${row.warehouse_id}-${row.unidad}`"
                            >
                                <td class="px-4 py-3 text-sm font-medium">
                                    {{ row.codigo_producto }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ row.producto }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ row.almacen }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ row.unidad }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm">
                                    {{ number(row.saldo_inicial, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm text-green-700">
                                    {{ number(row.ingresos, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm text-red-700">
                                    {{ number(row.salidas, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm text-blue-700">
                                    {{ number(row.transferencias_recibidas, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm text-orange-700">
                                    {{ number(row.transferencias_enviadas, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm font-bold">
                                    {{ number(row.saldo_actual, 3) }}
                                </td>
                            </tr>

                            <tr
                                v-if="props.rows.data.length === 0"
                            >
                                <td
                                    colspan="10"
                                    class="px-4 py-8 text-center text-sm text-gray-500"
                                >
                                    No hay movimientos registrados para los filtros seleccionados.
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </section>

            <!-- PAGINACIÓN -->
            <section
                v-if="props.rows.last_page > 1"
                class="flex flex-col items-center justify-between gap-3 rounded-xl border bg-white p-4 sm:flex-row"
            >
                <p class="text-sm text-gray-500">
                    Página {{ props.rows.current_page }}
                    de {{ props.rows.last_page }}
                </p>

                <div class="flex gap-2">

                    <Button
                        type="button"
                        variant="outline"
                        :disabled="props.rows.current_page <= 1"
                        @click="changePage(props.rows.current_page - 1)"
                    >
                        Anterior
                    </Button>

                    <Button
                        type="button"
                        variant="outline"
                        :disabled="props.rows.current_page >= props.rows.last_page"
                        @click="changePage(props.rows.current_page + 1)"
                    >
                        Siguiente
                    </Button>

                </div>
            </section>

        </div>
    </AppLayout>
</template>