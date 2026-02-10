<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Empleados',
        href: '/employees',
    },
];

const props = defineProps<{
    employees: Array<{
        id: number;
        dni: string;
        name: string;
        area: string;
        phone: string;
        foto_url: string | null;
    }>;
}>();

</script>
<template>
     <Head title="Listar Cliente" />

      <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="overflow-x-auto">
                <div>
                    <Link href="employees/create" class="mb-4 inline-block rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                        Crear Nuevo
                    </Link>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DNI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Área</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="employees in props.employees" :key="employees.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <img v-if="employees.foto_url" :src="employees.foto_url" alt="Foto" class="w-10 h-10 rounded-full object-cover">
                                <img v-else src="/default-avatar.png" alt="Foto por defecto" class="w-10 h-10 rounded-full object-cover">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ employees.dni }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ employees.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ employees.area }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ employees.phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <Link :href="`/employees/${employees.id}/edit`" class="text-blue-600 hover:text-blue-900 mr-4">Editar</Link>
                                <Link :href="`/employees/${employees.id}`" method="delete" as="button" class="text-red-500 hover:text-red-700">Eliminar</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        </AppLayout>
</template>
