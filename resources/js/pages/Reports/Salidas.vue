<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reportes',
        href: '/reports',
    },
];

const props = defineProps<{
    filters: {
        from: string;
        to: string;
        warehouse_id: number | null;
        responsible_id: number | null;
        customer_id: number | null;
        search: string;
        per_page: number;
    };

    warehouses: Array<{
        id: number;
        name: string;
        code: string;
    }>;

    responsibles: Array<{
        id: number;
        name: string;
    }>;

    customers: Array<{
        id: number;
        name: string;
    }>;

    rows: {
        data: Array<{
            id: number;
            salida_code: string;
            fecha_hora: string;
            warehouse_id: number;
            almacen: string;
            customer_id: number | null;
            cliente: string | null;
            responsible_id: number;
            responsable: string;
            product_id: number | null;
            codigo_producto: string | null;
            producto: string | null;
            cantidad: number | string;
            unidad: string;
            precio: number | string;
            total: number | string;
            motivo: string | null;
        }>;
        current_page: number;
        last_page: number;
        total: number;
        per_page: number
        from: number | null;
        to: number | null;
    };
}>();

const form = useForm({
    from: props.filters.from,
    to: props.filters.to,
    warehouse_id: props.filters.warehouse_id
        ? String(props.filters.warehouse_id)
        : '',
    responsible_id: props.filters.responsible_id
        ? String(props.filters.responsible_id)
        : '',
    customer_id: props.filters.customer_id
        ? String(props.filters.customer_id)
        : '',
    search: props.filters.search ?? '',
    per_page: props.filters.per_page ?? 25,
});

const applyFilters = () => {
    router.get(
        '/reports/salidas',
        {
            from: form.from,
            to: form.to,
            warehouse_id: form.warehouse_id || undefined,
            responsible_id: form.responsible_id || undefined,
            customer_id: form.customer_id || undefined,
            search: form.search || undefined,
            per_page: form.per_page,
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
        '/reports/salidas',
        {},
        {
            replace: true,
        },
    );
};

const changePage = (page: number) => {
    router.get(
        '/reports/salidas',
        {
            from: form.from,
            to: form.to,
            warehouse_id: form.warehouse_id || undefined,
            responsible_id: form.responsible_id || undefined,
            customer_id: form.customer_id || undefined,
            search: form.search || undefined,
            per_page: form.per_page,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const total = computed(() =>
    props.rows.data.reduce(
        (sum, row) => sum + Number(row.total || 0),
        0,
    ),
);

const totalUnits = computed(() =>
    props.rows.data.reduce(
        (sum, row) => sum + Number(row.cantidad || 0),
        0,
    ),
);

const currency = (value: number | string) => {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: 2,
    }).format(Number(value || 0));
};

const formatNumber = (
    value: number | string,
    fractionDigits = 0,
) => {
    const numberValue = Number(value);

    if (Number.isNaN(numberValue)) {
        return String(value);
    }

    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: fractionDigits,
        maximumFractionDigits: fractionDigits,
    }).format(numberValue);
};

const formatDateTime = (value: string | null) => {
    if (!value) {
        return '—';
    }

    return new Date(
        value.replace(' ', 'T'),
    ).toLocaleString('es-PE');
};
</script>
<template>
    <Head title="Reporte de Salidas" />

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
                           sm:flex-row sm:items-center
                           sm:justify-between"
                >
                    <div>
                        <h1
                            class="text-xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            Reporte de Salidas
                        </h1>

                        <p
                            class="mt-1 text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Detalle de productos registrados como salida.
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
                           md:grid-cols-3
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
                            v-model="form.from"
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
                            v-model="form.to"
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
                            v-model="form.warehouse_id"
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
                                :value="
                                    String(
                                        warehouse.id,
                                    )
                                "
                            >
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
                            v-model="form.responsible_id"
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
                                v-for="responsible in props.responsibles"
                                :key="responsible.id"
                                :value="
                                    String(
                                        responsible.id,
                                    )
                                "
                            >
                                {{ responsible.name }}
                            </option>
                        </select>
                    </div>

                    <!-- CLIENTE -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Cliente
                        </label>

                        <select
                            v-model="form.customer_id"
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
                                v-for="customer in props.customers"
                                :key="customer.id"
                                :value="
                                    String(
                                        customer.id,
                                    )
                                "
                            >
                                {{ customer.name }}
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
                            placeholder="Código salida, cliente, código o producto..."
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
                            v-model.number="form.per_page"
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
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                    </div>

                    <!-- BOTONES -->
                    <div
                        class="flex items-end gap-2
                               md:col-span-1"
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
                    </div>
                </form>

                <!-- RESUMEN -->
                <div
                    class="mt-6 grid gap-4
                           sm:grid-cols-2
                           lg:grid-cols-4"
                >
                    <!-- REGISTROS -->
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
                            Registros
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ formatNumber(props.rows.total) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            salidas registradas
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
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ currency(total) }}
                        </p>

                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            página actual
                        </p>
                    </div>

                    <!-- UNIDADES -->
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
                            Unidades
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ formatNumber(totalUnits, 3) }}
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
                <div
                    class="mb-4 flex flex-col gap-2
                           sm:flex-row
                           sm:items-center
                           sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            Detalle de salidas
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
                </div>

                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[1400px]
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
                                    Fecha / hora
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Código salida
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
                                    Cliente
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Responsable
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Código producto
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
                                    Cantidad
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
                                    Precio
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Total
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Motivo
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
                                    `${row.id}-${row.product_id}`
                                "
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ formatDateTime(row.fecha_hora) }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           font-medium
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    {{ row.salida_code }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.almacen }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.cliente }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.responsable }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.codigo_producto }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.producto }}
                                </td>

                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ formatNumber(row.cantidad, 3) }}
                                </td>

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

                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ currency(row.precio) }}
                                </td>

                                <td
                                    class="px-4 py-3 text-right
                                           text-sm font-medium
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    {{ currency(row.total) }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.motivo || '—' }}
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    props.rows.data.length ===
                                    0
                                "
                            >
                                <td
                                    colspan="12"
                                    class="px-4 py-8 text-center
                                           text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay salidas para los filtros
                                    seleccionados.
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