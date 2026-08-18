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
        <div
            class="space-y-6 bg-slate-50 p-4
                   dark:bg-slate-950"
        >
            <!-- ENCABEZADO -->
            <section
                class="rounded-xl border
                       border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
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
                            Movimiento de Productos
                        </h1>

                        <p
                            class="mt-1 text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Saldo inicial, movimientos y saldo actual
                            por producto.
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
                            v-model="filters.from"
                            type="date"
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
                            v-model="filters.to"
                            type="date"
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

                    <!-- UNIDAD -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Unidad
                        </label>

                        <select
                            v-model="filters.unit"
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
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Registros por página
                        </label>

                        <select
                            v-model.number="filters.per_page"
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
                            <option :value="10">
                                10
                            </option>

                            <option :value="25">
                                25
                            </option>

                            <option :value="50">
                                50
                            </option>

                            <option :value="100">
                                100
                            </option>
                        </select>
                    </div>

                    <!-- BOTONES -->
                    <div
                        class="flex gap-2
                               md:col-span-2
                               lg:col-span-2"
                    >
                        <Button
                            type="submit"
                            class="flex-1
                                   bg-blue-600
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
                            Limpiar filtros
                        </Button>
                    </div>
                </form>

                <!-- RESUMEN -->
                <div
                    class="mt-6 grid gap-4
                           sm:grid-cols-2
                           lg:grid-cols-4"
                >
                    <!-- INGRESOS -->
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
                            Ingresos
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ number(totalIngresos, 3) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            página actual
                        </p>
                    </div>

                    <!-- SALIDAS -->
                    <div
                        class="rounded-2xl border
                               border-red-200
                               bg-red-50 p-4
                               dark:border-red-500/30
                               dark:bg-red-500/10"
                    >
                        <p
                            class="text-sm font-medium
                                   text-red-700
                                   dark:text-red-400"
                        >
                            Salidas
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ number(totalSalidas, 3) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            página actual
                        </p>
                    </div>

                    <!-- TRANSFERENCIAS RECIBIDAS -->
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
                            Transferencias recibidas
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ number(totalTransferenciasRecibidas, 3) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            página actual
                        </p>
                    </div>

                    <!-- TRANSFERENCIAS ENVIADAS -->
                    <div
                        class="rounded-2xl border
                               border-orange-200
                               bg-orange-50 p-4
                               dark:border-orange-500/30
                               dark:bg-orange-500/10"
                    >
                        <p
                            class="text-sm font-medium
                                   text-orange-700
                                   dark:text-orange-400"
                        >
                            Transferencias enviadas
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ number(totalTransferenciasEnviadas, 3) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            página actual
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
                        Detalle de movimientos
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
                        class="w-full min-w-[1300px]
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
                                    Producto
                                </th>

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
                                    Unidad
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Saldo inicial
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Ingresos
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Salidas
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Transferencias recibidas
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Transferencias enviadas
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Saldo actual
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
                                :key="
                                    `${row.product_id}-${row.warehouse_id}-${row.unidad}`
                                "
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
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

                                <!-- ALMACÉN -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.almacen }}
                                </td>

                                <!-- UNIDAD -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{
                                        row.unidad === 'kilos'
                                            ? 'rollos'
                                            : row.unidad
                                    }}
                                </td>

                                <!-- SALDO INICIAL -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ number(row.saldo_inicial, 3) }}
                                </td>

                                <!-- INGRESOS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-green-700
                                           dark:text-green-400"
                                >
                                    {{ number(row.ingresos, 3) }}
                                </td>

                                <!-- SALIDAS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-red-700
                                           dark:text-red-400"
                                >
                                    {{ number(row.salidas, 3) }}
                                </td>

                                <!-- TRANSFERENCIAS RECIBIDAS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-blue-700
                                           dark:text-blue-400"
                                >
                                    {{
                                        number(
                                            row.transferencias_recibidas,
                                            3,
                                        )
                                    }}
                                </td>

                                <!-- TRANSFERENCIAS ENVIADAS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-orange-700
                                           dark:text-orange-400"
                                >
                                    {{
                                        number(
                                            row.transferencias_enviadas,
                                            3,
                                        )
                                    }}
                                </td>

                                <!-- SALDO ACTUAL -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm font-bold
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    {{ number(row.saldo_actual, 3) }}
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
                                    colspan="10"
                                    class="px-4 py-8
                                           text-center text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay movimientos registrados
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