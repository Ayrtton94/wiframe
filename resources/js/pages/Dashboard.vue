<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface DashboardStats {
    // Datos existentes
    sales_count: number;
    total_revenue: number;
    average_ticket: number;
    products_count: number;

    // Nuevos indicadores
    transfers_count?: number;
    customers_count?: number;
    suppliers_count?: number;

    // Inventario
    total_rolls?: number;
    total_kilos?: number;
    total_meters?: number;
}

interface TopProduct {
    store_id: number;
    code_product: string;
    name_product: string;
    total_quantity: number;
    total_amount: number;
}

interface SalesTrend {
    date: string;
    day: number;
    total: number;
}

interface Transfer {
    id: number;
    code: string;
    date: string;
    origin: string;
    destination: string;
    products_count: number;
    status: string;
}

interface AlertItem {
    type: 'critical' | 'warning' | 'info';
    title: string;
    description: string;
    count: number;
    action_url?: string;
}

interface Warehouse {
    id: number;
    name: string;
}

const props = defineProps<{
    stats: DashboardStats;

    top_products?: TopProduct[];

    month_sales_trend?: SalesTrend[];

    transfers?: Transfer[];

    warehouses?: Warehouse[];

    alerts?: AlertItem[];
}>();

/*
|--------------------------------------------------------------------------
| Filtros
|--------------------------------------------------------------------------
*/

const selectedWarehouse = ref('all');
const selectedPeriod = ref('month');

/*
|--------------------------------------------------------------------------
| Valores seguros
|--------------------------------------------------------------------------
*/

const topProducts = computed(() => props.top_products ?? []);

const transfers = computed(() => props.transfers ?? []);

const warehouses = computed(() => props.warehouses ?? []);

const alerts = computed(() => props.alerts ?? []);

const salesTrend = computed(() => props.month_sales_trend ?? []);

/*
|--------------------------------------------------------------------------
| Formateadores
|--------------------------------------------------------------------------
*/

const formatNumber = (value: number | undefined | null) => {
    return new Intl.NumberFormat('es-PE').format(Number(value ?? 0));
};

/*
|--------------------------------------------------------------------------
| Productos
|--------------------------------------------------------------------------
*/

const maxProductQuantity = computed(() => {
    if (!topProducts.value.length) {
        return 1;
    }

    return Math.max(
        ...topProducts.value.map((product) =>
            Number(product.total_quantity ?? 0),
        ),
        1,
    );
});

const productBarWidth = (quantity: number) => {
    return `${Math.max(
        (Number(quantity ?? 0) / maxProductQuantity.value) * 100,
        3,
    )}%`;
};

/*
|--------------------------------------------------------------------------
| Gráfico de salidas
|--------------------------------------------------------------------------
*/

const chart = computed(() => {
    const data = salesTrend.value;

    if (!data.length) {
        return {
            line: '',
            area: '',
            points: [],
        };
    }

    const width = 900;
    const height = 280;

    const paddingLeft = 30;
    const paddingRight = 20;
    const paddingTop = 25;
    const paddingBottom = 30;

    const chartWidth = width - paddingLeft - paddingRight;
    const chartHeight = height - paddingTop - paddingBottom;

    const maxValue = Math.max(
        ...data.map((item) => Number(item.total ?? 0)),
        1,
    );

    const points = data.map((item, index) => {
        const x =
            data.length === 1
                ? width / 2
                : paddingLeft +
                  (index / (data.length - 1)) * chartWidth;

        const y =
            paddingTop +
            chartHeight -
            (Number(item.total ?? 0) / maxValue) * chartHeight;

        return {
            x,
            y,
            value: Number(item.total ?? 0),
            date: item.date,
        };
    });

    const line = points
        .map((point) => `${point.x},${point.y}`)
        .join(' ');

    const area = [
        `${paddingLeft},${height - paddingBottom}`,
        ...points.map((point) => `${point.x},${point.y}`),
        `${width - paddingRight},${height - paddingBottom}`,
    ].join(' ');

    return {
        line,
        area,
        points,
    };
});

/*
|--------------------------------------------------------------------------
| Transferencias
|--------------------------------------------------------------------------
*/

const transferStatusClass = (status: string) => {
    const value = status.toLowerCase();

    if (
        value.includes('complet') ||
        value.includes('recibid')
    ) {
        return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300';
    }

    if (
        value.includes('tránsito') ||
        value.includes('transito')
    ) {
        return 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300';
    }

    if (
        value.includes('pendiente') ||
        value.includes('espera')
    ) {
        return 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300';
    }

    return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300';
};

/*
|--------------------------------------------------------------------------
| Alertas
|--------------------------------------------------------------------------
*/

const alertClasses = (type: AlertItem['type']) => {
    const classes = {
        critical:
            'border-red-100 bg-red-50 dark:border-red-950/40 dark:bg-red-950/20',
        warning:
            'border-amber-100 bg-amber-50 dark:border-amber-950/40 dark:bg-amber-950/20',
        info:
            'border-blue-100 bg-blue-50 dark:border-blue-950/40 dark:bg-blue-950/20',
    };

    return classes[type];
};

const alertIconClasses = (type: AlertItem['type']) => {
    const classes = {
        critical:
            'bg-red-100 text-red-600 dark:bg-red-950/50 dark:text-red-300',
        warning:
            'bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-300',
        info:
            'bg-blue-100 text-blue-600 dark:bg-blue-950/50 dark:text-blue-300',
    };

    return classes[type];
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout
        :breadcrumbs="[
            {
                title: 'Dashboard',
                href: '#',
            },
        ]"
    >
        <div
            class="min-h-full bg-slate-50 p-4 dark:bg-slate-950 sm:p-6"
        >
            <div class="mx-auto max-w-[1800px] space-y-6">

                <!-- =====================================================
                     HEADER
                ====================================================== -->

                <section
                    class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div>
                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            Dashboard
                        </h1>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Resumen general del sistema
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2" hidden>

                        <!-- ALMACÉN -->

                        <select
                            v-model="selectedWarehouse"
                            class="min-w-[190px] rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
                        >
                            <option value="all">
                                Todos los almacenes
                            </option>

                            <option
                                v-for="warehouse in warehouses"
                                :key="warehouse.id"
                                :value="warehouse.id"
                            >
                                {{ warehouse.name }}
                            </option>
                        </select>

                        <!-- PERIODO -->

                        <select
                            v-model="selectedPeriod"
                            class="min-w-[160px] rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
                        >
                            <option value="day">
                                Hoy
                            </option>

                            <option value="week">
                                Esta semana
                            </option>

                            <option value="month">
                                Este mes
                            </option>

                            <option value="year">
                                Este año
                            </option>
                        </select>

                        <!-- ACTUALIZAR -->

                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M20 11a8 8 0 0 0-15.5-3"
                                />

                                <path
                                    d="M4 5v4h4"
                                />

                                <path
                                    d="M4 13a8 8 0 0 0 15.5 3"
                                />

                                <path
                                    d="M20 19v-4h-4"
                                />
                            </svg>

                            Actualizar
                        </button>
                    </div>
                </section>

                <!-- =====================================================
                     KPIs
                ====================================================== -->

                <section
                    class="grid grid-cols-2 gap-4 xl:grid-cols-5"
                >

                    <!-- SALIDAS -->

                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Salidas
                                </p>

                                <p
                                    class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{ formatNumber(stats.sales_count) }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Este mes
                                </p>
                            </div>

                            <div
                                class="rounded-xl bg-blue-50 p-3 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300"
                            >
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M12 3v12" />
                                    <path d="m8 11 4 4 4-4" />
                                    <path
                                        d="M5 18h14a2 2 0 0 1 2 2v1H3v-1a2 2 0 0 1 2-2Z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </article>

                    <!-- TRANSFERENCIAS -->

                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Transferencias
                                </p>

                                <p
                                    class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatNumber(
                                            stats.transfers_count,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Este mes
                                </p>
                            </div>

                            <div
                                class="rounded-xl bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300"
                            >
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M7 7h13l-3-3" />
                                    <path d="M17 17H4l3 3" />
                                    <path d="M20 7l-3 3" />
                                    <path d="M4 17l3-3" />
                                </svg>
                            </div>
                        </div>
                    </article>

                    <!-- PRODUCTOS -->

                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Productos activos
                                </p>

                                <p
                                    class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatNumber(
                                            stats.products_count,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Total registrados
                                </p>
                            </div>

                            <div
                                class="rounded-xl bg-violet-50 p-3 text-violet-600 dark:bg-violet-950/40 dark:text-violet-300"
                            >
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"
                                    />

                                    <path
                                        d="m4 7.5 8 4.5 8-4.5"
                                    />

                                    <path d="M12 12v9" />
                                </svg>
                            </div>
                        </div>
                    </article>

                    <!-- CLIENTES -->

                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Clientes
                                </p>

                                <p
                                    class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatNumber(
                                            stats.customers_count,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Total registrados
                                </p>
                            </div>

                            <div
                                class="rounded-xl bg-amber-50 p-3 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300"
                            >
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle
                                        cx="9"
                                        cy="7"
                                        r="4"
                                    />

                                    <path
                                        d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"
                                    />

                                    <path
                                        d="M16 3.2a4 4 0 0 1 0 7.6"
                                    />

                                    <path
                                        d="M18 15a4 4 0 0 1 4 4v2"
                                    />
                                </svg>
                            </div>
                        </div>
                    </article>

                    <!-- PROVEEDORES -->

                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Proveedores
                                </p>

                                <p
                                    class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatNumber(
                                            stats.suppliers_count,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Total registrados
                                </p>
                            </div>

                            <div
                                class="rounded-xl bg-rose-50 p-3 text-rose-600 dark:bg-rose-950/40 dark:text-rose-300"
                            >
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        d="M3 7h11v10H3z"
                                    />

                                    <path
                                        d="M14 10h4l3 3v4h-7z"
                                    />

                                    <circle
                                        cx="7"
                                        cy="19"
                                        r="2"
                                    />

                                    <circle
                                        cx="18"
                                        cy="19"
                                        r="2"
                                    />
                                </svg>
                            </div>
                        </div>
                    </article>
                </section>

                <!-- =====================================================
                     TRANSFERENCIAS / TOP PRODUCTOS
                ====================================================== -->

                <section
                    class="grid gap-6 lg:grid-cols-2"
                >

                    <!-- HISTÓRICO TRANSFERENCIAS -->

                    <article
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800"
                        >
                            <div>
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Histórico de transferencias
                                </h2>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Movimiento entre almacenes
                                </p>
                            </div>

                            <Link
                                href="#"
                                class="text-sm font-semibold text-violet-600 hover:text-violet-700"
                            >
                                Ver todas
                            </Link>
                        </div>

                        <div
                            v-if="!transfers.length"
                            class="p-8 text-center text-sm text-slate-500"
                        >
                            No hay transferencias registradas.
                        </div>

                        <div
                            v-else
                            class="overflow-x-auto"
                        >
                            <table class="w-full text-left text-sm">
                                <thead
                                    class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800/50"
                                >
                                    <tr>
                                        <th class="px-5 py-3">
                                            Fecha
                                        </th>

                                        <th class="px-5 py-3">
                                            Código
                                        </th>

                                        <th class="px-5 py-3">
                                            Origen
                                        </th>

                                        <th class="px-5 py-3">
                                            Destino
                                        </th>

                                        <th class="px-5 py-3">
                                            Productos
                                        </th>

                                        <th class="px-5 py-3">
                                            Estado
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-slate-100 dark:divide-slate-800"
                                >
                                    <tr
                                        v-for="transfer in transfers.slice(
                                            0,
                                            5,
                                        )"
                                        :key="transfer.id"
                                        class="hover:bg-slate-50 dark:hover:bg-slate-800/40"
                                    >
                                        <td
                                            class="whitespace-nowrap px-5 py-4 text-slate-600 dark:text-slate-300"
                                        >
                                            {{ transfer.date }}
                                        </td>

                                        <td
                                            class="px-5 py-4 font-medium text-slate-800 dark:text-slate-200"
                                        >
                                            {{ transfer.code }}
                                        </td>

                                        <td
                                            class="px-5 py-4 text-slate-600 dark:text-slate-300"
                                        >
                                            {{ transfer.origin }}
                                        </td>

                                        <td
                                            class="px-5 py-4 text-slate-600 dark:text-slate-300"
                                        >
                                            {{ transfer.destination }}
                                        </td>

                                        <td
                                            class="px-5 py-4 text-slate-600 dark:text-slate-300"
                                        >
                                            {{
                                                formatNumber(
                                                    transfer.products_count,
                                                )
                                            }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                                :class="
                                                    transferStatusClass(
                                                        transfer.status,
                                                    )
                                                "
                                            >
                                                {{
                                                    transfer.status
                                                }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <!-- PRODUCTOS MÁS SALEN -->

                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-start justify-between"
                        >
                            <div>
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Productos con más salidas
                                </h2>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Productos con mayor movimiento
                                </p>
                            </div>

                            <Link
                                href="#"
                                class="text-sm font-semibold text-violet-600 hover:text-violet-700"
                            >
                                Ver reporte
                            </Link>
                        </div>

                        <div
                            v-if="!topProducts.length"
                            class="mt-8 text-center text-sm text-slate-500"
                        >
                            No hay información disponible.
                        </div>

                        <div
                            v-else
                            class="mt-6 space-y-5"
                        >
                            <div
                                v-for="(
                                    product, index
                                ) in topProducts.slice(0, 5)"
                                :key="product.store_id"
                            >
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <div
                                        class="flex min-w-0 items-center gap-3"
                                    >
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-sm font-bold text-violet-700 dark:bg-violet-950/40 dark:text-violet-300"
                                        >
                                            {{ index + 1 }}
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                {{
                                                    product.name_product
                                                }}
                                            </p>

                                            <p
                                                class="mt-0.5 text-xs text-slate-400"
                                            >
                                                {{
                                                    product.code_product
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <span
                                        class="shrink-0 text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{
                                            formatNumber(
                                                product.total_quantity,
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="mt-2 ml-11 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                >
                                    <div
                                        class="h-full rounded-full bg-violet-600"
                                        :style="{
                                            width: productBarWidth(
                                                product.total_quantity,
                                            ),
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                    </article>
                </section>

                <!-- =====================================================
                     INVENTARIO
                ====================================================== -->

                <section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div>
                        <h2
                            class="font-semibold text-slate-900 dark:text-white"
                        >
                            Inventario general
                        </h2>

                        <p
                            class="mt-1 text-xs text-slate-500"
                        >
                            Existencias actuales
                        </p>
                    </div>

                    <div
                        class="mt-5 grid gap-4 sm:grid-cols-3"
                    >

                        <!-- ROLLOS -->

                        <div
                            class="rounded-xl border border-slate-200 p-5 dark:border-slate-800"
                        >
                            <div
                                class="flex items-center gap-3"
                            >
                                <div
                                    class="rounded-xl bg-violet-50 p-3 text-violet-600 dark:bg-violet-950/40 dark:text-violet-300"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <circle
                                            cx="8"
                                            cy="8"
                                            r="4"
                                        />

                                        <circle
                                            cx="16"
                                            cy="16"
                                            r="4"
                                        />

                                        <path
                                            d="M11 5h6"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                                    >
                                        Rollos
                                    </p>

                                    <p
                                        class="mt-1 text-2xl font-bold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            formatNumber(
                                                stats.total_rolls,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- KILOS -->

                        <div
                            class="rounded-xl border border-slate-200 p-5 dark:border-slate-800">
                            <div
                                class="flex items-center gap-3"
                            >
                                <div
                                    class="rounded-xl bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            d="M6 7h12l1 14H5L6 7Z"
                                        />

                                        <path
                                            d="M9 7V5a3 3 0 0 1 6 0v2"
                                        />

                                        <path
                                            d="M8 12h8"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                                    >
                                        Kilos
                                    </p>

                                    <p
                                        class="mt-1 text-2xl font-bold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            formatNumber(
                                                stats.total_kilos,
                                            )
                                        }}
                                        <span
                                            class="text-sm font-medium text-slate-400"
                                        >
                                            kg
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- METROS -->

                        <div
                            class="rounded-xl border border-slate-200 p-5 dark:border-slate-800"
                        >
                            <div
                                class="flex items-center gap-3"
                            >
                                <div
                                    class="rounded-xl bg-orange-50 p-3 text-orange-600 dark:bg-orange-950/40 dark:text-orange-300"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            d="M4 19V5"
                                        />

                                        <path
                                            d="M4 19h16"
                                        />

                                        <path
                                            d="M8 15v4"
                                        />

                                        <path
                                            d="M12 12v7"
                                        />

                                        <path
                                            d="M16 15v4"
                                        />

                                        <path
                                            d="M20 12v7"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                                    >
                                        Metros
                                    </p>

                                    <p
                                        class="mt-1 text-2xl font-bold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            formatNumber(
                                                stats.total_meters,
                                            )
                                        }}
                                        <span
                                            class="text-sm font-medium text-slate-400"
                                        >
                                            m
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- =====================================================
                     GRÁFICO + ALERTAS
                ====================================================== -->

                <section
                    class="grid gap-6 lg:grid-cols-[1.45fr_.55fr]"
                >

                    <!-- GRÁFICO -->

                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-start justify-between"
                        >
                            <div>
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Evolución de salidas
                                </h2>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Últimos 30 días
                                </p>
                            </div>

                            <select
                                v-model="selectedPeriod"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                            >
                                <option value="month">
                                    Este mes
                                </option>

                                <option value="week">
                                    Esta semana
                                </option>

                                <option value="year">
                                    Este año
                                </option>
                            </select>
                        </div>

                        <div
                            v-if="!salesTrend.length"
                            class="flex h-[280px] items-center justify-center text-sm text-slate-500"
                        >
                            No hay información disponible.
                        </div>

                        <div
                            v-else
                            class="mt-5"
                        >
                            <svg
                                viewBox="0 0 900 280"
                                class="h-[280px] w-full"
                                preserveAspectRatio="none"
                            >
                                <!-- GRID -->

                                <line
                                    x1="30"
                                    y1="25"
                                    x2="880"
                                    y2="25"
                                    class="text-slate-100 dark:text-slate-800"
                                    stroke="currentColor"
                                />

                                <line
                                    x1="30"
                                    y1="100"
                                    x2="880"
                                    y2="100"
                                    class="text-slate-100 dark:text-slate-800"
                                    stroke="currentColor"
                                />

                                <line
                                    x1="30"
                                    y1="175"
                                    x2="880"
                                    y2="175"
                                    class="text-slate-100 dark:text-slate-800"
                                    stroke="currentColor"
                                />

                                <line
                                    x1="30"
                                    y1="250"
                                    x2="880"
                                    y2="250"
                                    class="text-slate-200 dark:text-slate-700"
                                    stroke="currentColor"
                                />

                                <!-- AREA -->

                                <polygon
                                    :points="chart.area"
                                    fill="rgba(124, 58, 237, 0.08)"
                                />

                                <!-- LINEA -->

                                <polyline
                                    :points="chart.line"
                                    fill="none"
                                    stroke="#7c3aed"
                                    stroke-width="4"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <!-- PUNTOS -->

                                <circle
                                    v-for="(
                                        point, index
                                    ) in chart.points"
                                    :key="index"
                                    :cx="point.x"
                                    :cy="point.y"
                                    r="4"
                                    fill="white"
                                    stroke="#7c3aed"
                                    stroke-width="3"
                                />
                            </svg>

                            <div
                                class="flex justify-between text-xs text-slate-400"
                            >
                                <span>
                                    Inicio del periodo
                                </span>

                                <span>
                                    Hoy
                                </span>
                            </div>
                        </div>
                    </article>

                    <!-- ALERTAS -->

                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div>
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Atención requerida
                            </h2>

                            <p
                                class="mt-1 text-xs text-slate-500"
                            >
                                Situaciones que requieren atención
                            </p>
                        </div>

                        <div
                            v-if="!alerts.length"
                            class="mt-6 rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500 dark:bg-slate-800/50"
                        >
                            No hay alertas pendientes.
                        </div>

                        <div
                            v-else
                            class="mt-5 space-y-3"
                        >
                            <Link
                                v-for="alert in alerts.slice(
                                    0,
                                    4,
                                )"
                                :key="`${alert.type}-${alert.title}`"
                                :href="alert.action_url || '#'"
                                class="block rounded-xl border p-4 transition hover:shadow-sm"
                                :class="alertClasses(alert.type)"
                            >
                                <div
                                    class="flex items-center gap-3"
                                >
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                        :class="
                                            alertIconClasses(
                                                alert.type,
                                            )
                                        "
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                d="M10.3 3.8 2.9 17a2 2 0 0 0 1.7 3h14.8a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"
                                            />

                                            <path
                                                d="M12 9v4"
                                            />

                                            <path
                                                d="M12 17h.01"
                                            />
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-100"
                                        >
                                            {{
                                                alert.count
                                            }}
                                            {{
                                                alert.title
                                            }}
                                        </p>

                                        <p
                                            class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                alert.description
                                            }}
                                        </p>
                                    </div>

                                    <span
                                        class="text-lg text-slate-400"
                                    >
                                        →
                                    </span>
                                </div>
                            </Link>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AppLayout>
</template>