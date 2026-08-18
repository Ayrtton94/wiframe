<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps<{
  products: {
    data: Array<{
      id: number;
      code_product: string;
      name_product: string;
      fabric_type: string;
      color: string;
      proveedor: string;
      price: number;
      wholesale_price: number;
      public_price: number;
      image_url: string | null;
    }>;
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
  };
  filters: {
    search?: string;
    fabric_type?: string;
  };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Catálogo',
        href: '/catalog',
    },
];

const filters = reactive({
    search: props.filters.search ?? '',
    fabric_type: props.filters.fabric_type ?? '',
});

const hasResults = computed(() => props.products.data.length > 0);

const submitFilters = () => {
    if (!filters.search && !filters.fabric_type) {
        router.get('/catalog');
        return;
    }

    router.get('/catalog', filters, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['products'],
    });
};

const clearFilters = () => {
    filters.search = '';
    filters.fabric_type = '';
    submitFilters();
};

const stockBadgeClass = (product: {
    public_price: number;
    wholesale_price: number;
}) => {
    if (product.public_price > 0 && product.wholesale_price > 0) {
        return 'bg-emerald-100 text-emerald-700';
    }

    return 'bg-amber-100 text-amber-700';
};

</script>

<template>
    <Head title="Catálogo de productos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 rounded-xl
                   bg-slate-50 p-4
                   dark:bg-slate-950"
        >
            <!-- ENCABEZADO Y FILTROS -->
            <section
                class="rounded-xl border border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div
                    class="mb-4 flex flex-col gap-2
                           md:flex-row md:items-end
                           md:justify-between"
                >
                    <div>
                        <h1
                            class="text-2xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            Catálogo de productos
                        </h1>

                        <p
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            Busca por código, nombre o tipo de tela
                            y revisa el detalle del producto.
                        </p>
                    </div>

                    <div
                        class="rounded-full bg-slate-100 px-4 py-2
                               text-sm font-medium text-slate-700
                               dark:bg-slate-800
                               dark:text-slate-300"
                    >
                        {{ props.products.total }}
                        productos encontrados
                    </div>
                </div>

                <form
                    class="grid gap-3
                           md:grid-cols-[2fr_1fr_auto_auto]"
                    @submit.prevent="submitFilters"
                >
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Buscar por código o nombre"
                        class="rounded-lg border
                               border-slate-300
                               bg-white px-4 py-2.5 text-sm
                               text-slate-900
                               placeholder:text-slate-400
                               shadow-sm outline-none
                               focus:border-blue-500
                               focus:ring-2
                               focus:ring-blue-500/20
                               dark:border-slate-600
                               dark:bg-slate-800
                               dark:text-slate-100
                               dark:placeholder:text-slate-500"
                    />

                    <input
                        v-model="filters.fabric_type"
                        type="text"
                        placeholder="Tipo de tela"
                        class="rounded-lg border
                               border-slate-300
                               bg-white px-4 py-2.5 text-sm
                               text-slate-900
                               placeholder:text-slate-400
                               shadow-sm outline-none
                               focus:border-blue-500
                               focus:ring-2
                               focus:ring-blue-500/20
                               dark:border-slate-600
                               dark:bg-slate-800
                               dark:text-slate-100
                               dark:placeholder:text-slate-500"
                    />

                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600
                               px-4 py-2.5 text-sm font-medium
                               text-white transition
                               hover:bg-blue-700
                               dark:hover:bg-blue-500"
                    >
                        Buscar
                    </button>

                    <button
                        type="button"
                        @click="clearFilters"
                        class="rounded-lg border
                               border-slate-300
                               px-4 py-2.5 text-sm font-medium
                               text-slate-700 transition
                               hover:bg-slate-50
                               dark:border-slate-600
                               dark:text-slate-300
                               dark:hover:bg-slate-800"
                    >
                        Limpiar
                    </button>
                </form>
            </section>

            <!-- TABLA -->
            <section
                class="overflow-hidden rounded-xl
                       border border-slate-200
                       bg-white shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div
                    v-if="hasResults"
                    class="overflow-x-auto"
                >
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
                                           uppercase tracking-wide
                                           text-slate-500
                                           dark:text-slate-300"
                                >
                                    Imagen
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase tracking-wide
                                           text-slate-500
                                           dark:text-slate-300"
                                >
                                    Código
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase tracking-wide
                                           text-slate-500
                                           dark:text-slate-300"
                                >
                                    Producto
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase tracking-wide
                                           text-slate-500
                                           dark:text-slate-300"
                                >
                                    Tipo de tela
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase tracking-wide
                                           text-slate-500
                                           dark:text-slate-300"
                                >
                                    Color
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase tracking-wide
                                           text-slate-500
                                           dark:text-slate-300"
                                >
                                    Proveedor
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase tracking-wide
                                           text-slate-500
                                           dark:text-slate-300"
                                >
                                    Precios
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase tracking-wide
                                           text-slate-500
                                           dark:text-slate-300"
                                >
                                    Estado
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase tracking-wide
                                           text-slate-500
                                           dark:text-slate-300"
                                >
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y
                                   divide-slate-100
                                   dark:divide-slate-700"
                        >
                            <tr
                                v-for="product in props.products.data"
                                :key="product.id"
                                class="transition
                                       hover:bg-slate-50/70
                                       dark:hover:bg-slate-800/70"
                            >
                                <!-- IMAGEN -->
                                <td
                                    class="px-4 py-3"
                                >
                                    <img
                                        v-if="
                                            product.image_url
                                        "
                                        :src="
                                            product.image_url
                                        "
                                        :alt="
                                            product.name_product
                                        "
                                        class="h-14 w-14
                                               rounded-lg
                                               object-cover"
                                    />

                                    <div
                                        v-else
                                        class="h-14 w-14
                                               rounded-lg
                                               bg-slate-100
                                               dark:bg-slate-800"
                                        aria-hidden="true"
                                    ></div>
                                </td>

                                <!-- CÓDIGO -->
                                <td
                                    class="px-4 py-3 text-sm
                                           font-medium
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    {{ product.code_product }}
                                </td>

                                <!-- PRODUCTO -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ product.name_product }}
                                </td>

                                <!-- TELA -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ product.fabric_type }}
                                </td>

                                <!-- COLOR -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ product.color }}
                                </td>

                                <!-- PROVEEDOR -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{ product.proveedor }}
                                </td>

                                <!-- PRECIOS -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    <div>
                                        Público:
                                        S/
                                        {{
                                            product.public_price
                                        }}
                                    </div>

                                    <div>
                                        Mayorista:
                                        S/
                                        {{
                                            product.wholesale_price
                                        }}
                                    </div>
                                </td>

                                <!-- ESTADO -->
                                <td
                                    class="px-4 py-3 text-sm"
                                >
                                    <span
                                        class="inline-flex
                                               rounded-full
                                               px-3 py-1
                                               text-xs
                                               font-semibold"
                                        :class="
                                            stockBadgeClass(
                                                product,
                                            )
                                        "
                                    >
                                        Disponible para consulta
                                    </span>
                                </td>

                                <!-- ACCIÓN -->
                                <td
                                    class="px-4 py-3 text-sm"
                                >
                                    <Link
                                        :href="`/catalog/${product.id}`"
                                        class="inline-flex
                                               rounded-lg
                                               bg-slate-900 px-3 py-2
                                               text-sm font-medium
                                               text-white transition
                                               hover:bg-slate-700
                                               dark:bg-slate-700
                                               dark:hover:bg-slate-600"
                                    >
                                        Ver detalle
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- SIN RESULTADOS -->
                <div
                    v-else
                    class="flex flex-col items-center
                           gap-2 px-6 py-14 text-center"
                >
                    <h2
                        class="text-lg font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        No se encontraron productos
                    </h2>

                    <p
                        class="max-w-md text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Intenta cambiar los filtros de búsqueda
                        para encontrar un producto del catálogo.
                    </p>
                </div>
            </section>

            <!-- PAGINACIÓN -->
            <section
                class="flex flex-col gap-3 rounded-xl
                       border border-slate-200
                       bg-white p-4 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900
                       md:flex-row md:items-center
                       md:justify-between"
            >
                <p
                    class="text-sm text-slate-600
                           dark:text-slate-400"
                >
                    Mostrando
                    {{ props.products.from ?? 0 }}
                    a
                    {{ props.products.to ?? 0 }}
                    de
                    {{ props.products.total }}
                    registros.
                </p>

                <div
                    class="flex items-center gap-2"
                >
                    <Link
                        v-if="
                            props.products.current_page >
                            1
                        "
                        :href="`/catalog?page=${props.products.current_page - 1}&search=${encodeURIComponent(filters.search)}&fabric_type=${encodeURIComponent(filters.fabric_type)}`"
                        class="rounded-lg border
                               border-slate-300 px-4 py-2
                               text-sm text-slate-700
                               transition hover:bg-slate-50
                               dark:border-slate-600
                               dark:text-slate-300
                               dark:hover:bg-slate-800"
                    >
                        Anterior
                    </Link>

                    <span
                        class="rounded-lg bg-blue-600
                               px-4 py-2 text-sm
                               font-medium text-white"
                    >
                        Página
                        {{ props.products.current_page }}
                    </span>

                    <Link
                        v-if="
                            props.products.current_page <
                            props.products.last_page
                        "
                        :href="`/catalog?page=${props.products.current_page + 1}&search=${encodeURIComponent(filters.search)}&fabric_type=${encodeURIComponent(filters.fabric_type)}`"
                        class="rounded-lg border
                               border-slate-300 px-4 py-2
                               text-sm text-slate-700
                               transition hover:bg-slate-50
                               dark:border-slate-600
                               dark:text-slate-300
                               dark:hover:bg-slate-800"
                    >
                        Siguiente
                    </Link>
                </div>
            </section>
        </div>
    </AppLayout>
</template>