<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type SaleRow = {
    id: number;
    salida_code: string;
    fecha_hora: string;
    warehouse_id: number;
    almacen: string | null;

    customer_id: number | null;
    cliente: string | null;

    responsible_id: number | null;
    responsable: string | null;

    product_id: number | null;
    codigo_producto: string | null;
    producto: string | null;
    color: string | null;

    cantidad: number | string;
    unidad: string;

    precio: number | string;
    total: number | string;

    motivo: string | null;
};

type GroupedItem = {
    id: string;
    product_id: number | null;
    product_code: string;
    product_name: string;
    color: string | null;
    quantity: number;
    unit: string;
    unit_price: number;
    total: number;
};

type GroupedSale = {
    id: number;
    code: string;
    created_at: string;

    warehouse_id: number;
    warehouse_name: string;

    customer_id: number | null;
    customer_name: string;

    responsible_id: number | null;
    responsible_name: string;

    reason: string;
    total: number;

    items_count: number;
    items: GroupedItem[];
};

const props = defineProps<{
    rows: {
        data: SaleRow[];
        current_page: number;
        last_page: number;
        per_page: number;
        from: number | null;
        to: number | null;
        total: number;
    };

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
        code?: string;
    }>;

    responsibles: Array<{
        id: number;
        name: string;
    }>;

    customers: Array<{
        id: number;
        name: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reportes',
        href: '/reports',
    },
    {
        title: 'Salidas',
        href: '/reports/salidas',
    },
];

const expanded = ref<number | null>(null);

const filters = ref({
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
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

const applyFilters = (page = 1) => {
    router.get(
        '/reports/salidas',
        {
            from: filters.value.from || undefined,
            to: filters.value.to || undefined,
            warehouse_id:
                filters.value.warehouse_id || undefined,
            responsible_id:
                filters.value.responsible_id || undefined,
            customer_id:
                filters.value.customer_id || undefined,
            search: filters.value.search || undefined,
            per_page: filters.value.per_page,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    filters.value = {
        from: '',
        to: '',
        warehouse_id: '',
        responsible_id: '',
        customer_id: '',
        search: '',
        per_page: 25,
    };

    router.get(
        '/reports/salidas',
        {},
        {
            replace: true,
            preserveState: false,
        },
    );
};

const exportExcel = () => {
    const params = new URLSearchParams();

    if (filters.value.from) {
        params.set('from', filters.value.from);
    }

    if (filters.value.to) {
        params.set('to', filters.value.to);
    }

    if (filters.value.warehouse_id) {
        params.set(
            'warehouse_id',
            filters.value.warehouse_id,
        );
    }

    if (filters.value.responsible_id) {
        params.set(
            'responsible_id',
            filters.value.responsible_id,
        );
    }

    if (filters.value.customer_id) {
        params.set(
            'customer_id',
            filters.value.customer_id,
        );
    }

    if (filters.value.search) {
        params.set(
            'search',
            filters.value.search,
        );
    }

    window.location.href =
        `/reports/salidas/export?${params.toString()}`;
};

const money = (value: number | string) => {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
};

const number = (
    value: number | string,
    digits = 2,
) => {
    const numberValue = Number(value);

    if (Number.isNaN(numberValue)) {
        return '0';
    }

    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: digits,
    }).format(numberValue);
};

const dateTime = (value: string) => {
    if (!value) {
        return '-';
    }

    const date = new Date(
        value.replace(' ', 'T'),
    );

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('es-PE', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(date);
};

const groupedRows = computed<GroupedSale[]>(() => {
    const groups = new Map<number, GroupedSale>();

    for (const row of props.rows.data) {
        if (!groups.has(row.id)) {
            groups.set(row.id, {
                id: row.id,
                code: row.salida_code,
                created_at: row.fecha_hora,

                warehouse_id: row.warehouse_id,
                warehouse_name:
                    row.almacen ?? '-',

                customer_id: row.customer_id,
                customer_name:
                    row.cliente ??
                    'CONSUMIDOR FINAL',

                responsible_id:
                    row.responsible_id,
                responsible_name:
                    row.responsable ?? '-',

                reason:
                    row.motivo ??
                    'SALIDA',

                total: 0,

                items_count: 0,
                items: [],
            });
        }

        const sale = groups.get(row.id)!;

        const quantity = Number(
            row.cantidad || 0,
        );

        const unitPrice = Number(
            row.precio || 0,
        );

        const lineTotal = Number(
            row.total || 0,
        );

        sale.items.push({
            id: `${row.id}-${row.product_id}-${sale.items_count}`,
            product_id: row.product_id,
            product_code: row.codigo_producto ?? '-',
            product_name: row.producto ?? '-',
            color: row.color ?? null,
            quantity,
            unit: row.unidad ?? '',
            unit_price: unitPrice,
            total: lineTotal,
        });

        sale.items_count += 1;
        sale.total += lineTotal;
    }

    return Array.from(
        groups.values(),
    );
});

const totalSales = computed(() =>
    groupedRows.value.length,
);

const totalAmount = computed(() =>
    groupedRows.value.reduce(
        (sum, row) =>
            sum + Number(row.total || 0),
        0,
    ),
);

const totalUnits = computed(() =>
    groupedRows.value.reduce(
        (sum, row) =>
            sum +
            row.items.reduce(
                (itemSum, item) =>
                    itemSum +
                    Number(item.quantity || 0),
                0,
            ),
        0,
    ),
);

const toggleDetails = (id: number) => {
    expanded.value =
        expanded.value === id
            ? null
            : id;
};
</script>

<template>
    <Head title="Reporte de Salidas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="space-y-6
                   bg-slate-50 p-4
                   dark:bg-slate-950"
        >
            <!-- ENCABEZADO -->
            <section
                class="rounded-xl
                       border border-slate-200
                       bg-white p-5
                       shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div
                    class="flex flex-col
                           gap-4
                           lg:flex-row
                           lg:items-center
                           lg:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-bold
                                   uppercase tracking-[0.18em]
                                   text-blue-600
                                   dark:text-blue-400"
                        >
                            Consulta y control
                        </p>

                        <h1
                            class="mt-1 text-2xl font-semibold
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
                            Consulta y control de todas
                            las salidas registradas.
                        </p>
                    </div>

                    <div
                        class="flex flex-wrap gap-2"
                    >
                        <button
                            type="button"
                            class="rounded-lg
                                   border border-slate-300
                                   bg-white px-4 py-2
                                   text-sm font-medium
                                   text-slate-700
                                   transition
                                   hover:bg-slate-100
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-200
                                   dark:hover:bg-slate-700"
                            @click="clearFilters"
                        >
                            ↻ Limpiar filtros
                        </button>

                        <button
                            type="button"
                            class="rounded-lg
                                   border border-emerald-300
                                   bg-emerald-50 px-4 py-2
                                   text-sm font-medium
                                   text-emerald-700
                                   transition
                                   hover:bg-emerald-100
                                   dark:border-emerald-500/40
                                   dark:bg-emerald-500/10
                                   dark:text-emerald-400
                                   dark:hover:bg-emerald-500/20"
                            @click="exportExcel"
                        >
                            ⇩ Exportar Excel
                        </button>
                    </div>
                </div>

                <!-- FILTROS -->
                <form
                    class="mt-6 grid gap-4
                           md:grid-cols-2
                           lg:grid-cols-4"
                    @submit.prevent="
                        applyFilters(1)
                    "
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
                                   text-sm
                                   text-slate-900
                                   outline-none
                                   transition
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
                                   text-sm
                                   text-slate-900
                                   outline-none
                                   transition
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
                                   text-sm
                                   text-slate-900
                                   outline-none
                                   transition
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
                                {{
                                    warehouse.code
                                        ? `${warehouse.code} - ${warehouse.name}`
                                        : warehouse.name
                                }}
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
                            v-model="
                                filters.responsible_id
                            "
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-sm
                                   text-slate-900
                                   outline-none
                                   transition
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
                            v-model="filters.customer_id"
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-sm
                                   text-slate-900
                                   outline-none
                                   transition
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

                    <!-- BUSCADOR -->
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
                            v-model="filters.search"
                            type="text"
                            placeholder="Código salida, cliente, código, producto o color..."
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-sm
                                   text-slate-900
                                   placeholder:text-slate-400
                                   outline-none
                                   transition
                                   focus:border-blue-500
                                   focus:ring-2
                                   focus:ring-blue-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-100
                                   dark:placeholder:text-slate-500"
                        />
                    </div>

                    <!-- POR PÁGINA -->
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
                            v-model.number="
                                filters.per_page
                            "
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-sm
                                   text-slate-900
                                   outline-none
                                   transition
                                   focus:border-blue-500
                                   focus:ring-2
                                   focus:ring-blue-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-100"
                        >
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
                        class="flex items-end gap-2"
                    >
                        <button
                            type="submit"
                            class="w-full rounded-lg
                                   bg-blue-600 px-4 py-2
                                   text-sm font-medium
                                   text-white
                                   transition
                                   hover:bg-blue-700
                                   dark:hover:bg-blue-500"
                        >
                            ⌕ Aplicar filtros
                        </button>
                    </div>
                </form>
            </section>

            <!-- TARJETAS -->
            <section
                class="grid gap-4
                       sm:grid-cols-2
                       xl:grid-cols-4"
            >
                <!-- SALIDAS -->
                <div
                    class="rounded-xl
                           border border-blue-200
                           bg-blue-50 p-4
                           dark:border-blue-500/30
                           dark:bg-blue-500/10"
                >
                    <span
                        class="text-sm font-medium
                               text-blue-700
                               dark:text-blue-400"
                    >
                        Total de salidas
                    </span>

                    <strong
                        class="mt-2 block text-3xl
                               font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ totalSales }}
                    </strong>

                    <small
                        class="text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        registros en esta página
                    </small>
                </div>

                <!-- TOTAL -->
                <div
                    class="rounded-xl
                           border border-emerald-200
                           bg-emerald-50 p-4
                           dark:border-emerald-500/30
                           dark:bg-emerald-500/10"
                >
                    <span
                        class="text-sm font-medium
                               text-emerald-700
                               dark:text-emerald-400"
                    >
                        Total de salidas
                    </span>

                    <strong
                        class="mt-2 block text-3xl
                               font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ money(totalAmount) }}
                    </strong>

                    <small
                        class="text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        monto en la página actual
                    </small>
                </div>

                <!-- UNIDADES -->
                <div
                    class="rounded-xl
                           border border-amber-200
                           bg-amber-50 p-4
                           dark:border-amber-500/30
                           dark:bg-amber-500/10"
                >
                    <span
                        class="text-sm font-medium
                               text-amber-700
                               dark:text-amber-400"
                    >
                        Total de unidades
                    </span>

                    <strong
                        class="mt-2 block text-3xl
                               font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ number(totalUnits, 3) }}
                    </strong>

                    <small
                        class="text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        unidades en la página
                    </small>
                </div>

                <!-- PAGINA -->
                <div
                    class="rounded-xl
                           border border-violet-200
                           bg-violet-50 p-4
                           dark:border-violet-500/30
                           dark:bg-violet-500/10"
                >
                    <span
                        class="text-sm font-medium
                               text-violet-700
                               dark:text-violet-400"
                    >
                        Página actual
                    </span>

                    <strong
                        class="mt-2 block text-3xl
                               font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ props.rows.current_page }}
                    </strong>

                    <small
                        class="text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        de {{ props.rows.last_page }}
                    </small>
                </div>
            </section>

            <!-- TABLA -->
            <section
                class="rounded-xl
                       border border-slate-200
                       bg-white shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div class="p-5">
                    <h2
                        class="text-lg font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        Detalle de salidas
                    </h2>

                    <p
                        class="mt-1 text-sm
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
                        class="min-w-[1150px]
                               w-full
                               border-collapse"
                    >
                        <thead
                            class="bg-slate-50
                                   dark:bg-slate-800"
                        >
                            <tr>
                                <th
                                    class="px-4 py-3
                                           text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Fecha / Hora
                                </th>

                                <th
                                    class="px-4 py-3
                                           text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Código salida
                                </th>

                                <th
                                    class="px-4 py-3
                                           text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Almacén
                                </th>

                                <th
                                    class="px-4 py-3
                                           text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Cliente
                                </th>

                                <th
                                    class="px-4 py-3
                                           text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Responsable
                                </th>

                                <th
                                    class="px-4 py-3
                                           text-center
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Ítems
                                </th>

                                <th
                                    class="px-4 py-3
                                           text-right
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Total
                                </th>

                                <th
                                    class="px-4 py-3
                                           text-left
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Motivo
                                </th>

                                <th
                                    class="px-4 py-3
                                           text-center
                                           text-xs font-semibold
                                           uppercase
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y
                                   divide-slate-200
                                   dark:divide-slate-700"
                        >
                            <template
                                v-for="sale in groupedRows"
                                :key="sale.id"
                            >
                                <!-- FILA PRINCIPAL -->
                                <tr
                                    class="transition
                                           hover:bg-slate-50
                                           dark:hover:bg-slate-800/60"
                                >
                                    <td
                                        class="px-4 py-3
                                               text-sm
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{
                                            dateTime(
                                                sale.created_at,
                                            )
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-3
                                               text-sm font-semibold
                                               text-slate-900
                                               dark:text-slate-100"
                                    >
                                        {{ sale.code }}
                                    </td>

                                    <td
                                        class="px-4 py-3
                                               text-sm
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{ sale.warehouse_name }}
                                    </td>

                                    <td
                                        class="px-4 py-3
                                               text-sm
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{ sale.customer_name }}
                                    </td>

                                    <td
                                        class="px-4 py-3
                                               text-sm
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{
                                            sale.responsible_name
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-3
                                               text-center"
                                    >
                                        <span
                                            class="inline-flex
                                                   rounded-full
                                                   bg-slate-100
                                                   px-2.5 py-1
                                                   text-xs font-semibold
                                                   text-slate-700
                                                   dark:bg-slate-800
                                                   dark:text-slate-300"
                                        >
                                            {{ sale.items_count }}
                                            {{
                                                sale.items_count ===
                                                1
                                                    ? 'producto'
                                                    : 'productos'
                                            }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-4 py-3
                                               text-right
                                               text-sm font-bold
                                               text-slate-900
                                               dark:text-slate-100"
                                    >
                                        {{ money(sale.total) }}
                                    </td>

                                    <td
                                        class="px-4 py-3"
                                    >
                                        <span
                                            class="inline-flex
                                                   rounded-full
                                                   bg-emerald-50
                                                   px-2.5 py-1
                                                   text-xs font-semibold
                                                   text-emerald-700
                                                   dark:bg-emerald-500/10
                                                   dark:text-emerald-400"
                                        >
                                            {{
                                                sale.reason
                                            }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-4 py-3
                                               text-center"
                                    >
                                        <button
                                            type="button"
                                            class="rounded-lg
                                                   border
                                                   border-blue-300
                                                   bg-blue-50
                                                   px-3 py-1.5
                                                   text-xs font-medium
                                                   text-blue-700
                                                   transition
                                                   hover:bg-blue-100
                                                   dark:border-blue-500/40
                                                   dark:bg-blue-500/10
                                                   dark:text-blue-400
                                                   dark:hover:bg-blue-500/20"
                                            @click="
                                                toggleDetails(
                                                    sale.id,
                                                )
                                            "
                                        >
                                            {{
                                                expanded ===
                                                sale.id
                                                    ? 'Ocultar'
                                                    : 'Ver detalle'
                                            }}
                                        </button>
                                    </td>
                                </tr>

                                <!-- DETALLE -->
                                <tr
                                    v-if="
                                        expanded ===
                                        sale.id
                                    "
                                >
                                    <td
                                        colspan="9"
                                        class="bg-slate-50
                                               px-4 py-4
                                               dark:bg-slate-950"
                                    >
                                        <div
                                            class="rounded-xl
                                                   border
                                                   border-slate-200
                                                   bg-white
                                                   p-4
                                                   dark:border-slate-700
                                                   dark:bg-slate-900"
                                        >
                                            <div
                                                class="flex
                                                       flex-col
                                                       gap-3
                                                       lg:flex-row
                                                       lg:items-center
                                                       lg:justify-between"
                                            >
                                                <div>
                                                    <p
                                                        class="text-xs
                                                               font-semibold
                                                               uppercase
                                                               text-blue-600
                                                               dark:text-blue-400"
                                                    >
                                                        Detalle de salida
                                                    </p>

                                                    <h3
                                                        class="mt-1
                                                               text-base
                                                               font-semibold
                                                               text-slate-900
                                                               dark:text-slate-100"
                                                    >
                                                        {{
                                                            sale.code
                                                        }}
                                                    </h3>
                                                </div>

                                                <div
                                                    class="flex flex-wrap
                                                           gap-2 text-xs
                                                           text-slate-500
                                                           dark:text-slate-400"
                                                >
                                                    <span>
                                                        Fecha:
                                                        {{
                                                            dateTime(
                                                                sale.created_at,
                                                            )
                                                        }}
                                                    </span>

                                                    <span>
                                                        Almacén:
                                                        {{
                                                            sale.warehouse_name
                                                        }}
                                                    </span>

                                                    <span>
                                                        Cliente:
                                                        {{
                                                            sale.customer_name
                                                        }}
                                                    </span>

                                                    <span>
                                                        Responsable:
                                                        {{
                                                            sale.responsible_name
                                                        }}
                                                    </span>

                                                    <span>
                                                        Motivo:
                                                        {{
                                                            sale.reason
                                                        }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div
                                                class="mt-4
                                                       overflow-x-auto"
                                            >
                                                <table
                                                    class="min-w-full
                                                           border-collapse"
                                                >
                                                    <thead
                                                        class="bg-slate-50
                                                               dark:bg-slate-800"
                                                    >
                                                        <tr>
                                                            <th
                                                                class="px-3 py-2
                                                                       text-left
                                                                       text-xs
                                                                       font-semibold
                                                                       uppercase
                                                                       text-slate-600
                                                                       dark:text-slate-300"
                                                            >
                                                                Código producto
                                                            </th>

                                                            <th
                                                                class="px-3 py-2
                                                                       text-left
                                                                       text-xs
                                                                       font-semibold
                                                                       uppercase
                                                                       text-slate-600
                                                                       dark:text-slate-300"
                                                            >
                                                                Producto
                                                            </th>

                                                            <th
                                                                class="px-3 py-2
                                                                       text-left
                                                                       text-xs
                                                                       font-semibold
                                                                       uppercase
                                                                       text-slate-600
                                                                       dark:text-slate-300"
                                                            >
                                                                Color
                                                            </th>

                                                            <th
                                                                class="px-3 py-2
                                                                       text-right
                                                                       text-xs
                                                                       font-semibold
                                                                       uppercase
                                                                       text-slate-600
                                                                       dark:text-slate-300"
                                                            >
                                                                Cantidad
                                                            </th>

                                                            <th
                                                                class="px-3 py-2
                                                                       text-left
                                                                       text-xs
                                                                       font-semibold
                                                                       uppercase
                                                                       text-slate-600
                                                                       dark:text-slate-300"
                                                            >
                                                                Unidad
                                                            </th>

                                                            <th
                                                                class="px-3 py-2
                                                                       text-right
                                                                       text-xs
                                                                       font-semibold
                                                                       uppercase
                                                                       text-slate-600
                                                                       dark:text-slate-300"
                                                            >
                                                                Precio unitario
                                                            </th>

                                                            <th
                                                                class="px-3 py-2
                                                                       text-right
                                                                       text-xs
                                                                       font-semibold
                                                                       uppercase
                                                                       text-slate-600
                                                                       dark:text-slate-300"
                                                            >
                                                                Subtotal
                                                            </th>
                                                        </tr>
                                                    </thead>

                                                    <tbody
                                                        class="divide-y
                                                               divide-slate-200
                                                               dark:divide-slate-700"
                                                    >
                                                        <tr
                                                            v-for="item in sale.items"
                                                            :key="item.id"
                                                        >
                                                            <td
                                                                class="px-3 py-2
                                                                       text-sm
                                                                       font-medium
                                                                       text-slate-900
                                                                       dark:text-slate-100"
                                                            >
                                                                {{
                                                                    item.product_code
                                                                }}
                                                            </td>

                                                            <td
                                                                class="px-3 py-2
                                                                       text-sm
                                                                       text-slate-700
                                                                       dark:text-slate-300"
                                                            >
                                                                {{
                                                                    item.product_name
                                                                }}
                                                            </td>

                                                            <td
                                                                class="px-3 py-2
                                                                       text-sm
                                                                       text-slate-700
                                                                       dark:text-slate-300"
                                                            >
                                                                {{ item.color || '—' }}
                                                            </td>

                                                            <td
                                                                class="px-3 py-2
                                                                       text-right
                                                                       text-sm
                                                                       text-slate-700
                                                                       dark:text-slate-300"
                                                            >
                                                                {{
                                                                    number(
                                                                        item.quantity,
                                                                        String(
                                                                            item.unit,
                                                                        ).toLowerCase() ===
                                                                            'kilos'
                                                                            ? 0
                                                                            : 3,
                                                                    )
                                                                }}
                                                            </td>

                                                            <td
                                                                class="px-3 py-2
                                                                       text-sm
                                                                       text-slate-700
                                                                       dark:text-slate-300"
                                                            >
                                                                {{
                                                                    String(
                                                                        item.unit ||
                                                                            '',
                                                                    ).toLowerCase() ===
                                                                    'kilos'
                                                                        ? 'Rollos'
                                                                        : 'Metros'
                                                                }}
                                                            </td>

                                                            <td
                                                                class="px-3 py-2
                                                                       text-right
                                                                       text-sm
                                                                       text-slate-700
                                                                       dark:text-slate-300"
                                                            >
                                                                {{
                                                                    money(
                                                                        item.unit_price,
                                                                    )
                                                                }}
                                                            </td>

                                                            <td
                                                                class="px-3 py-2
                                                                       text-right
                                                                       text-sm
                                                                       font-semibold
                                                                       text-slate-900
                                                                       dark:text-slate-100"
                                                            >
                                                                {{
                                                                    money(
                                                                        item.total,
                                                                    )
                                                                }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div
                                                class="mt-4 flex
                                                       justify-end
                                                       border-t
                                                       border-slate-200
                                                       pt-3
                                                       dark:border-slate-700"
                                            >
                                                <div
                                                    class="flex
                                                           items-center
                                                           gap-5"
                                                >
                                                    <span
                                                        class="text-sm
                                                               text-slate-500
                                                               dark:text-slate-400"
                                                    >
                                                        TOTAL DE LA SALIDA
                                                    </span>

                                                    <strong
                                                        class="text-lg
                                                               text-slate-900
                                                               dark:text-slate-100"
                                                    >
                                                        {{
                                                            money(
                                                                sale.total,
                                                            )
                                                        }}
                                                    </strong>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <!-- SIN RESULTADOS -->
                            <tr
                                v-if="
                                    groupedRows.length ===
                                    0
                                "
                            >
                                <td
                                    colspan="9"
                                    class="px-4 py-10
                                           text-center
                                           text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No se encontraron
                                    salidas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINACIÓN -->
                <div
                    v-if="
                        props.rows.last_page > 1
                    "
                    class="flex flex-col
                           items-center
                           justify-between
                           gap-3
                           border-t
                           border-slate-200
                           p-4
                           dark:border-slate-700
                           sm:flex-row"
                >
                    <span
                        class="text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Página
                        {{ props.rows.current_page }}
                        de
                        {{ props.rows.last_page }}
                    </span>

                    <div
                        class="flex items-center
                               gap-2"
                    >
                        <button
                            type="button"
                            :disabled="
                                props.rows.current_page <= 1
                            "
                            class="rounded-lg
                                   border
                                   border-slate-300
                                   bg-white
                                   px-3 py-2
                                   text-sm
                                   text-slate-700
                                   transition
                                   hover:bg-slate-100
                                   disabled:cursor-not-allowed
                                   disabled:opacity-40
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-200
                                   dark:hover:bg-slate-700"
                            @click="
                                applyFilters(
                                    props.rows.current_page -
                                        1,
                                )
                            "
                        >
                            ‹
                        </button>

                        <span
                            class="rounded-lg
                                   bg-blue-600
                                   px-3 py-2
                                   text-sm
                                   font-medium
                                   text-white"
                        >
                            {{
                                props.rows.current_page
                            }}
                        </span>

                        <button
                            type="button"
                            :disabled="
                                props.rows.current_page >=
                                props.rows.last_page
                            "
                            class="rounded-lg
                                   border
                                   border-slate-300
                                   bg-white
                                   px-3 py-2
                                   text-sm
                                   text-slate-700
                                   transition
                                   hover:bg-slate-100
                                   disabled:cursor-not-allowed
                                   disabled:opacity-40
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-200
                                   dark:hover:bg-slate-700"
                            @click="
                                applyFilters(
                                    props.rows.current_page +
                                        1,
                                )
                            "
                        >
                            ›
                        </button>
                    </div>
                </div>
            </section>

            <p
                class="text-xs
                       text-slate-500
                       dark:text-slate-500"
            >
                Los montos mostrados corresponden a las
                salidas registradas. El reporte utiliza la
                fecha de registro de la salida.
            </p>
        </div>
    </AppLayout>
</template>