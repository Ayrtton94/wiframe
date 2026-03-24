<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    stats: {
        sales_count: number;
        total_revenue: number;
        average_ticket: number;
        products_count: number;
    };
    top_products: Array<{
        store_id: number;
        code_product: string;
        name_product: string;
        total_quantity: number;
        total_amount: number;
    }>;
    best_seller: {
        id: number;
        name: string;
        sales_count: number;
        total_sold: number;
    } | null;
    month_sales_trend: Array<{
        date: string;
        day: number;
        total: number;
    }>;
}>();

const currency = (value: number) =>
    new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
    }).format(Number(value || 0));

const chartConfig = computed(() => {
    if (!props.month_sales_trend.length) {
        return { points: '' };
    }

    const width = 680;
    const height = 220;
    const maxValue = Math.max(
        ...props.month_sales_trend.map((item) => Number(item.total || 0)),
        1,
    );

    const points = props.month_sales_trend
        .map((item, index) => {
            const x =
                props.month_sales_trend.length === 1
                    ? width / 2
                    : (index / (props.month_sales_trend.length - 1)) * width;
            const y = height - (Number(item.total || 0) / maxValue) * (height - 20);

            return `${x},${y}`;
        })
        .join(' ');

    return { points };
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">

            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">
                Dashboard de ventas
            </h1>

            <!-- STATS -->
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-xl border bg-white p-4 shadow-sm dark:bg-slate-900">
                    <p class="text-sm text-slate-500">Ventas registradas</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats.sales_count }}</p>
                </article>

                <article class="rounded-xl border bg-white p-4 shadow-sm dark:bg-slate-900">
                    <p class="text-sm text-slate-500">Ingresos por ventas</p>
                    <p class="mt-2 text-2xl font-bold">{{ currency(stats.total_revenue) }}</p>
                </article>

                <article class="rounded-xl border bg-white p-4 shadow-sm dark:bg-slate-900">
                    <p class="text-sm text-slate-500">Ticket promedio</p>
                    <p class="mt-2 text-2xl font-bold">{{ currency(stats.average_ticket) }}</p>
                </article>

                <article class="rounded-xl border bg-white p-4 shadow-sm dark:bg-slate-900">
                    <p class="text-sm text-slate-500">Productos activos</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats.products_count }}</p>
                </article>
            </section>

            <!-- TOP + SELLER -->
            <section class="grid gap-4 lg:grid-cols-2">

                <!-- TOP PRODUCTOS -->
                <article class="rounded-xl border bg-white p-4 shadow-sm dark:bg-slate-900">
                    <h2 class="mb-3 text-lg font-semibold">Top 5 productos más vendidos</h2>

                    <div v-if="top_products.length === 0" class="text-sm text-slate-500">
                        Aún no hay ventas.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left">Producto</th>
                                    <th class="px-3 py-2 text-left">Cantidad</th>
                                    <th class="px-3 py-2 text-left">Monto</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="product in top_products" :key="product.store_id">
                                    <td class="px-3 py-2">
                                        {{ product.code_product }} - {{ product.name_product }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ Number(product.total_quantity).toFixed(3) }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ currency(product.total_amount) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>

                <!-- BEST SELLER -->
                <article class="rounded-xl border bg-white p-4 shadow-sm dark:bg-slate-900">
                    <h2 class="mb-3 text-lg font-semibold">Mejor vendedor</h2>

                    <div v-if="!best_seller" class="text-sm text-slate-500">
                        No hay datos.
                    </div>

                    <div v-else class="space-y-2 text-sm">
                        <p><strong>Nombre:</strong> {{ best_seller.name }}</p>
                        <p><strong>Ventas:</strong> {{ best_seller.sales_count }}</p>
                        <p><strong>Total:</strong> {{ currency(best_seller.total_sold) }}</p>
                    </div>
                </article>

            </section>

            <!-- GRÁFICO -->
            <section class="rounded-xl border bg-white p-4 shadow-sm dark:bg-slate-900">
                <h2 class="mb-3 text-lg font-semibold">Venta diaria del mes</h2>

                <div v-if="month_sales_trend.length === 0">
                    No hay datos.
                </div>

                <div v-else class="space-y-3">

                    <svg viewBox="0 0 680 220" class="w-full h-[220px]">
                        <polyline
                            fill="none"
                            stroke="#2563eb"
                            stroke-width="3"
                            :points="chartConfig.points"
                        />
                    </svg>

                </div>
            </section>

        </div>
    </AppLayout>
</template>
