<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    stocks: {
        data: Array<{
            id: number;
            warehouse_id: number;
            store_id: number;
            kilos_available: number;
            metros_available: number;
            kilos_reserved: number;
            metros_reserved: number;
            warehouse: { name: string; code: string };
            store: { code_product: string; name_product: string };
        }>;
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
    };
    warehouses: Array<{ id: number; name: string; code: string }>;
    products: Array<{ id: number; code_product: string; name_product: string }>;
    filters: {
        search?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock por almacén', href: '/warehouse-stocks' },
];

const form = useForm({
    warehouse_id: '',
    store_id: '',
    kilos_available: 0,
    metros_available: 0,
});

const selectedProduct = computed(() => {
    if (!form.store_id) {
        return null;
    }

    return props.products.find((product: { id: number; code_product: string; name_product: string }) => product.id === Number(form.store_id)) ?? null;
});

const canSaveStock = computed(() => {
    const hasWarehouse = Boolean(form.warehouse_id);
    const hasProduct = Boolean(form.store_id);
    const hasKilos = Number(form.kilos_available || 0) > 0;
    const hasMetros = Number(form.metros_available || 0) > 0;

    return hasWarehouse && hasProduct && (hasKilos || hasMetros);
});

const productSearch = ref('');
const filters = reactive({
    search: props.filters?.search ?? '',
});

const filteredProducts = computed(() => {
    const term = productSearch.value.trim().toLowerCase();

    if (!term) {
        return props.products;
    }

    return props.products.filter((product: { id: number; code_product: string; name_product: string }) => {
        const haystack = `${product.code_product} ${product.name_product}`.toLowerCase();
        return haystack.includes(term);
    });
});

const clearProductSearch = () => {
    productSearch.value = '';
    form.store_id = '';
};

const saveStock = () => {
    form.post('/warehouse-stocks', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const submitFilters = () => {
    const params = filters.search ? { search: filters.search } : {};

    router.get('/warehouse-stocks', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['stocks', 'filters'],
    });
};

const clearFilters = () => {
    filters.search = '';
    submitFilters();
};

const stockStatus = (stock: { kilos_available: number; metros_available: number }) => {
    const kilos = Number(stock.kilos_available || 0);
    const metros = Number(stock.metros_available || 0);

    if (kilos <= 0 && metros <= 0) {
        return {
            label: 'Sin stock',
            className: 'bg-red-100 text-red-700',
        };
    }

    if (kilos < 1 || metros < 1) {
        return {
            label: 'Stock bajo',
            className: 'bg-amber-100 text-amber-700',
        };
    }

    return {
        label: 'Con stock',
        className: 'bg-emerald-100 text-emerald-700',
    };
};

const page = usePage<any>();

const roles = page.props.auth?.roles ?? [];
const isAdmin = roles.includes('admin');


const removeStock = (stockId: number) => {
    router.delete(`/warehouse-stocks/${stockId}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Stock por almacén" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

            <div v-if="isAdmin" class="rounded border bg-white p-4">
                <h2 class="mb-3 text-lg font-semibold">Asignar stock inicial / actualización</h2>

                <form class="space-y-4" @submit.prevent="saveStock">
                <div class="grid gap-3 md:grid-cols-2">
                    <select v-model="form.warehouse_id" class="rounded border px-3 py-2" required>
                        <option disabled value="">Selecciona almacén</option>
                        <option v-for="warehouse in props.warehouses" :key="warehouse.id" :value="warehouse.id">
                            {{ warehouse.code }} - {{ warehouse.name }}
                        </option>
                    </select>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <input
                                v-model="productSearch"
                                type="text"
                                class="w-full rounded border px-3 py-2"
                                placeholder="Buscar por código o nombre del producto"
                            />
                            <button v-if="productSearch" class="rounded border px-2 py-2 text-sm text-slate-600" type="button" @click="clearProductSearch">
                                Limpiar
                            </button>
                        </div>
                        <select v-model="form.store_id" class="w-full rounded border px-3 py-2" required>
                            <option disabled value="">Selecciona producto</option>
                            <option v-if="filteredProducts.length === 0" disabled value="">
                                No hay productos que coincidan con la búsqueda
                            </option>
                            <option v-for="product in filteredProducts" :key="product.id" :value="product.id">
                                {{ product.code_product }} - {{ product.name_product }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Rollos disponibles</label>
                        <input v-model.number="form.kilos_available" min="0" step="0.001" type="number" class="w-full rounded border px-3 py-2" />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">Metros disponibles</label>
                        <input v-model.number="form.metros_available" min="0" step="0.001" type="number" class="w-full rounded border px-3 py-2" />
                    </div>
                </div>

                <div v-if="selectedProduct" class="rounded border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                    <p class="font-medium">Producto seleccionado:</p>
                    <p>{{ selectedProduct.code_product }} - {{ selectedProduct.name_product }}</p>
                </div>

                <div class="text-sm text-slate-600">
                    <p>Debes seleccionar un producto y asignar al menos un valor mayor a 0 en kilos o metros para guardar.</p>
                </div>

                <div>
                    <button
                        class="rounded bg-blue-600 px-4 py-2 text-white disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="form.processing || !canSaveStock"
                        type="submit"
                    >
                        Guardar stock
                    </button>
                </div>

            </form>
            </div>

            <div class="rounded border bg-white p-4">
                <form class="mb-4 flex flex-col gap-2 md:flex-row md:items-end" @submit.prevent="submitFilters">
                    <div class="flex-1">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Buscar stock</label>
                        <input
                            v-model="filters.search"
                            class="w-full rounded border px-3 py-2"
                            placeholder="Código, nombre o almacén"
                            type="text"
                        />
                    </div>
                    <div class="flex gap-2">
                        <button class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white" type="submit">
                            Buscar
                        </button>
                        <button class="rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700" type="button" @click="clearFilters">
                            Limpiar
                        </button>
                    </div>
                </form>

                <div v-if="props.stocks.data.length === 0" class="rounded border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-600">
                    No hay registros de stock para los filtros seleccionados.
                </div>

                <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Almacén</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Producto</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Stock disponible</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="stock in props.stocks.data" :key="stock.id">
                            <td class="px-4 py-3 text-sm">{{ stock.warehouse.code }} - {{ stock.warehouse.name }}</td>
                            <td class="px-4 py-3 text-sm">{{ stock.store.code_product }} - {{ stock.store.name_product }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex flex-col gap-1">
                                    <span class="font-medium">{{ stock.kilos_available }} kg / {{ stock.metros_available }} m</span>
                                    <span :class="stockStatus(stock).className" class="inline-flex w-fit rounded-full px-2 py-1 text-xs font-semibold">
                                        {{ stockStatus(stock).label }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <button class="text-red-600" @click="removeStock(stock.id)">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>

            <div v-if="props.stocks.total > 0" class="flex items-center justify-between text-sm text-slate-600">
                <span>Mostrando {{ props.stocks.from }} a {{ props.stocks.to }} de {{ props.stocks.total }} registros</span>
                <div class="flex gap-2">
                    <Link v-if="props.stocks.current_page > 1" :href="`/warehouse-stocks?page=${props.stocks.current_page - 1}${filters.search ? `&search=${encodeURIComponent(filters.search)}` : ''}`" class="rounded border px-3 py-1.5 hover:bg-slate-50">Anterior</Link>
                    <span class="rounded bg-blue-600 px-3 py-1.5 text-white">{{ props.stocks.current_page }}</span>
                    <Link v-if="props.stocks.current_page < props.stocks.last_page" :href="`/warehouse-stocks?page=${props.stocks.current_page + 1}${filters.search ? `&search=${encodeURIComponent(filters.search)}` : ''}`" class="rounded border px-3 py-1.5 hover:bg-slate-50">Siguiente</Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
