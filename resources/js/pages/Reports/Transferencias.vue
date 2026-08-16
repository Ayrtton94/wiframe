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
        <div class="space-y-6 p-4">

            <!-- ENCABEZADO -->
            <section class="rounded-xl border bg-white p-4">

                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h1 class="text-xl font-semibold">
                            Reporte de Transferencias
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Histórico de movimientos entre almacenes.
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

                    <!-- ORIGEN -->
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Almacén origen
                        </label>

                        <select
                            v-model="filters.from_warehouse_id"
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

                    <!-- DESTINO -->
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Almacén destino
                        </label>

                        <select
                            v-model="filters.to_warehouse_id"
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

                    <!-- ESTADO -->
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Estado
                        </label>

                        <select
                            v-model="filters.status"
                            class="w-full rounded border px-3 py-2"
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
                    <div class="flex gap-2">
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
                            Limpiar
                        </Button>
                    </div>
                </form>

                <!-- RESUMEN -->
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                    <div
                        class="rounded-2xl border border-gray-200 bg-slate-50 p-4"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Transferencias
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
                            Metros solicitados
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ number(totalSolicitados, 3) }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-gray-200 bg-orange-50 p-4"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Metros despachados
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ number(totalDespachados, 3) }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-gray-200 bg-emerald-50 p-4"
                    >
                        <p class="text-sm font-medium text-gray-600">
                            Metros recibidos
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ number(totalRecibidos, 3) }}
                        </p>
                    </div>

                </div>
            </section>

            <!-- TABLA -->
            <section class="rounded-xl border bg-white p-4">

                <div class="mb-4">
                    <h2 class="text-lg font-semibold">
                        Histórico de transferencias
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
                        class="w-full min-w-[1500px] divide-y divide-gray-200"
                    >

                        <thead class="bg-gray-50">
                            <tr>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Fecha solicitud
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Código
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Origen
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Destino
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Producto
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Rollos solicitados
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Rollos despachados
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Rollos recibidos
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Metros solicitados
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Metros despachados
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase">
                                    Metros recibidos
                                </th>

                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase">
                                    Estado
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Despacho
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase">
                                    Recepción
                                </th>

                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">

                            <tr
                                v-for="row in props.rows.data"
                                :key="`${row.id}-${row.codigo_producto}`"
                            >

                                <td class="px-4 py-3 text-sm">
                                    {{ formatDate(row.fecha_solicitud) }}
                                </td>

                                <td class="px-4 py-3 text-sm font-medium">
                                    {{ row.transferencia_code }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ row.almacen_origen }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ row.almacen_destino }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium">
                                        {{ row.codigo_producto }}
                                    </div>

                                    <div class="text-gray-500">
                                        {{ row.producto }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-right text-sm">
                                    {{ number(row.rollos_solicitados, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm">
                                    {{ number(row.rollos_despachados, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm">
                                    {{ number(row.rollos_recibidos, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm">
                                    {{ number(row.metros_solicitados, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm">
                                    {{ number(row.metros_despachados, 3) }}
                                </td>

                                <td class="px-4 py-3 text-right text-sm">
                                    {{ number(row.metros_recibidos, 3) }}
                                </td>

                                <!-- ESTADO -->
                                <td class="px-4 py-3 text-center">

                                    <span
                                        v-if="row.status === 'requested'"
                                        class="inline-flex rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-700"
                                    >
                                        {{ statusLabel(row.status) }}
                                    </span>

                                    <span
                                        v-else-if="row.status === 'shipped'"
                                        class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700"
                                    >
                                        {{ statusLabel(row.status) }}
                                    </span>

                                    <span
                                        v-else-if="row.status === 'received'"
                                        class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700"
                                    >
                                        {{ statusLabel(row.status) }}
                                    </span>

                                    <span
                                        v-else-if="row.status === 'cancelled'"
                                        class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700"
                                    >
                                        {{ statusLabel(row.status) }}
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700"
                                    >
                                        {{ statusLabel(row.status) }}
                                    </span>

                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ formatDate(row.fecha_despacho) }}
                                </td>

                                <td class="px-4 py-3 text-sm">
                                    {{ formatDate(row.fecha_recepcion) }}
                                </td>

                            </tr>

                            <tr
                                v-if="props.rows.data.length === 0"
                            >
                                <td
                                    colspan="14"
                                    class="px-4 py-8 text-center text-sm text-gray-500"
                                >
                                    No hay transferencias para los filtros seleccionados.
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