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
    { title: 'Almacenes', href: '/warehouses' },
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

const toggleWarehouse = (warehouse: { id: number; name: string; code: string; is_active: boolean }) => {
    router.patch(
        `/warehouses/${warehouse.id}`,
        {
            name: warehouse.name,
            code: warehouse.code,
            is_active: !warehouse.is_active,
        },
        { preserveScroll: true },
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
            <div class="rounded border bg-white p-4">
                <h2 class="mb-3 text-lg font-semibold">Nuevo almacén</h2>
                <form class="grid gap-3 md:grid-cols-3" @submit.prevent="saveWarehouse">
                    <input v-model="form.name" class="rounded border px-3 py-2" placeholder="Nombre" required />
                    <input v-model="form.code" class="rounded border px-3 py-2" placeholder="Código" required />
                    <label class="flex items-center gap-2 text-sm">
                        <input v-model="form.is_active" type="checkbox" />
                        Activo
                    </label>
                    <button class="w-fit rounded bg-blue-600 px-3 py-2 text-white" :disabled="form.processing" type="submit">
                        Guardar
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto rounded border bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Nombre</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Código</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Estado</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="warehouse in props.warehouses.data" :key="warehouse.id">
                            <td class="px-4 py-3 text-sm">{{ warehouse.name }}</td>
                            <td class="px-4 py-3 text-sm">{{ warehouse.code }}</td>
                            <td class="px-4 py-3 text-sm">{{ warehouse.is_active ? 'Activo' : 'Inactivo' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <button class="mr-3 text-blue-600" @click="toggleWarehouse(warehouse)">
                                    {{ warehouse.is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                                <button class="text-red-600" @click="removeWarehouse(warehouse.id)">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
