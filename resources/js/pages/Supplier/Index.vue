<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Proveedores',
        href: '/suppliers',
    },
];

const props = defineProps<{
    suppliers: Array<{
        id: number;
        ruc: string;
        company_name: string;
        category: string;
        phone: string;
        email: string;
    }>;
}>();

// DEBUG: ver qué datos recibe el componente
console.log('Index.vue suppliers:', props.suppliers);

const deleteSupplier = (id: number) => {
    if (confirm('¿Estás seguro de que deseas eliminar este proveedor?')) {
        router.delete(`/suppliers/${id}`);
    }
};


</script>
<template>
     <Head title="Listar Proveedores" />

      <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="overflow-x-auto">
                <!-- DEBUG: muestra cuántos proveedores hay -->
                <!--<div class="bg-blue-100 p-2 mb-4 rounded">Total proveedores: {{ props.suppliers?.length ?? 0 }}</div>-->
                
                <div>
                    <Link href="suppliers/create" class="mb-4 inline-block rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                        Crear Nuevo
                    </Link>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RUC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razon Social</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rubro/Categoria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono Principal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo Electrónico</th>  
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>                          
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="supplier in props.suppliers" :key="supplier.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ supplier.ruc }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ supplier.company_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ supplier.category }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ supplier.phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ supplier.email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <Link :href="`/suppliers/${supplier.id}/edit`" class="text-blue-500 hover:text-blue-700 mr-2">Editar</Link>
                                <button @click="deleteSupplier(supplier.id)" class="text-red-500 hover:text-red-700 cursor-pointer">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                    </table>
                </div>
        </div>
    </AppLayout>
</template>
