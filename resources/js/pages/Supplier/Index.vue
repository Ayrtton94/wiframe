<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    suppliers: {
        data: Array<{
            id: number;
            ruc: string;
            company_name: string;
            category: string;
            phone: string;
            email: string;
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
        title: 'Proveedores',
        href: '/suppliers',
    },
];

const filters = reactive({
    search: props.filters.search ?? '',
});

const deleteSupplier = (id: number) => {
    if (confirm('¿Estás seguro de que deseas eliminar este proveedor?')) {
        router.delete(`/suppliers/${id}`);
    }
};

const submitFilters = () => {
    const params = filters.search ? { search: filters.search } : {};

    router.get('/suppliers', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['suppliers', 'filters'],
    });
};

const clearFilters = () => {
    filters.search = '';
    submitFilters();
};


</script>
<template>
     <Head title="Listar Proveedores" />

      <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="overflow-x-auto">
                <div class="flex flex-1 flex-col gap-2 md:flex-row md:items-end">
                        <form class="flex flex-1 flex-col gap-2 md:flex-row" @submit.prevent="submitFilters">
                            <input
                                v-model="filters.search"
                                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:outline-none"
                                placeholder="Buscar por nombre o RUC"
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
                    </div>                
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
                        <tr v-for="supplier in props.suppliers.data" :key="supplier.id">
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
