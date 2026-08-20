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

const exportExcel = () => {
    const params = new URLSearchParams({
        warehouse_id: filters.warehouse_id
            ? String(filters.warehouse_id)
            : '',
        product_id: filters.product_id
            ? String(filters.product_id)
            : '',
        search: filters.search || '',
    });

    window.location.href =
        `/reports/inventario/export?${params.toString()}`;
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
                <!-- ENCABEZADO -->
                <div
                    class="flex flex-col gap-3
                           sm:flex-row
                           sm:items-center
                           sm:justify-between"
                >
                    <div>
                        <h1
                            class="text-xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            Reporte de Inventario
                        </h1>

                        <p
                            class="mt-1 text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Existencias actuales por almacén y producto.
                        </p>
                    </div>

                    <Button
                        type="button"
                        variant="outline"
                        class="border-slate-300
                               bg-white
                               text-slate-700
                               hover:bg-slate-50
                               dark:border-slate-600
                               dark:bg-slate-800
                               dark:text-slate-300
                               dark:hover:bg-slate-700"
                        @click="applyFilters"
                    >
                        Actualizar
                    </Button>
                </div>

                <!-- FILTROS -->
                <form
                    class="mt-5 grid gap-4
                           md:grid-cols-2
                           lg:grid-cols-4"
                    @submit.prevent="applyFilters"
                >
                    <!-- ALMACÉN -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Almacén
                        </label>

                        <select
                            v-model="filters.warehouse_id"
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-sm text-slate-900
                                   outline-none
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
                                :value="String(warehouse.id)"
                            >
                                {{ warehouse.name }}
                            </option>
                        </select>
                    </div>

                    <!-- PRODUCTO -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Producto
                        </label>

                        <select
                            v-model="filters.product_id"
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-sm text-slate-900
                                   outline-none
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
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Buscar
                        </label>

                        <input
                            v-model="filters.search"
                            type="search"
                            placeholder="Código, producto o almacén..."
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-sm text-slate-900
                                   placeholder:text-slate-400
                                   outline-none
                                   focus:border-blue-500
                                   focus:ring-2
                                   focus:ring-blue-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-100
                                   dark:placeholder:text-slate-500"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- BOTONES -->
                    <div
                        class="flex flex-col gap-2
                            md:col-span-1
                            sm:flex-row"
                    >
                        <Button
                            type="submit"
                            class="flex-1 bg-blue-600
                                text-white
                                hover:bg-blue-700
                                dark:hover:bg-blue-500"
                        >
                            Aplicar filtros
                        </Button>

                        <Button
                            type="button"
                            variant="outline"
                            class="flex-1
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
                            Limpiar
                        </Button>

                        <Button
                            type="button"
                            variant="outline"
                            class="flex-1
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
                           lg:grid-cols-4"
                >
                    <!-- PRODUCTOS -->
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
                            Productos
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ number(props.rows.total, 0) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            registros
                        </p>
                    </div>

                    <!-- ROLLOS -->
                    <div
                        class="rounded-2xl border
                               border-blue-200
                               bg-blue-50 p-4
                               dark:border-blue-500/30
                               dark:bg-blue-500/10"
                    >
                        <p
                            class="text-sm font-medium
                                   text-blue-700
                                   dark:text-blue-400"
                        >
                            Rollos
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ number(totalRollos, 3) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            página actual
                        </p>
                    </div>

                    <!-- METROS -->
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
                            Metros disponibles
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ number(totalMetros, 3) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            página actual
                        </p>
                    </div>

                    <!-- PÁGINA -->
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
                            Página
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ props.rows.current_page }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            de {{ props.rows.last_page }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- TABLA -->
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
                        Inventario por producto
                    </h2>

                    <p
                        class="text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
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
                                    Código
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
                                    Metros
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Stock mínimo
                                </th>

                                <th
                                    class="px-4 py-3 text-center
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Estado
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y
                                   divide-slate-100
                                   dark:divide-slate-700"
                        >
                            <tr
                                v-for="row in props.rows.data"
                                :key="row.id"
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
                                <!-- ALMACÉN -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.almacen }}
                                </td>

                                <!-- CÓDIGO -->
                                <td
                                    class="px-4 py-3 text-sm
                                           font-medium
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    {{ row.codigo_producto }}
                                </td>

                                <!-- PRODUCTO -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.producto }}
                                </td>

                                <!-- ROLLOS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ number(row.rollos, 3) }}
                                </td>

                                <!-- METROS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ number(row.metros, 3) }}
                                </td>

                                <!-- STOCK MÍNIMO -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ number(row.stock_minimo, 3) }}
                                </td>

                                <!-- ESTADO -->
                                <td
                                    class="px-4 py-3 text-center"
                                >
                                    <span
                                        v-if="
                                            Number(row.metros) <=
                                            Number(row.stock_minimo)
                                        "
                                        class="inline-flex
                                               rounded-full
                                               bg-red-100 px-2.5 py-1
                                               text-xs font-medium
                                               text-red-700
                                               dark:bg-red-500/15
                                               dark:text-red-400"
                                    >
                                        Stock bajo
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex
                                               rounded-full
                                               bg-green-100 px-2.5 py-1
                                               text-xs font-medium
                                               text-green-700
                                               dark:bg-green-500/15
                                               dark:text-green-400"
                                    >
                                        Disponible
                                    </span>
                                </td>
                            </tr>

                            <!-- SIN RESULTADOS -->
                            <tr
                                v-if="
                                    props.rows.data.length ===
                                    0
                                "
                            >
                                <td
                                    colspan="7"
                                    class="px-4 py-8
                                           text-center text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay registros de inventario
                                    para los filtros seleccionados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- PAGINACIÓN -->
            <section
                v-if="props.rows.last_page > 1"
                class="flex flex-col
                       items-center justify-between
                       gap-3 rounded-xl border
                       border-slate-200
                       bg-white p-4
                       dark:border-slate-700
                       dark:bg-slate-900
                       sm:flex-row"
            >
                <p
                    class="text-sm
                           text-slate-500
                           dark:text-slate-400"
                >
                    Página
                    {{ props.rows.current_page }}
                    de
                    {{ props.rows.last_page }}
                </p>

                <div class="flex gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="border-slate-300
                               bg-white
                               text-slate-700
                               hover:bg-slate-50
                               dark:border-slate-600
                               dark:bg-slate-800
                               dark:text-slate-300
                               dark:hover:bg-slate-700"
                        :disabled="
                            props.rows.current_page <= 1
                        "
                        @click="
                            changePage(
                                props.rows.current_page - 1,
                            )
                        "
                    >
                        Anterior
                    </Button>

                    <Button
                        type="button"
                        variant="outline"
                        class="border-slate-300
                               bg-white
                               text-slate-700
                               hover:bg-slate-50
                               dark:border-slate-600
                               dark:bg-slate-800
                               dark:text-slate-300
                               dark:hover:bg-slate-700"
                        :disabled="
                            props.rows.current_page >=
                            props.rows.last_page
                        "
                        @click="
                            changePage(
                                props.rows.current_page + 1,
                            )
                        "
                    >
                        Siguiente
                    </Button>
                </div>
            </section>
        </div>
    </AppLayout>
</template>