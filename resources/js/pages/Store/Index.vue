<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

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
            minimum_stock: number;
            kilos: number | string;
            metros: number | string;
            image_url: string | null;
            is_active: boolean;

            warehouse_stocks: Array<{
                id: number;
                warehouse_id: number;
                warehouse_name: string | null;
                kilos_available: number | string;
                metros_available: number | string;
                kilos_reserved: number | string;
                metros_reserved: number | string;
            }>;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Almacen',
        href: '/stores',
    },
];

const isImportDialogOpen = ref(false);
const filters = reactive({
    search: props.filters.search ?? '',
});
const importForm = useForm<{
    file: File | null;
}>({
    file: null,
});

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    importForm.file = target.files?.[0] ?? null;
};

const toggleProductStatus = (product: { id: number; is_active: boolean }) => {
    const action = product.is_active ? 'desactivar' : 'activar';

    if (confirm(`¿Seguro que deseas ${action} este producto?`)) {
        router.patch(`/stores/${product.id}/toggle-status`, {}, { preserveScroll: true });
    }
};

const submitImport = () => {
    importForm.post('/stores/import', {
        forceFormData: true,
        onSuccess: () => {
            importForm.reset();
            isImportDialogOpen.value = false;
        },
    });
};

const submitFilters = () => {
    const params = filters.search ? { search: filters.search } : {};

    router.get('/stores', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['products'],
    });
};

const clearFilters = () => {
    filters.search = '';
    submitFilters();
};

</script>

<template>
    <Head title="Inventario de Productos" />
    ```vue
<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">

        <!-- Encabezado -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Productos
                </h1>

                <p class="text-sm text-slate-500">
                    Administra el inventario, precios y estado de tus productos.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Dialog v-model:open="isImportDialogOpen">
                    <DialogTrigger as-child>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                        >
                            Importar Excel
                        </button>
                    </DialogTrigger>

                    <DialogContent class="sm:max-w-lg">
                        <DialogHeader>
                            <DialogTitle>
                                Importar productos
                            </DialogTitle>

                            <DialogDescription>
                                Sube un archivo CSV exportado desde Excel para crear o actualizar productos en la base de datos.
                            </DialogDescription>
                        </DialogHeader>

                        <form
                            class="space-y-5"
                            @submit.prevent="submitImport"
                        >
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Archivo CSV
                                </label>

                                <input
                                    accept=".csv,.txt"
                                    type="file"
                                    @change="handleFileChange"
                                    class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200"
                                />

                                <p class="mt-2 text-xs leading-5 text-slate-500">
                                    Columnas requeridas:
                                    code_product, name_product, fabric_type, color,
                                    proveedor, kilos, metros, minimum_stock, price,
                                    public_price, wholesale_price, price_roll,
                                    special_price, location, description.
                                </p>

                                <InputError
                                    :message="importForm.errors.file"
                                    class="mt-2"
                                />
                            </div>

                            <DialogFooter>
                                <button
                                    type="button"
                                    @click="isImportDialogOpen = false"
                                    class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                >
                                    Cancelar
                                </button>

                                <button
                                    type="submit"
                                    :disabled="importForm.processing || !importForm.file"
                                    class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        importForm.processing
                                            ? 'Importando...'
                                            : 'Importar'
                                    }}
                                </button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                <Link
                    href="/stores/create"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    + Crear Nuevo
                </Link>

                <a
                    href="/stores/export"
                    class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                >
                    Exportar productos
                </a>
            </div>
        </div>

        <!-- Card principal -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <!-- Filtros -->
            <div class="border-b border-slate-200 bg-slate-50/70 p-5">
                <form
                    class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    @submit.prevent="submitFilters"
                >
                    <div class="flex-1">
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Buscar por código, nombre o proveedor..."
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        />
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Buscar
                        </button>

                        <button
                            type="button"
                            @click="clearFilters"
                            class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                        >
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-100">
                        <tr>
                            <th class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Imagen
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Código
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Nombre
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Tipo de Tela
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Color
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Proveedor
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Ubicación
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Precio
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Rollos
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Metros
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Kilos
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Estado
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">

                        <tr
                            v-for="product in products.data"
                            :key="product.id"
                            class="transition hover:bg-slate-50"
                        >
                            <!-- Imagen -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <img
                                    v-if="product.image_url"
                                    :src="product.image_url"
                                    alt="Imagen producto"
                                    class="h-12 w-12 rounded-lg border border-slate-200 object-cover shadow-sm"
                                />

                                <div
                                    v-else
                                    class="flex h-12 w-12 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-400"
                                    aria-hidden="true"
                                >
                                    —
                                </div>
                            </td>

                            <!-- Código -->
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-800">
                                {{ product.code_product }}
                            </td>

                            <!-- Nombre -->
                            <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                {{ product.name_product }}
                            </td>

                            <!-- Tela -->
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ product.fabric_type }}
                            </td>

                            <!-- Color -->
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ product.color }}
                            </td>

                            <!-- Proveedor -->
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ product.proveedor }}
                            </td>                           

                            <!-- Ubicacion -->
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{
                                    product.warehouse_stocks
                                        .map(stock => stock.warehouse_name)
                                        .filter(Boolean)
                                        .join(', ') || '-'
                                }}
                            </td>

                            <!-- Precio -->
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-slate-700">
                                ${{ product.price }}
                            </td>

                            <!-- Kilos = rollos -->
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-slate-600">
                                {{ product.kilos }}
                            </td>

                            <!-- Metros es metros -->
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-slate-600">
                                {{ product.metros }}
                            </td>

                            <!-- Stock minimo = kilos -->
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ product.minimum_stock }}
                            </td>

                            <!-- Estado -->
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <span
                                    :class="
                                        product.is_active
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700'
                                    "
                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                >
                                    {{
                                        product.is_active
                                            ? 'Activo'
                                            : 'Desactivado'
                                    }}
                                </span>
                            </td>

                            <!-- Acciones -->
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <Link
                                        :href="`/stores/${product.id}`"
                                        class="font-medium text-green-600 transition hover:text-green-800"
                                    >
                                        Ver
                                    </Link>

                                    <Link
                                        :href="`/stores/${product.id}/edit`"
                                        class="font-medium text-blue-600 transition hover:text-blue-800"
                                    >
                                        Editar
                                    </Link>

                                    <button
                                        type="button"
                                        class="font-medium transition"
                                        :class="
                                            product.is_active
                                                ? 'text-red-600 hover:text-red-800'
                                                : 'text-green-600 hover:text-green-800'
                                        "
                                        @click="toggleProductStatus(product)"
                                    >
                                        {{
                                            product.is_active
                                                ? 'Desactivar'
                                                : 'Activar'
                                        }}
                                    </button>

                                </div>
                            </td>
                        </tr>

                        <!-- Sin resultados -->
                        <tr v-if="products.data.length === 0">
                            <td
                                colspan="13"
                                class="px-6 py-14 text-center"
                            >
                                <div class="flex flex-col items-center justify-center">
                                    <div class="mb-3 rounded-full bg-slate-100 p-4">
                                        <span class="text-2xl">📦</span>
                                    </div>

                                    <p class="text-sm font-medium text-slate-700">
                                        No se encontraron productos
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Intenta realizar otra búsqueda.
                                    </p>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="flex flex-col gap-4 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">

                <div class="text-sm text-slate-500">
                    Mostrando
                    <span class="font-semibold text-slate-700">
                        {{ products.from }}
                    </span>
                    a
                    <span class="font-semibold text-slate-700">
                        {{ products.to }}
                    </span>
                    de
                    <span class="font-semibold text-slate-700">
                        {{ products.total }}
                    </span>
                    resultados
                </div>

                <div class="flex items-center gap-2">

                    <Link
                        v-if="products.current_page > 1"
                        :href="`/stores?page=${products.current_page - 1}${filters.search ? `&search=${encodeURIComponent(filters.search)}` : ''}`"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-100"
                    >
                        ← Anterior
                    </Link>

                    <span
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm"
                    >
                        {{ products.current_page }}
                    </span>

                    <Link
                        v-if="products.current_page < products.last_page"
                        :href="`/stores?page=${products.current_page + 1}${filters.search ? `&search=${encodeURIComponent(filters.search)}` : ''}`"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-100"
                    >
                        Siguiente →
                    </Link>

                </div>
            </div>

        </div>
    </div>
</AppLayout>
```

</template>
