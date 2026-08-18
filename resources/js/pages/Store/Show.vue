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
        <div
            class="flex h-full flex-1 flex-col gap-6
                   overflow-x-auto bg-slate-50 p-4
                   dark:bg-slate-950"
        >
            <!-- ENCABEZADO -->
            <div class="mx-auto w-full max-w-6xl">
                <div
                    class="flex flex-col gap-3
                           sm:flex-row sm:items-center
                           sm:justify-between"
                >
                    <div>
                        <div
                            class="flex flex-wrap items-center gap-3"
                        >
                            <h1
                                class="text-2xl font-bold
                                       text-slate-900
                                       dark:text-slate-100"
                            >
                                {{ props.store.name_product }}
                            </h1>

                            <span
                                :class="
                                    props.store.is_active
                                        ? 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400'
                                        : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400'
                                "
                                class="rounded-full px-3 py-1
                                       text-xs font-semibold"
                            >
                                {{
                                    props.store.is_active
                                        ? 'Activo'
                                        : 'Inactivo'
                                }}
                            </span>
                        </div>

                        <p
                            class="mt-1 text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Código:
                            {{ props.store.code_product }}
                        </p>
                    </div>

                    <div
                        class="flex flex-col gap-2
                               sm:flex-row"
                    >
                        <Link
                            :href="
                                `/stores/${props.store.id}/edit`
                            "
                            class="inline-flex items-center
                                   justify-center rounded-lg
                                   bg-blue-600 px-5 py-2.5
                                   text-sm font-medium text-white
                                   shadow-sm transition
                                   hover:bg-blue-700
                                   dark:hover:bg-blue-500"
                        >
                            Editar Producto
                        </Link>

                        <Link
                            href="/stores"
                            class="inline-flex items-center
                                   justify-center rounded-lg
                                   border border-slate-300
                                   bg-white px-5 py-2.5
                                   text-sm font-medium
                                   text-slate-700
                                   shadow-sm transition
                                   hover:bg-slate-50
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-300
                                   dark:hover:bg-slate-700"
                        >
                            ← Volver
                        </Link>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO -->
            <div
                class="mx-auto grid w-full max-w-6xl
                       grid-cols-1 gap-6
                       lg:grid-cols-3"
            >
                <!-- IMAGEN -->
                <div
                    class="overflow-hidden rounded-xl
                           border border-slate-200
                           bg-white shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <div
                        class="border-b
                               border-slate-200
                               bg-slate-50 px-6 py-4
                               dark:border-slate-700
                               dark:bg-slate-800"
                    >
                        <h2
                            class="font-semibold
                                   text-slate-800
                                   dark:text-slate-100"
                        >
                            Imagen del producto
                        </h2>
                    </div>

                    <div
                        class="flex min-h-80
                               items-center justify-center
                               p-6"
                    >
                        <img
                            v-if="props.store.image_url"
                            :src="
                                props.store.image_url
                            "
                            :alt="
                                props.store.name_product
                            "
                            class="max-h-80 w-full
                                   rounded-lg object-contain"
                        />

                        <div
                            v-else
                            class="flex h-64 w-full
                                   items-center
                                   justify-center rounded-lg
                                   bg-slate-100
                                   dark:bg-slate-800"
                        >
                            <div class="text-center">
                                <div
                                    class="text-5xl
                                           text-slate-300
                                           dark:text-slate-600"
                                >
                                    📦
                                </div>

                                <p
                                    class="mt-3 text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    Sin imagen disponible
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INFORMACIÓN -->
                <div
                    class="overflow-hidden rounded-xl
                           border border-slate-200
                           bg-white shadow-sm
                           lg:col-span-2
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <div
                        class="border-b
                               border-slate-200
                               bg-slate-50 px-6 py-4
                               dark:border-slate-700
                               dark:bg-slate-800"
                    >
                        <h2
                            class="font-semibold
                                   text-slate-800
                                   dark:text-slate-100"
                        >
                            Información del producto
                        </h2>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-5 p-6
                               sm:grid-cols-2"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold
                                       uppercase tracking-wide
                                       text-slate-400
                                       dark:text-slate-500"
                            >
                                Código
                            </p>

                            <p
                                class="mt-1 text-sm font-medium
                                       text-slate-800
                                       dark:text-slate-100"
                            >
                                {{
                                    props.store.code_product ||
                                    '-'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold
                                       uppercase tracking-wide
                                       text-slate-400
                                       dark:text-slate-500"
                            >
                                Nombre
                            </p>

                            <p
                                class="mt-1 text-sm font-medium
                                       text-slate-800
                                       dark:text-slate-100"
                            >
                                {{
                                    props.store.name_product ||
                                    '-'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold
                                       uppercase tracking-wide
                                       text-slate-400
                                       dark:text-slate-500"
                            >
                                Tipo de Tela
                            </p>

                            <p
                                class="mt-1 text-sm
                                       text-slate-700
                                       dark:text-slate-300"
                            >
                                {{
                                    props.store.fabric_type ||
                                    '-'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold
                                       uppercase tracking-wide
                                       text-slate-400
                                       dark:text-slate-500"
                            >
                                Color
                            </p>

                            <p
                                class="mt-1 text-sm
                                       text-slate-700
                                       dark:text-slate-300"
                            >
                                {{
                                    props.store.color ||
                                    '-'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold
                                       uppercase tracking-wide
                                       text-slate-400
                                       dark:text-slate-500"
                            >
                                Proveedor
                            </p>

                            <p
                                class="mt-1 text-sm
                                       text-slate-700
                                       dark:text-slate-300"
                            >
                                {{
                                    props.store.proveedor ||
                                    '-'
                                }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-semibold
                                       uppercase tracking-wide
                                       text-slate-400
                                       dark:text-slate-500"
                            >
                                Almacén y/o Tienda
                            </p>

                            <p
                                class="mt-1 text-sm
                                       text-slate-700
                                       dark:text-slate-300"
                            >
                                {{
                                    props.store.warehouse_stocks
                                        .map(
                                            (stock) =>
                                                stock.warehouse_name,
                                        )
                                        .filter(Boolean)
                                        .join(', ') || '-'
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- STOCK -->
                <div
                    class="overflow-hidden rounded-xl
                           border border-slate-200
                           bg-white shadow-sm
                           lg:col-span-3
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <div
                        class="border-b
                               border-slate-200
                               bg-slate-50 px-6 py-4
                               dark:border-slate-700
                               dark:bg-slate-800"
                    >
                        <h2
                            class="font-semibold
                                   text-slate-800
                                   dark:text-slate-100"
                        >
                            Stock
                        </h2>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-4 p-6
                               sm:grid-cols-3"
                    >
                        <!-- ROLLOS -->
                        <div
                            class="rounded-lg
                                   border border-slate-200
                                   bg-slate-50 p-5
                                   dark:border-slate-700
                                   dark:bg-slate-800"
                        >
                            <p
                                class="text-xs font-semibold
                                       uppercase
                                       tracking-wide
                                       text-slate-500
                                       dark:text-slate-400"
                            >
                                Rollos
                            </p>

                            <p
                                class="mt-2 text-2xl
                                       font-bold
                                       text-blue-600
                                       dark:text-blue-400"
                            >
                                {{ props.store.kilos }}
                            </p>
                        </div>

                        <!-- METROS -->
                        <div
                            class="rounded-lg
                                   border border-slate-200
                                   bg-slate-50 p-5
                                   dark:border-slate-700
                                   dark:bg-slate-800"
                        >
                            <p
                                class="text-xs font-semibold
                                       uppercase
                                       tracking-wide
                                       text-slate-500
                                       dark:text-slate-400"
                            >
                                Metros
                            </p>

                            <p
                                class="mt-2 text-2xl
                                       font-bold
                                       text-blue-600
                                       dark:text-blue-400"
                            >
                                {{ props.store.metros }}
                            </p>
                        </div>

                        <!-- STOCK MÍNIMO -->
                        <div
                            class="rounded-lg
                                   border border-slate-200
                                   bg-slate-50 p-5
                                   dark:border-slate-700
                                   dark:bg-slate-800"
                        >
                            <p
                                class="text-xs font-semibold
                                       uppercase
                                       tracking-wide
                                       text-slate-500
                                       dark:text-slate-400"
                            >
                                Stock mínimo
                            </p>

                            <p
                                class="mt-2 text-2xl
                                       font-bold
                                       text-slate-800
                                       dark:text-slate-100"
                            >
                                {{
                                    props.store.minimum_stock
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- PRECIOS -->
                <div
                    class="overflow-hidden rounded-xl
                           border border-slate-200
                           bg-white shadow-sm
                           lg:col-span-3
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <div
                        class="border-b
                               border-slate-200
                               bg-slate-50 px-6 py-4
                               dark:border-slate-700
                               dark:bg-slate-800"
                    >
                        <h2
                            class="font-semibold
                                   text-slate-800
                                   dark:text-slate-100"
                        >
                            Precios
                        </h2>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-4 p-6
                               sm:grid-cols-2
                               lg:grid-cols-5"
                    >
                        <!-- PRECIO -->
                        <div
                            class="rounded-lg
                                   border
                                   border-slate-200 p-5
                                   dark:border-slate-700"
                        >
                            <p
                                class="text-xs font-semibold
                                       uppercase
                                       text-slate-400
                                       dark:text-slate-500"
                            >
                                Precio
                            </p>

                            <p
                                class="mt-2 text-xl
                                       font-bold
                                       text-slate-800
                                       dark:text-slate-100"
                            >
                                S/
                                {{
                                    Number(
                                        props.store.price,
                                    ).toFixed(2)
                                }}
                            </p>
                        </div>

                        <!-- PÚBLICO -->
                        <div
                            class="rounded-lg
                                   border
                                   border-slate-200 p-5
                                   dark:border-slate-700"
                        >
                            <p
                                class="text-xs font-semibold
                                       uppercase
                                       text-slate-400
                                       dark:text-slate-500"
                            >
                                Público
                            </p>

                            <p
                                class="mt-2 text-xl
                                       font-bold
                                       text-blue-600
                                       dark:text-blue-400"
                            >
                                S/
                                {{
                                    Number(
                                        props.store.public_price,
                                    ).toFixed(2)
                                }}
                            </p>
                        </div>

                        <!-- MAYORISTA -->
                        <div
                            class="rounded-lg
                                   border
                                   border-slate-200 p-5
                                   dark:border-slate-700"
                        >
                            <p
                                class="text-xs font-semibold
                                       uppercase
                                       text-slate-400
                                       dark:text-slate-500"
                            >
                                Mayorista
                            </p>

                            <p
                                class="mt-2 text-xl
                                       font-bold
                                       text-green-600
                                       dark:text-green-400"
                            >
                                S/
                                {{
                                    Number(
                                        props.store.wholesale_price,
                                    ).toFixed(2)
                                }}
                            </p>
                        </div>

                        <!-- POR ROLLO -->
                        <div
                            class="rounded-lg
                                   border
                                   border-slate-200 p-5
                                   dark:border-slate-700"
                        >
                            <p
                                class="text-xs font-semibold
                                       uppercase
                                       text-slate-400
                                       dark:text-slate-500"
                            >
                                Por Rollo
                            </p>

                            <p
                                class="mt-2 text-xl
                                       font-bold
                                       text-purple-600
                                       dark:text-purple-400"
                            >
                                S/
                                {{
                                    Number(
                                        props.store.price_roll,
                                    ).toFixed(2)
                                }}
                            </p>
                        </div>

                        <!-- ESPECIAL -->
                        <div
                            class="rounded-lg
                                   border
                                   border-amber-200
                                   bg-amber-50 p-5
                                   dark:border-amber-500/30
                                   dark:bg-amber-500/10"
                        >
                            <p
                                class="text-xs font-semibold
                                       uppercase
                                       text-amber-600
                                       dark:text-amber-400"
                            >
                                Especial
                            </p>

                            <p
                                class="mt-2 text-xl
                                       font-bold
                                       text-amber-700
                                       dark:text-amber-300"
                            >
                                S/
                                {{
                                    Number(
                                        props.store.special_price,
                                    ).toFixed(2)
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- DESCRIPCIÓN -->
                <div
                    class="overflow-hidden rounded-xl
                           border border-slate-200
                           bg-white shadow-sm
                           lg:col-span-3
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <div
                        class="border-b
                               border-slate-200
                               bg-slate-50 px-6 py-4
                               dark:border-slate-700
                               dark:bg-slate-800"
                    >
                        <h2
                            class="font-semibold
                                   text-slate-800
                                   dark:text-slate-100"
                        >
                            Descripción
                        </h2>
                    </div>

                    <div class="p-6">
                        <p
                            v-if="
                                props.store.description
                            "
                            class="whitespace-pre-line
                                   text-sm leading-7
                                   text-slate-600
                                   dark:text-slate-300"
                        >
                            {{ props.store.description }}
                        </p>

                        <p
                            v-else
                            class="text-sm italic
                                   text-slate-400
                                   dark:text-slate-500"
                        >
                            No hay una descripción registrada
                            para este producto.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>