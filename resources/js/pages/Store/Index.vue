<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Almacen',
        href: '/stores',
    },
];

defineProps<{
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
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
    };
}>();

const isImportDialogOpen = ref(false);
const importForm = useForm<{
    file: File | null;
}>({
    file: null,
});

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    importForm.file = target.files?.[0] ?? null;
};

const submitImport = () => {
    importForm.post('/catalog/import', {
        forceFormData: true,
        onSuccess: () => {
            importForm.reset();
            isImportDialogOpen.value = false;
        },
    });
};

</script>

<template>
    <Head title="Inventario de Productos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="overflow-x-auto">
                <div>
                    <Link href="stores/create" class="mb-4 inline-block rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                        Crear Nuevo
                    </Link>
                </div>
                                <div class="mb-4 flex justify-end">
                    <Dialog v-model:open="isImportDialogOpen">
                        <DialogTrigger as-child>
                            <button
                                class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700"
                                type="button"
                            >
                                Importar Excel (CSV)
                            </button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-lg">
                            <DialogHeader>
                                <DialogTitle>Importar productos</DialogTitle>
                                <DialogDescription>
                                    Sube un archivo CSV exportado desde Excel para crear o actualizar productos en la base de datos.
                                </DialogDescription>
                            </DialogHeader>

                            <form class="space-y-3" @submit.prevent="submitImport">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-slate-700">
                                        Archivo CSV
                                    </label>
                                    <input
                                        accept=".csv,.txt"
                                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                        type="file"
                                        @change="handleFileChange"
                                    />
                                    <p class="mt-2 text-xs text-slate-500">
                                        Columnas requeridas: code_product, name_product, fabric_type, color, proveedor, kilos, metros, minimum_stock, price, public_price, wholesale_price, price_roll, special_price, location, description.
                                    </p>
                                    <InputError :message="importForm.errors.file" class="mt-2" />
                                </div>

                                <DialogFooter>
                                    <button
                                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                                        type="button"
                                        @click="isImportDialogOpen = false"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        :disabled="importForm.processing || !importForm.file"
                                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        type="submit"
                                    >
                                        {{ importForm.processing ? 'Importando...' : 'Importar' }}
                                    </button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Tela</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Mayorista</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Minorista</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="product in products.data" :key="product.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <img v-if="product.image_url" :src="product.image_url" alt="Imagen producto" class="h-12 w-12 rounded object-cover">
                                <div v-else class="flex h-12 w-12 items-center justify-center rounded bg-gray-100 text-[10px] text-gray-500">Sin imagen</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ product.code_product }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.name_product }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.fabric_type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.color }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.proveedor }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ product.price }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ product.wholesale_price }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ product.public_price }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <Link :href="`/stores/${product.id}/edit`" class="text-blue-500 hover:text-blue-700 mr-2">Editar</Link>                               
                            </td>
                        </tr>
                    </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Mostrando {{ products.from }} a {{ products.to }} de {{ products.total }} resultados
                    </div>
                    <div class="flex space-x-2">
                        <Link
                            v-if="products.current_page > 1"
                            :href="`/stores?page=${products.current_page - 1}`"
                            class="px-3 py-2 text-sm border rounded bg-white text-gray-700 border-gray-300 hover:bg-gray-50"
                        >
                            Anterior
                        </Link>
                        <span class="px-3 py-2 text-sm border rounded bg-blue-500 text-white border-blue-500">
                            {{ products.current_page }}
                        </span>
                        <Link
                            v-if="products.current_page < products.last_page"
                            :href="`/stores?page=${products.current_page + 1}`"
                            class="px-3 py-2 text-sm border rounded bg-white text-gray-700 border-gray-300 hover:bg-gray-50"
                        >
                            Siguiente
                        </Link>
                    </div>
                </div>
        </div>
    </AppLayout>
</template>
