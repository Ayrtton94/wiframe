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
     ```vue
<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">

        <!-- Encabezado -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Empleados
                </h1>

                <p class="text-sm text-slate-500">
                    Administra la información de los empleados registrados.
                </p>
            </div>

            <Link
                href="/employees/create"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                + Crear Nuevo
            </Link>
        </div>

        <!-- Card principal -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">

                    <!-- Encabezado de tabla -->
                    <thead class="bg-slate-100">
                        <tr>
                            <th
                                class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600"
                            >
                                Número de Identificación
                            </th>

                            <th
                                class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600"
                            >
                                Nombre
                            </th>

                            <th
                                class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600"
                            >
                                Área
                            </th>

                            <th
                                class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600"
                            >
                                Teléfono
                            </th>

                            <th
                                class="whitespace-nowrap px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-600"
                            >
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <!-- Datos -->
                    <tbody class="divide-y divide-slate-200 bg-white">

                        <tr
                            v-for="employee in props.employees"
                            :key="employee.id"
                            class="transition hover:bg-slate-50"
                        >
                            <!-- Identificación -->
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-800">
                                {{ employee.dni }}
                            </td>

                            <!-- Nombre -->
                            <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                {{ employee.name }}
                            </td>

                            <!-- Área -->
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ employee.area }}
                            </td>

                            <!-- Teléfono -->
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ employee.phone }}
                            </td>

                            <!-- Acciones -->
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">

                                    <Link
                                        :href="`/employees/${employee.id}/edit`"
                                        class="font-medium text-blue-600 transition hover:text-blue-800"
                                    >
                                        Editar
                                    </Link>

                                    <Link
                                        :href="`/employees/${employee.id}`"
                                        method="delete"
                                        as="button"
                                        class="font-medium text-red-600 transition hover:text-red-800"
                                    >
                                        Eliminar
                                    </Link>

                                </div>
                            </td>
                        </tr>

                        <!-- Sin resultados -->
                        <tr v-if="props.employees.length === 0">
                            <td
                                colspan="5"
                                class="px-6 py-14 text-center"
                            >
                                <div class="flex flex-col items-center justify-center">

                                    <div class="mb-3 rounded-full bg-slate-100 p-4">
                                        <span class="text-2xl">👤</span>
                                    </div>

                                    <p class="text-sm font-medium text-slate-700">
                                        No hay empleados registrados
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Los empleados que registres aparecerán aquí.
                                    </p>

                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                <p class="text-sm text-slate-500">
                    Total de empleados:
                    <span class="font-semibold text-slate-700">
                        {{ props.employees.length }}
                    </span>
                </p>
            </div>

        </div>
    </div>
</AppLayout>
```

</template>
