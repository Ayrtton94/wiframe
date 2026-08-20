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

const formatNumber = (value: number | string) => {
    const numberValue = Number(value);

    if (Number.isNaN(numberValue)) {
        return String(value);
    }

    if (Number.isInteger(numberValue)) {
        return numberValue.toString();
    }

    return numberValue.toFixed(2).replace(/\.00$/, '');
};
</script>
<template>
    <Head :title="`Venta ${props.sale.code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6
                   rounded-xl bg-slate-50 p-4
                   dark:bg-slate-950"
        >
            <!-- ENCABEZADO -->
            <section
                class="flex flex-col gap-4
                       rounded-xl border
                       border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900
                       sm:flex-row
                       sm:items-center
                       sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ props.sale.code }}
                    </h1>

                    <p
                        class="text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        {{ props.sale.customer.name }} ·
                        {{ props.sale.warehouse.name }}
                    </p>
                </div>

                <Link
                    href="/sales"
                    class="inline-flex w-fit items-center
                           justify-center rounded-lg
                           bg-slate-900 px-4 py-2
                           text-sm font-medium text-white
                           transition hover:bg-slate-700
                           dark:bg-slate-700
                           dark:hover:bg-slate-600"
                >
                    Volver
                </Link>
            </section>

            <!-- INFORMACIÓN GENERAL -->
            <section
                class="grid gap-4 md:grid-cols-4"
            >
                <!-- CLIENTE -->
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-xs font-medium
                               uppercase
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Cliente
                    </p>

                    <p
                        class="mt-2 text-sm font-medium
                               text-slate-800
                               dark:text-slate-100"
                    >
                        {{ props.sale.customer.dni }} -
                        {{ props.sale.customer.name }}
                    </p>
                </div>

                <!-- UBICACIÓN -->
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-xs font-medium
                               uppercase
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Ubicación
                    </p>

                    <p
                        class="mt-2 text-sm font-medium
                               text-slate-800
                               dark:text-slate-100"
                    >
                        {{ props.sale.warehouse.code }} -
                        {{ props.sale.warehouse.name }}
                    </p>
                </div>

                <!-- VENDEDOR -->
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-xs font-medium
                               uppercase
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Vendedor
                    </p>

                    <p
                        class="mt-2 text-sm font-medium
                               text-slate-800
                               dark:text-slate-100"
                    >
                        {{ props.sale.seller.name }}
                    </p>
                </div>

                <!-- ESTADO -->
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-xs font-medium
                               uppercase
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Estado
                    </p>

                    <p
                        class="mt-2 text-sm font-medium
                               text-slate-800
                               dark:text-slate-100"
                    >
                        {{ props.sale.status }}
                    </p>
                </div>
            </section>

            <!-- PRODUCTOS -->
            <section
                class="overflow-hidden rounded-xl
                       border border-slate-200
                       bg-white shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y
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
                                    Producto
                                </th>

                                <th
                                    class="px-4 py-3 text-left
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
                                    class="px-4 py-3 text-left
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
                                    Importe
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y
                                   divide-slate-100
                                   dark:divide-slate-700"
                        >
                            <tr
                                v-for="item in props.sale.items"
                                :key="item.id"
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
                                <!-- PRODUCTO -->
                                <td
                                    class="px-4 py-3 text-sm"
                                >
                                    <div
                                        class="font-medium
                                               text-slate-900
                                               dark:text-slate-100"
                                    >
                                        {{ item.store.code_product }}
                                    </div>

                                    <div
                                        class="mt-1 text-sm
                                               text-slate-500
                                               dark:text-slate-400"
                                    >
                                        {{ item.store.name_product }}
                                    </div>
                                </td>

                                <!-- CANTIDAD -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ formatNumber(item.quantity) }}
                                </td>

                                <!-- UNIDAD -->
                                <td
                                    class="px-4 py-3 text-sm
                                           uppercase
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{
                                        item.unit === 'kilos'
                                            ? 'Rollos'
                                            : 'Metros'
                                    }}
                                </td>

                                <!-- PRECIO -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    S/
                                    {{ formatNumber(item.unit_price) }}
                                </td>

                                <!-- IMPORTE -->
                                <td
                                    class="px-4 py-3 text-right
                                           text-sm font-medium
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    S/
                                    {{ formatNumber(item.line_total) }}
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    props.sale.items.length === 0
                                "
                            >
                                <td
                                    colspan="5"
                                    class="px-4 py-8 text-center
                                           text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay productos registrados
                                    en esta venta.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- NOTAS Y TOTALES -->
            <section
                class="grid gap-4 md:grid-cols-3"
            >
                <!-- NOTAS -->
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900
                           md:col-span-2"
                >
                    <p
                        class="text-xs font-medium
                               uppercase
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Notas
                    </p>

                    <p
                        class="mt-2 text-sm
                               text-slate-700
                               dark:text-slate-300"
                    >
                        {{
                            props.sale.notes ||
                            'Sin notas registradas.'
                        }}
                    </p>
                </div>

                <!-- TOTALES -->
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-xs font-medium
                               uppercase
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Subtotal
                    </p>

                    <p
                        class="mt-2 text-lg font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        S/ {{ props.sale.subtotal }}
                    </p>

                    <p
                        class="mt-4 text-xs font-medium
                               uppercase
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Total
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        S/ {{ props.sale.total }}
                    </p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>