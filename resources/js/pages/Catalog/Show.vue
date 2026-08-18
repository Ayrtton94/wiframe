<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Catálogo',
        href: '/catalog',
    },
];

const { product, stock_summary, stock_locations } =
    defineProps<{
        product: {
            id: number;
            code_product: string;
            name_product: string;
            fabric_type: string;
            color: string;
            proveedor: string;
            price: number;
            public_price: number;
            wholesale_price: number;
            price_roll?: number | null;
            special_price?: number | null;
            location?: string | null;
            description?: string | null;
            image_url?: string | null;
        };

        stock_summary: {
            kilos_available: number;
            metros_available: number;
            kilos_reserved: number;
            metros_reserved: number;
        };

        stock_locations: Array<{
            id: number;
            warehouse_id: number;
            warehouse_name: string;
            warehouse_code: string;
            kilos_available: number;
            metros_available: number;
            kilos_reserved: number;
            metros_reserved: number;
        }>;
    }>();

const showImage = ref(false);

const currency = (value: number | null | undefined) => {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
};
</script>

<template>
    <Head :title="product.name_product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6
                   bg-slate-50 p-4
                   dark:bg-slate-950"
        >
            <!-- ENCABEZADO -->
            <div
                class="flex flex-col gap-4
                       sm:flex-row sm:items-center
                       sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ product.name_product }}
                    </h1>

                    <p
                        class="text-sm text-slate-500
                               dark:text-slate-400"
                    >
                        {{ product.code_product }}
                    </p>
                </div>

                <Link
                    href="/catalog"
                    class="rounded-lg bg-blue-600 px-4 py-2
                           text-center text-sm font-medium
                           text-white transition
                           hover:bg-blue-700
                           dark:hover:bg-blue-500"
                >
                    Volver
                </Link>
            </div>

            <!-- INFORMACIÓN PRINCIPAL -->
            <div
                class="grid gap-6 md:grid-cols-2"
            >
                <!-- IMAGEN -->
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <img
                        v-if="product.image_url"
                        :src="product.image_url"
                        :alt="product.name_product"
                        class="h-64 w-full cursor-pointer
                               rounded-lg object-cover
                               transition hover:scale-[1.02]"
                        @click="showImage = true"
                    />

                    <div
                        v-else
                        class="flex h-64 items-center
                               justify-center rounded-lg
                               bg-slate-100
                               dark:bg-slate-800"
                        aria-hidden="true"
                    >
                        <span
                            class="text-sm
                                   text-slate-400
                                   dark:text-slate-500"
                        >
                            Sin imagen
                        </span>
                    </div>
                </div>

                <!-- INFORMACIÓN -->
                <div
                    class="space-y-3 rounded-xl border
                           border-slate-200
                           bg-white p-5 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <div>
                        <span
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            Código
                        </span>

                        <p
                            class="font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ product.code_product }}
                        </p>
                    </div>

                    <div>
                        <span
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            Tipo de tela
                        </span>

                        <p
                            class="font-medium
                                   text-slate-800
                                   dark:text-slate-200"
                        >
                            {{ product.fabric_type }}
                        </p>
                    </div>

                    <div>
                        <span
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            Color
                        </span>

                        <p
                            class="font-medium
                                   text-slate-800
                                   dark:text-slate-200"
                        >
                            {{ product.color }}
                        </p>
                    </div>

                    <div>
                        <span
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            Proveedor
                        </span>

                        <p
                            class="font-medium
                                   text-slate-800
                                   dark:text-slate-200"
                        >
                            {{ product.proveedor }}
                        </p>
                    </div>

                    <div>
                        <span
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            Ubicación
                        </span>

                        <p
                            class="font-medium
                                   text-slate-800
                                   dark:text-slate-200"
                        >
                            {{
                                product.location ||
                                'No definida'
                            }}
                        </p>
                    </div>

                    <div>
                        <span
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            Descripción
                        </span>

                        <p
                            class="font-medium
                                   text-slate-800
                                   dark:text-slate-200"
                        >
                            {{
                                product.description ||
                                'Sin descripción'
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- VISOR DE IMAGEN -->
            <div
                v-if="showImage"
                class="fixed inset-0 z-50 flex
                       items-center justify-center
                       bg-slate-950/90 p-4"
                @click="showImage = false"
            >
                <img
                    :src="product.image_url ?? ''"
                    :alt="product.name_product"
                    class="max-h-[90vh] max-w-[90vw]
                           rounded-xl object-contain
                           shadow-2xl"
                    @click.stop
                />
            </div>

            <!-- PRECIOS -->
            <div
                class="grid gap-4
                       sm:grid-cols-2
                       lg:grid-cols-5"
            >
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-sm text-slate-500
                               dark:text-slate-400"
                    >
                        Precio base
                    </p>

                    <p
                        class="mt-1 text-xl font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ currency(product.price) }}
                    </p>
                </div>

                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-sm text-slate-500
                               dark:text-slate-400"
                    >
                        Precio público
                    </p>

                    <p
                        class="mt-1 text-xl font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ currency(product.public_price) }}
                    </p>
                </div>

                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-sm text-slate-500
                               dark:text-slate-400"
                    >
                        Precio mayorista
                    </p>

                    <p
                        class="mt-1 text-xl font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ currency(product.wholesale_price) }}
                    </p>
                </div>

                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-sm text-slate-500
                               dark:text-slate-400"
                    >
                        Precio por rollo
                    </p>

                    <p
                        class="mt-1 text-xl font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ currency(product.price_roll) }}
                    </p>
                </div>

                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-4 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-sm text-slate-500
                               dark:text-slate-400"
                    >
                        Precio especial
                    </p>

                    <p
                        class="mt-1 text-xl font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ currency(product.special_price) }}
                    </p>
                </div>
            </div>

            <!-- RESUMEN DE STOCK -->
            <div
                class="rounded-xl border
                       border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <h2
                    class="mb-4 text-lg font-semibold
                           text-slate-900
                           dark:text-slate-100"
                >
                    Resumen de stock
                </h2>

                <div
                    class="grid gap-4
                           sm:grid-cols-2
                           lg:grid-cols-4"
                >
                    <div
                        class="rounded-lg bg-slate-50 p-4
                               dark:bg-slate-800"
                    >
                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Rollos disponibles
                        </p>

                        <p
                            class="mt-1 text-2xl font-bold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{
                                stock_summary.kilos_available
                            }}
                        </p>
                    </div>

                    <div
                        class="rounded-lg bg-slate-50 p-4
                               dark:bg-slate-800"
                    >
                        <p
                            class="text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Metros disponibles
                        </p>

                        <p
                            class="mt-1 text-2xl font-bold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{
                                stock_summary.metros_available
                            }}
                        </p>
                    </div>

                    <div
                        class="rounded-lg bg-amber-50 p-4
                               dark:bg-amber-500/10"
                    >
                        <p
                            class="text-sm
                                   text-amber-700
                                   dark:text-amber-400"
                        >
                            Rollos reservados
                        </p>

                        <p
                            class="mt-1 text-2xl font-bold
                                   text-amber-900
                                   dark:text-amber-300"
                        >
                            {{
                                stock_summary.kilos_reserved
                            }}
                        </p>
                    </div>

                    <div
                        class="rounded-lg bg-amber-50 p-4
                               dark:bg-amber-500/10"
                    >
                        <p
                            class="text-sm
                                   text-amber-700
                                   dark:text-amber-400"
                        >
                            Metros reservados
                        </p>

                        <p
                            class="mt-1 text-2xl font-bold
                                   text-amber-900
                                   dark:text-amber-300"
                        >
                            {{
                                stock_summary.metros_reserved
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- STOCK POR ALMACÉN -->
            <div
                class="overflow-hidden rounded-xl
                       border border-slate-200
                       bg-white shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div
                    class="border-b
                           border-slate-200 px-5 py-4
                           dark:border-slate-700"
                >
                    <h2
                        class="text-lg font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        Stock por almacén
                    </h2>

                    <p
                        class="mt-1 text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Existencias disponibles en cada ubicación.
                    </p>
                </div>

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
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Almacén
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Código
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Rollos
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Metros disponibles
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Rollos reservados
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Metros reservados
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y
                                   divide-slate-200
                                   dark:divide-slate-700"
                        >
                            <tr
                                v-for="row in stock_locations"
                                :key="row.id"
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
                                <td
                                    class="px-4 py-3 text-sm
                                           font-medium
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    {{
                                        row.warehouse_name
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{
                                        row.warehouse_code
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{
                                        row.kilos_available
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{
                                        row.metros_available
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{
                                        row.kilos_reserved
                                    }}
                                </td>

                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{
                                        row.metros_reserved
                                    }}
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    stock_locations.length ===
                                    0
                                "
                            >
                                <td
                                    colspan="6"
                                    class="px-4 py-8
                                           text-center text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay stock registrado
                                    para este producto.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>