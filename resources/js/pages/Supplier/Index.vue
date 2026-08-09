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
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">

        <!-- Encabezado -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Proveedores
                </h1>
                <p class="text-sm text-slate-500">
                    Administra y consulta tus proveedores registrados.
                </p>
            </div>

            <Link
                href="/suppliers/create"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                + Crear Nuevo
            </Link>
        </div>

        <!-- Contenedor principal -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <!-- Filtros -->
            <div class="border-b border-slate-200 bg-slate-50/70 p-5">
                <form
                    class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    @submit.prevent="submitFilters"
                >
                    <div class="relative flex-1">
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Buscar por nombre o RUC..."
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
                            class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300"
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
                                RUC
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Razón Social
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Rubro / Categoría
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Teléfono Principal
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Correo Electrónico
                            </th>

                            <th class="whitespace-nowrap px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-600">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr
                            v-for="supplier in props.suppliers.data"
                            :key="supplier.id"
                            class="transition hover:bg-slate-50"
                        >
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-800">
                                {{ supplier.ruc }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                {{ supplier.company_name }}
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ supplier.category }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ supplier.phone }}
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ supplier.email }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                                <div class="flex items-center justify-center gap-3">
                                    <Link
                                        :href="`/suppliers/${supplier.id}`"
                                        class="font-medium text-green-600 transition hover:text-green-800"
                                    >
                                        Ver
                                    </Link>

                                    <Link
                                        :href="`/suppliers/${supplier.id}/edit`"
                                        class="font-medium text-blue-600 transition hover:text-blue-800"
                                    >
                                        Editar
                                    </Link>

                                    <button
                                        @click="deleteSupplier(supplier.id)"
                                        type="button"
                                        class="cursor-pointer font-medium text-red-600 transition hover:text-red-800"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Sin resultados -->
                        <tr v-if="props.suppliers.data.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-12 text-center"
                            >
                                <div class="flex flex-col items-center justify-center">
                                    <div class="mb-3 rounded-full bg-slate-100 p-4">
                                        <span class="text-2xl">📋</span>
                                    </div>

                                    <p class="text-sm font-medium text-slate-700">
                                        No se encontraron proveedores
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

            <!-- Footer de la tabla -->
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                <div class="flex flex-col gap-2 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                    <span>
                        Mostrando
                        <span class="font-medium text-slate-700">
                            {{ props.suppliers.data.length }}
                        </span>
                        proveedores
                    </span>
                </div>
            </div>

        </div>
    </div>
</AppLayout>
```

</template>
