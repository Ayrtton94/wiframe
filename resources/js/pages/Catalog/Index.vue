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
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <section class="rounded-xl border bg-white p-5 shadow-sm">
                <div
                    class="mb-4 flex flex-col gap-2 md:flex-row md:items-end md:justify-between"
                >
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">
                            Catálogo de productos
                        </h1>
                        <p class="text-sm text-slate-500">
                            Busca por código, nombre o tipo de tela y revisa el
                            detalle del producto.
                        </p>
                    </div>

                    <div
                        class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700"
                    >
                        {{ props.products.total }} productos encontrados
                    </div>
                </div>

                <form
                    class="grid gap-3 md:grid-cols-[2fr_1fr_auto_auto]"
                    @submit.prevent="submitFilters"
                >
                    <input
                        v-model="filters.search"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:outline-none"
                        placeholder="Buscar por código o nombre"
                        type="text"
                    />

                    <input
                        v-model="filters.fabric_type"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:outline-none"
                        placeholder="Tipo de tela"
                        type="text"
                    />

                    <button
                        class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
                        type="submit"
                    >
                        Buscar
                    </button>

                    <button
                        class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        type="button"
                        @click="clearFilters"
                    >
                        Limpiar
                    </button>
                </form>
            </section>

            <section
                class="overflow-hidden rounded-xl border bg-white shadow-sm"
            >
                <div v-if="hasResults" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Imagen
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Código
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Producto
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Tipo de tela
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Color
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Proveedor
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Precios
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase"
                                >
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="product in props.products.data"
                                :key="product.id"
                                class="hover:bg-slate-50/70"
                            >
                                <td class="px-4 py-3">
                                    <img
                                        v-if="product.image_url"
                                        :src="product.image_url"
                                        :alt="product.name_product"
                                        class="h-14 w-14 rounded-lg object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-14 w-14 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-500"
                                    >
                                        Sin imagen
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 text-sm font-medium text-slate-900"
                                >
                                    {{ product.code_product }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">
                                    {{ product.name_product }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">
                                    {{ product.fabric_type }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">
                                    {{ product.color }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">
                                    {{ product.proveedor }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">
                                    <div>
                                        Público: S/ {{ product.public_price }}
                                    </div>
                                    <div>
                                        Mayorista: S/
                                        {{ product.wholesale_price }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="stockBadgeClass(product)"
                                    >
                                        Disponible para consulta
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <Link
                                        :href="`/catalog/${product.id}`"
                                        class="inline-flex rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-700"
                                    >
                                        Ver detalle
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center gap-2 px-6 py-14 text-center"
                >
                    <h2 class="text-lg font-semibold text-slate-900">
                        No se encontraron productos
                    </h2>
                    <p class="max-w-md text-sm text-slate-500">
                        Intenta cambiar los filtros de búsqueda para encontrar
                        un producto del catálogo.
                    </p>
                </div>
            </section>

            <section
                class="flex flex-col gap-3 rounded-xl border bg-white p-4 shadow-sm md:flex-row md:items-center md:justify-between"
            >
                <p class="text-sm text-slate-600">
                    Mostrando {{ props.products.from ?? 0 }} a
                    {{ props.products.to ?? 0 }} de
                    {{ props.products.total }} registros.
                </p>

                <div class="flex items-center gap-2">
                    <Link
                        v-if="props.products.current_page > 1"
                        :href="`/catalog?page=${props.products.current_page - 1}&search=${encodeURIComponent(filters.search)}&fabric_type=${encodeURIComponent(filters.fabric_type)}`"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                    >
                        Anterior
                    </Link>
                    <span
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white"
                    >
                        Página {{ props.products.current_page }}
                    </span>
                    <Link
                        v-if="
                            props.products.current_page <
                            props.products.last_page
                        "
                        :href="`/catalog?page=${props.products.current_page + 1}&search=${encodeURIComponent(filters.search)}&fabric_type=${encodeURIComponent(filters.fabric_type)}`"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                    >
                        Siguiente
                    </Link>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
