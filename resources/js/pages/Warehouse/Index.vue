<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    warehouses: {
        data: Array<{
            id: number;
            name: string;
            code: string;
            is_active: boolean;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Almacenes',
        href: '/warehouses',
    },
];

const form = useForm({
    name: '',
    code: '',
    is_active: true,
});

const saveWarehouse = () => {
    form.post('/warehouses', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const toggleWarehouse = (warehouse: {
    id: number;
    name: string;
    code: string;
    is_active: boolean;
}) => {
    router.patch(
        `/warehouses/${warehouse.id}`,
        {
            name: warehouse.name,
            code: warehouse.code,
            is_active: !warehouse.is_active,
        },
        {
            preserveScroll: true,
        },
    );
};

const removeWarehouse = (warehouseId: number) => {
    router.delete(`/warehouses/${warehouseId}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Almacenes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

            <!-- CREAR ALMACÉN -->
            <div
                class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm
                       dark:border-slate-700 dark:bg-slate-900"
            >
                <div class="mb-4">
                    <h2
                        class="text-lg font-semibold text-slate-900
                               dark:text-slate-100"
                    >
                        Nuevo almacén
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500
                               dark:text-slate-400"
                    >
                        Registra un nuevo almacén en el sistema.
                    </p>
                </div>

                <form
                    class="grid gap-4 md:grid-cols-3"
                    @submit.prevent="saveWarehouse"
                >
                    <!-- NOMBRE -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium
                                   text-slate-700 dark:text-slate-300"
                        >
                            Nombre
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Ej. Almacén Central"
                            required
                            class="w-full rounded-lg border border-slate-300
                                   bg-white px-3 py-2 text-sm text-slate-900
                                   outline-none transition
                                   placeholder:text-slate-400
                                   focus:border-blue-500 focus:ring-2
                                   focus:ring-blue-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-100
                                   dark:placeholder:text-slate-500
                                   dark:focus:border-blue-400"
                        />

                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-600
                                   dark:text-red-400"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- CÓDIGO -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium
                                   text-slate-700 dark:text-slate-300"
                        >
                            Código
                        </label>

                        <input
                            v-model="form.code"
                            type="text"
                            placeholder="Ej. ALM-001"
                            required
                            class="w-full rounded-lg border border-slate-300
                                   bg-white px-3 py-2 text-sm text-slate-900
                                   outline-none transition
                                   placeholder:text-slate-400
                                   focus:border-blue-500 focus:ring-2
                                   focus:ring-blue-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-100
                                   dark:placeholder:text-slate-500
                                   dark:focus:border-blue-400"
                        />

                        <p
                            v-if="form.errors.code"
                            class="mt-1 text-sm text-red-600
                                   dark:text-red-400"
                        >
                            {{ form.errors.code }}
                        </p>
                    </div>

                    <!-- ESTADO -->
                    <div class="flex items-end">
                        <label
                            class="flex cursor-pointer items-center gap-3
                                   rounded-lg border border-slate-200
                                   px-4 py-2.5 text-sm text-slate-700
                                   dark:border-slate-700
                                   dark:text-slate-300"
                        >
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300
                                       text-blue-600 focus:ring-blue-500
                                       dark:border-slate-600
                                       dark:bg-slate-800"
                            />

                            <span>Activo</span>
                        </label>
                    </div>

                    <!-- BOTÓN -->
                    <div class="md:col-span-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-5 py-2.5
                                   text-sm font-medium text-white
                                   shadow-sm transition
                                   hover:bg-blue-700
                                   focus:outline-none focus:ring-2
                                   focus:ring-blue-500 focus:ring-offset-2
                                   disabled:cursor-not-allowed
                                   disabled:opacity-50
                                   dark:focus:ring-offset-slate-900"
                        >
                            {{
                                form.processing
                                    ? 'Guardando...'
                                    : 'Guardar almacén'
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- LISTADO -->
            <div
                class="overflow-hidden rounded-xl border
                       border-slate-200 bg-white shadow-sm
                       dark:border-slate-700 dark:bg-slate-900"
            >
                <div
                    class="border-b border-slate-200 px-5 py-4
                           dark:border-slate-700"
                >
                    <h2
                        class="text-lg font-semibold text-slate-900
                               dark:text-slate-100"
                    >
                        Almacenes registrados
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500
                               dark:text-slate-400"
                    >
                        Administra el estado de los almacenes.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs
                                           font-semibold uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Nombre
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs
                                           font-semibold uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Código
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs
                                           font-semibold uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Estado
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-xs
                                           font-semibold uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-200
                                   bg-white dark:divide-slate-700
                                   dark:bg-slate-900"
                        >
                            <tr
                                v-for="warehouse in props.warehouses.data"
                                :key="warehouse.id"
                                class="transition hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
                                <!-- NOMBRE -->
                                <td
                                    class="px-4 py-3 text-sm font-medium
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    {{ warehouse.name }}
                                </td>

                                <!-- CÓDIGO -->
                                <td
                                    class="px-4 py-3 text-sm
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    {{ warehouse.code }}
                                </td>

                                <!-- ESTADO -->
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        :class="
                                            warehouse.is_active
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400'
                                                : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400'
                                        "
                                        class="inline-flex rounded-full px-2.5 py-1
                                               text-xs font-medium"
                                    >
                                        {{
                                            warehouse.is_active
                                                ? 'Activo'
                                                : 'Inactivo'
                                        }}
                                    </span>
                                </td>

                                <!-- ACCIONES -->
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex flex-wrap gap-3">
                                        <button
                                            type="button"
                                            class="font-medium text-blue-600
                                                   hover:text-blue-800
                                                   dark:text-blue-400
                                                   dark:hover:text-blue-300"
                                            @click="
                                                toggleWarehouse(warehouse)
                                            "
                                        >
                                            {{
                                                warehouse.is_active
                                                    ? 'Desactivar'
                                                    : 'Activar'
                                            }}
                                        </button>

                                        <button
                                            type="button"
                                            class="font-medium text-red-600
                                                   hover:text-red-800
                                                   dark:text-red-400
                                                   dark:hover:text-red-300"
                                            @click="
                                                removeWarehouse(
                                                    warehouse.id,
                                                )
                                            "
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    props.warehouses.data.length === 0
                                "
                            >
                                <td
                                    colspan="4"
                                    class="px-4 py-8 text-center text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay almacenes registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>