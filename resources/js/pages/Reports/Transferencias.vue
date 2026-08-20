<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reportes', href: '/reports' },
    {
        title: 'Transferencias',
        href: '/reports/transferencias',
    },
];

const props = defineProps<{
    rows: {
        data: Array<{
            id: number;
            transferencia_code: string;
            fecha_solicitud: string | null;

            almacen_origen: string;
            almacen_destino: string;

            codigo_producto: string;
            producto: string;

            rollos_solicitados: number | string;
            rollos_despachados: number | string;
            rollos_recibidos: number | string;

            metros_solicitados: number | string;
            metros_despachados: number | string;
            metros_recibidos: number | string;

            status: string;

            fecha_despacho: string | null;
            fecha_recepcion: string | null;
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
        from_warehouse_id: number | string | null;
        to_warehouse_id: number | string | null;
        status: string;
        search: string;
        per_page: number;
    };

    warehouses: Array<{
        id: number;
        name: string;
    }>;
}>();

const filters = {
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',

    from_warehouse_id: props.filters.from_warehouse_id
        ? String(props.filters.from_warehouse_id)
        : '',

    to_warehouse_id: props.filters.to_warehouse_id
        ? String(props.filters.to_warehouse_id)
        : '',

    status: props.filters.status ?? '',

    search: props.filters.search ?? '',

    per_page: props.filters.per_page ?? 25,
};

const totalSolicitados = computed(() =>
    props.rows.data.reduce(
        (sum, row) => sum + Number(row.metros_solicitados || 0),
        0,
    ),
);

const totalDespachados = computed(() =>
    props.rows.data.reduce(
        (sum, row) => sum + Number(row.metros_despachados || 0),
        0,
    ),
);

const totalRecibidos = computed(() =>
    props.rows.data.reduce(
        (sum, row) => sum + Number(row.metros_recibidos || 0),
        0,
    ),
);

const formatDate = (value: string | null) => {
    if (!value) {
        return '—';
    }

    return new Date(
        value.replace(' ', 'T'),
    ).toLocaleString('es-PE');
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

const statusLabel = (status: string) => {
    const labels: Record<string, string> = {
        requested: 'Solicitada',
        shipped: 'Despachada',
        received: 'Recibida',
        cancelled: 'Cancelada',
    };

    return labels[status] ?? status;
};

const applyFilters = () => {
    router.get(
        '/reports/transferencias',
        {
            from: filters.from || undefined,
            to: filters.to || undefined,

            from_warehouse_id:
                filters.from_warehouse_id || undefined,

            to_warehouse_id:
                filters.to_warehouse_id || undefined,

            status: filters.status || undefined,
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
        '/reports/transferencias',
        {},
        {
            replace: true,
        },
    );
};

const exportExcel = () => {
    const params = new URLSearchParams({
        from: filters.from || '',
        to: filters.to || '',
        from_warehouse_id:
            filters.from_warehouse_id
                ? String(filters.from_warehouse_id)
                : '',
        to_warehouse_id:
            filters.to_warehouse_id
                ? String(filters.to_warehouse_id)
                : '',
        status: filters.status || '',
        search: filters.search || '',
    });

    window.location.href =
        `/reports/transferencias/export?${params.toString()}`;
};

const changePage = (page: number) => {
    if (
        page < 1 ||
        page > props.rows.last_page
    ) {
        return;
    }

    router.get(
        '/reports/transferencias',
        {
            from: filters.from || undefined,
            to: filters.to || undefined,

            from_warehouse_id:
                filters.from_warehouse_id || undefined,

            to_warehouse_id:
                filters.to_warehouse_id || undefined,

            status: filters.status || undefined,
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
    <Head title="Reporte de Transferencias" />

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
                            Reporte de Transferencias
                        </h1>

                        <p
                            class="mt-1 text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Histórico de movimientos entre almacenes.
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

                    <!-- ORIGEN -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Almacén origen
                        </label>

                        <select
                            v-model="filters.from_warehouse_id"
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

                    <!-- DESTINO -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Almacén destino
                        </label>

                        <select
                            v-model="filters.to_warehouse_id"
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

                    <!-- ESTADO -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Estado
                        </label>

                        <select
                            v-model="filters.status"
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
                                Todos los estados
                            </option>

                            <option value="requested">
                                Solicitada
                            </option>

                            <option value="shipped">
                                Despachada
                            </option>

                            <option value="received">
                                Recibida
                            </option>

                            <option value="cancelled">
                                Cancelada
                            </option>
                        </select>
                    </div>

                    <!-- BUSCAR -->
                    <div
                        class="lg:col-span-2"
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
                    <!-- TRANSFERENCIAS -->
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
                            Transferencias
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

                    <!-- METROS SOLICITADOS -->
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
                            Metros solicitados
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ number(totalSolicitados, 3) }}
                        </p>
                    </div>

                    <!-- METROS DESPACHADOS -->
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
                            Metros despachados
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ number(totalDespachados, 3) }}
                        </p>
                    </div>

                    <!-- METROS RECIBIDOS -->
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
                            Metros recibidos
                        </p>

                        <p
                            class="mt-2 text-3xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ number(totalRecibidos, 3) }}
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
                        Histórico de transferencias
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
                        class="w-full min-w-[1500px]
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
                                    Fecha solicitud
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
                                    Origen
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Destino
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
                                    Rollos solicitados
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Rollos despachados
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Rollos recibidos
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Metros solicitados
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Metros despachados
                                </th>

                                <th
                                    class="px-4 py-3 text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Metros recibidos
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

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Despacho
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Recepción
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
                                    `${row.id}-${row.codigo_producto}`
                                "
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
                                <!-- FECHA -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ formatDate(row.fecha_solicitud) }}
                                </td>

                                <!-- CÓDIGO -->
                                <td
                                    class="px-4 py-3 text-sm
                                           font-medium
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    {{ row.transferencia_code }}
                                </td>

                                <!-- ORIGEN -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.almacen_origen }}
                                </td>

                                <!-- DESTINO -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ row.almacen_destino }}
                                </td>

                                <!-- PRODUCTO -->
                                <td
                                    class="px-4 py-3 text-sm"
                                >
                                    <div
                                        class="font-medium
                                               text-slate-900
                                               dark:text-slate-100"
                                    >
                                        {{ row.codigo_producto }}
                                    </div>

                                    <div
                                        class="text-slate-500
                                               dark:text-slate-400"
                                    >
                                        {{ row.producto }}
                                    </div>
                                </td>

                                <!-- ROLLOS SOLICITADOS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ number(row.rollos_solicitados, 3) }}
                                </td>

                                <!-- ROLLOS DESPACHADOS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ number(row.rollos_despachados, 3) }}
                                </td>

                                <!-- ROLLOS RECIBIDOS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ number(row.rollos_recibidos, 3) }}
                                </td>

                                <!-- METROS SOLICITADOS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ number(row.metros_solicitados, 3) }}
                                </td>

                                <!-- METROS DESPACHADOS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ number(row.metros_despachados, 3) }}
                                </td>

                                <!-- METROS RECIBIDOS -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ number(row.metros_recibidos, 3) }}
                                </td>

                                <!-- ESTADO -->
                                <td
                                    class="px-4 py-3 text-center"
                                >
                                    <span
                                        v-if="
                                            row.status ===
                                            'requested'
                                        "
                                        class="inline-flex
                                               rounded-full
                                               bg-yellow-100
                                               px-2.5 py-1
                                               text-xs font-medium
                                               text-yellow-700
                                               dark:bg-yellow-500/15
                                               dark:text-yellow-400"
                                    >
                                        {{ statusLabel(row.status) }}
                                    </span>

                                    <span
                                        v-else-if="
                                            row.status ===
                                            'shipped'
                                        "
                                        class="inline-flex
                                               rounded-full
                                               bg-blue-100
                                               px-2.5 py-1
                                               text-xs font-medium
                                               text-blue-700
                                               dark:bg-blue-500/15
                                               dark:text-blue-400"
                                    >
                                        {{ statusLabel(row.status) }}
                                    </span>

                                    <span
                                        v-else-if="
                                            row.status ===
                                            'received'
                                        "
                                        class="inline-flex
                                               rounded-full
                                               bg-green-100
                                               px-2.5 py-1
                                               text-xs font-medium
                                               text-green-700
                                               dark:bg-green-500/15
                                               dark:text-green-400"
                                    >
                                        {{ statusLabel(row.status) }}
                                    </span>

                                    <span
                                        v-else-if="
                                            row.status ===
                                            'cancelled'
                                        "
                                        class="inline-flex
                                               rounded-full
                                               bg-red-100
                                               px-2.5 py-1
                                               text-xs font-medium
                                               text-red-700
                                               dark:bg-red-500/15
                                               dark:text-red-400"
                                    >
                                        {{ statusLabel(row.status) }}
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex
                                               rounded-full
                                               bg-slate-100
                                               px-2.5 py-1
                                               text-xs font-medium
                                               text-slate-700
                                               dark:bg-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{ statusLabel(row.status) }}
                                    </span>
                                </td>

                                <!-- DESPACHO -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ formatDate(row.fecha_despacho) }}
                                </td>

                                <!-- RECEPCIÓN -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ formatDate(row.fecha_recepcion) }}
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
                                    colspan="14"
                                    class="px-4 py-8
                                           text-center text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay transferencias para los
                                    filtros seleccionados.
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