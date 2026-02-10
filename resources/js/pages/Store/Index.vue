<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Almacen',
        href: '/stores',
    },
];

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

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
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
                                <Link :href="`/stores/${product.id}`" method="delete" as="button" class="text-red-500 hover:text-red-700">Eliminar</Link>
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
