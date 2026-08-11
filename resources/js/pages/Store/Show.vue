```vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    store: {
        id: number;
        code_product: string;
        name_product: string;
        fabric_type: string;
        color: string;
        proveedor: string;
        minimum_stock: number;
        kilos: number;
        metros: number;
        price: number;
        public_price: number;
        wholesale_price: number;
        price_roll: number;
        special_price: number;
        location: string;
        is_active: boolean;
        description: string;
        image_url: string | null;

        warehouse_stocks: {
            id: number;
            warehouse_id: number;
            warehouse_name: string | null;
            kilos_available: number;
            metros_available: number;
            kilos_reserved: number;
            metros_reserved: number;
        }[];
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Productos', href: '/stores' },
    {
        title: props.store.name_product,
        href: `/stores/${props.store.id}`,
    },
];
</script>

<template>
    <Head :title="props.store.name_product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">

            <!-- Encabezado -->
            <div class="mx-auto w-full max-w-6xl">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-2xl font-bold text-slate-800">
                                {{ props.store.name_product }}
                            </h1>

                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="
                                    props.store.is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                "
                            >
                                {{ props.store.is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-slate-500">
                            Código: {{ props.store.code_product }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">

                        <Link
                            :href="`/stores/${props.store.id}/edit`"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                        >
                            Editar Producto
                        </Link>

                        <Link
                            href="/stores"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                        >
                            ← Volver
                        </Link>

                    </div>
                </div>
            </div>


            <!-- Contenido -->
            <div class="mx-auto grid w-full max-w-6xl grid-cols-1 gap-6 lg:grid-cols-3">

                <!-- Imagen -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <h2 class="font-semibold text-slate-800">
                            Imagen del producto
                        </h2>
                    </div>

                    <div class="flex min-h-80 items-center justify-center p-6">

                        <img
                            v-if="props.store.image_url"
                            :src="props.store.image_url"
                            :alt="props.store.name_product"
                            class="max-h-80 w-full rounded-lg object-contain"
                        />

                        <div
                            v-else
                            class="flex h-64 w-full items-center justify-center rounded-lg bg-slate-100"
                        >
                            <div class="text-center">
                                <div class="text-5xl text-slate-300">
                                    📦
                                </div>

                                <p class="mt-3 text-sm text-slate-500">
                                    Sin imagen disponible
                                </p>
                            </div>
                        </div>

                    </div>
                </div>


                <!-- Información -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">

                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <h2 class="font-semibold text-slate-800">
                            Información del producto
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Código
                            </p>

                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ props.store.code_product || '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Nombre
                            </p>

                            <p class="mt-1 text-sm font-medium text-slate-800">
                                {{ props.store.name_product || '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Tipo de Tela
                            </p>

                            <p class="mt-1 text-sm text-slate-700">
                                {{ props.store.fabric_type || '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Color
                            </p>

                            <p class="mt-1 text-sm text-slate-700">
                                {{ props.store.color || '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Proveedor
                            </p>

                            <p class="mt-1 text-sm text-slate-700">
                                {{ props.store.proveedor || '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Almacén y/o Tienda
                            </p>

                            <p class="mt-1 text-sm text-slate-700">
                                {{ props.store.warehouse_stocks.map(stock => stock.warehouse_name).join(', ') || '-' }}
                            </p>
                        </div>

                    </div>
                </div>


                <!-- Stock -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-3">

                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <h2 class="font-semibold text-slate-800">
                            Stock
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-3">

                        

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Rollos
                            </p>

                            <p class="mt-2 text-2xl font-bold text-blue-600">
                                {{ props.store.kilos }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Metros
                            </p>

                            <p class="mt-2 text-2xl font-bold text-blue-600">
                                {{ props.store.metros }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                kilos
                            </p>

                            <p class="mt-2 text-2xl font-bold text-slate-800">
                                {{ props.store.minimum_stock }}
                            </p>
                        </div>

                    </div>
                </div>


                <!-- Precios -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-3">

                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <h2 class="font-semibold text-slate-800">
                            Precios
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 lg:grid-cols-5">

                        <div class="rounded-lg border border-slate-200 p-5">
                            <p class="text-xs font-semibold uppercase text-slate-400">
                                Precio
                            </p>

                            <p class="mt-2 text-xl font-bold text-slate-800">
                                $ {{ Number(props.store.price).toFixed(2) }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-5">
                            <p class="text-xs font-semibold uppercase text-slate-400">
                                Público
                            </p>

                            <p class="mt-2 text-xl font-bold text-blue-600">
                                $ {{ Number(props.store.public_price).toFixed(2) }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-5">
                            <p class="text-xs font-semibold uppercase text-slate-400">
                                Mayorista
                            </p>

                            <p class="mt-2 text-xl font-bold text-green-600">
                                $ {{ Number(props.store.wholesale_price).toFixed(2) }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-5">
                            <p class="text-xs font-semibold uppercase text-slate-400">
                                Por Rollo
                            </p>

                            <p class="mt-2 text-xl font-bold text-purple-600">
                                $ {{ Number(props.store.price_roll).toFixed(2) }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-amber-50 p-5">
                            <p class="text-xs font-semibold uppercase text-amber-600">
                                Especial
                            </p>

                            <p class="mt-2 text-xl font-bold text-amber-700">
                                $ {{ Number(props.store.special_price).toFixed(2) }}
                            </p>
                        </div>

                    </div>
                </div>


                <!-- Descripción -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-3">

                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <h2 class="font-semibold text-slate-800">
                            Descripción
                        </h2>
                    </div>

                    <div class="p-6">
                        <p
                            v-if="props.store.description"
                            class="whitespace-pre-line text-sm leading-7 text-slate-600"
                        >
                            {{ props.store.description }}
                        </p>

                        <p
                            v-else
                            class="text-sm italic text-slate-400"
                        >
                            No hay una descripción registrada para este producto.
                        </p>
                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>
```
