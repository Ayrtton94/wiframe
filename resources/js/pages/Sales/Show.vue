<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    sale: {
        id: number;
        code: string;
        status: string;
        subtotal: number;
        total: number;
        notes: string | null;
        customer: { name: string; dni: string };
        warehouse: { name: string; code: string };
        seller: { name: string };
        items: Array<{
            id: number;
            unit: string;
            quantity: number;
            unit_price: number;
            line_total: number;
            store: { code_product: string; name_product: string };
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ventas', href: '/sales' },
    { title: props.sale.code, href: `/sales/${props.sale.id}` },
];
</script>

<template>
    <Head :title="`Venta ${props.sale.code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <section
                class="flex items-center justify-between rounded-xl border bg-white p-5 shadow-sm"
            >
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">
                        {{ props.sale.code }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        {{ props.sale.customer.name }} ·
                        {{ props.sale.warehouse.name }}
                    </p>
                </div>
                <Link
                    href="/sales"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                    >Volver</Link
                >
            </section>

            <section class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500 uppercase">Cliente</p>
                    <p class="mt-2 text-sm font-medium">
                        {{ props.sale.customer.dni }} -
                        {{ props.sale.customer.name }}
                    </p>
                </div>
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500 uppercase">Ubicación</p>
                    <p class="mt-2 text-sm font-medium">
                        {{ props.sale.warehouse.code }} -
                        {{ props.sale.warehouse.name }}
                    </p>
                </div>
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500 uppercase">Vendedor</p>
                    <p class="mt-2 text-sm font-medium">
                        {{ props.sale.seller.name }}
                    </p>
                </div>
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500 uppercase">Estado</p>
                    <p class="mt-2 text-sm font-medium">
                        {{ props.sale.status }}
                    </p>
                </div>
            </section>

            <section
                class="overflow-hidden rounded-xl border bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Producto
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Unidad
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Cantidad
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Precio
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Importe
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in props.sale.items" :key="item.id">
                                <td class="px-4 py-3 text-sm">
                                    {{ item.store.code_product }} -
                                    {{ item.store.name_product }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ item.unit }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ item.quantity }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    S/ {{ item.unit_price }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    S/ {{ item.line_total }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <div
                    class="rounded-xl border bg-white p-4 shadow-sm md:col-span-2"
                >
                    <p class="text-xs text-slate-500 uppercase">Notas</p>
                    <p class="mt-2 text-sm text-slate-700">
                        {{ props.sale.notes || 'Sin notas registradas.' }}
                    </p>
                </div>
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500 uppercase">Subtotal</p>
                    <p class="mt-2 text-lg font-semibold">
                        S/ {{ props.sale.subtotal }}
                    </p>
                    <p class="mt-4 text-xs text-slate-500 uppercase">Total</p>
                    <p class="mt-2 text-2xl font-bold">
                        S/ {{ props.sale.total }}
                    </p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>