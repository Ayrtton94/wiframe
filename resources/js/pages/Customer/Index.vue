<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clientes',
        href: '/customers',
    },
];

const props = defineProps<{
    customers: Array<{
        id: number;
        name: string;
        email: string;
        phone: string;
        position: string;
        dni: string;
    }>;
}>();

</script>
<template>
     <Head title="Listar Cliente" />

      <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="overflow-x-auto">
                <div>
                    <Link href="customers/create" class="mb-4 inline-block rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                        Crear Nuevo
                    </Link>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RUC/DNI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razon Social/Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefono</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo Electronico</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="customer in props.customers" :key="customer.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ customer.dni }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ customer.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ customer.phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ customer.email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ customer.position }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="`/customers/${customer.id}/edit`" class="text-blue-600 hover:text-blue-900 mr-4">Editar</Link>
                                <Link :href="`/customers/${customer.id}`" method="delete" as="button" class="text-red-600 hover:text-red-900">Eliminar</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        </AppLayout>
</template>
