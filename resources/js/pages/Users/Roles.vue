<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

interface UserRow {
    id: number;
    name: string;
    email: string;
    role: string | null;
    warehouse_ids: number[];
}

interface WarehouseOption {
    id: number;
    name: string;
    code: string;
}

const props = defineProps<{
    users: UserRow[];
    roles: string[];
    warehouses: WarehouseOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles de usuarios',
        href: '/users/roles',
    },
];

const editableUsers = reactive(
    props.users.map((user) => ({
        ...user,
        selectedRole: user.role ?? '',
        selectedWarehouses: user.warehouse_ids.map(String),
    })),
);

const updateUserAccess = (userId: number) => {
    const row = editableUsers.find((item) => item.id === userId);
    if (!row || !row.selectedRole) {
        return;
    }

    router.patch(
        `/users/${userId}/roles`,
        {
            role: row.selectedRole,
            warehouse_ids: row.selectedWarehouses.map((value) => Number(value)),
        },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head title="Gestión de roles" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="rounded-lg border bg-white p-4 shadow-sm">
                <h1 class="mb-1 text-lg font-semibold">Asignar roles y almacenes</h1>
                <p class="mb-4 text-sm text-gray-600">
                    Puedes definir el rol del usuario y los almacenes que puede operar.
                </p>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Correo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Rol</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Almacenes asignados</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="user in editableUsers" :key="user.id">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ user.name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ user.email }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <select
                                    v-model="user.selectedRole"
                                    class="rounded border px-3 py-2"
                                >
                                    <option value="" disabled>Seleccionar rol</option>
                                    <option v-for="role in props.roles" :key="role" :value="role">
                                        {{ role }}
                                    </option>
                                </select>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <select
                                    v-model="user.selectedWarehouses"
                                    class="min-w-64 rounded border px-3 py-2"
                                    multiple
                                >
                                    <option v-for="warehouse in props.warehouses" :key="warehouse.id" :value="String(warehouse.id)">
                                        {{ warehouse.code }} - {{ warehouse.name }}
                                    </option>
                                </select>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <button
                                    class="rounded bg-blue-600 px-3 py-2 text-white"
                                    @click="updateUserAccess(user.id)"
                                >
                                    Guardar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
