<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reportes', href: '/reports' },
    { title: 'Inventario', href: '/reports/inventario' },
];

const props = defineProps<{
    rows: {
        data: Array<{
            id: number;
            warehouse_id: number;
            almacen: string;
            codigo_producto: string;
            producto: string;
            rollos: number | string;
            metros: number | string;
            stock_minimo: number | string;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };

    filters: {
        warehouse_id: number | string | null;
        product_id: number | string | null;
        search: string;
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
    warehouse_id: props.filters.warehouse_id
        ? String(props.filters.warehouse_id)
        : '',

    product_id: props.filters.product_id
        ? String(props.filters.product_id)
        : '',

    search: props.filters.search ?? '',

    per_page: props.filters.per_page ?? 25,
};

const totalRollos = computed(() =>
    props.rows.data.reduce(
        (sum, row) => sum + Number(row.rollos || 0),
        0,
    ),
);

const totalMetros = computed(() =>
    props.rows.data.reduce(
        (sum, row) => sum + Number(row.metros || 0),
        0,
    ),
);

const currency = (value: number | string) => {
    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
};

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
        '/reports/inventario',
        {
            warehouse_id: filters.warehouse_id || undefined,
            product_id: filters.product_id || undefined,
            search: filters.search || undefined,
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
        '/reports/inventario',
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
        '/reports/inventario',
        {
            warehouse_id: filters.warehouse_id || undefined,
            product_id: filters.product_id || undefined,
            search: filters.search || undefined,
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
    <Head title="Reporte de Inventario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">

            <!-- ENCABEZADO -->
            <section class="rounded-xl border bg-white p-4">
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1 class="text-xl font-semibold">
                            Reporte de Inventario
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Existencias actuales por almacén y producto.
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

                    <!-- BÚSQUEDA -->
                    <div class="lg:col-span-2">
                        <label class="mb-1 block text-sm font-medium">
                            Buscar
                        </label>

                        <input
                            v-model="filters.search"
                            type="search"
                            placeholder="Código, producto o almacén..."
                            class="w-full rounded border px-3 py-2"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- BOTONES -->
                    <div class="flex gap-2 lg:col-span-4">
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

                    <div
                        class="rounded-2xl border border-gray-200 bg-slate-50 p-4"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Productos
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ number(props.rows.total, 0) }}
                        </p>

                        <p class="text-sm text-gray-500">
                            registros
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-gray-200 bg-blue-50 p-4"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Rollos
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ number(totalRollos, 3) }}
                        </p>

                        <p class="text-sm text-gray-500">
                            página actual
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-gray-200 bg-emerald-50 p-4"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Metros disponibles
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ number(totalMetros, 3) }}
                        </p>

                        <p class="text-sm text-gray-500">
                            página actual
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-gray-200 bg-yellow-50 p-4"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Página
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ props.rows.current_page }}
                        </p>

                        <p class="text-sm text-gray-500">
                            de {{ props.rows.last_page }}
                        </p>
                    </div>

                </div>
            </section>

            <!-- TABLA -->
            <section class="rounded-xl border bg-white p-4">

                <div class="mb-4">
                    <h2 class="text-lg font-semibold">
                        Inventario por producto
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
                        class="w-full min-w-[900px] divide-y divide-gray-200"
                    >
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Almacén
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Código
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Producto
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Rollos
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Metros
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Stock mínimo
                                </th>

                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase">
                                    Estado
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">

                            <tr
                                v-for="row in props.rows.data"
                                :key="row.id"
                            >
                                <td class="px-4 py-3 text-sm">
                                    {{ row.almacen }}
                                </td>

                                <td class="px-4 py-3 text-sm font-medium">
                                    {{ row.codigo_producto }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ row.producto }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm">
                                    {{ number(row.rollos, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm">
                                    {{ number(row.metros, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm">
                                    {{ number(row.stock_minimo, 3) }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span
                                        v-if="Number(row.metros) <= Number(row.stock_minimo)"
                                        class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700"
                                    >
                                        Stock bajo
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700"
                                    >
                                        Disponible
                                    </span>
                                </td>
                            </tr>

                            <tr
                                v-if="props.rows.data.length === 0"
                            >
                                <td
                                    colspan="7"
                                    class="px-4 py-8 text-center text-sm text-gray-500"
                                >
                                    No hay registros de inventario para los filtros seleccionados.
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