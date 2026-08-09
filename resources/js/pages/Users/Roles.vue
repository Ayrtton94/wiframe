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

const onRoleChange = (user: any) => {
    if (!['almacen', 'tienda'].includes(user.selectedRole)) {
        user.selectedWarehouses = [];
    }
};

const updateUserAccess = (userId: number) => {
    const row = editableUsers.find((item) => item.id === userId);

    if (!row || !row.selectedRole) return;

    if (
        ['almacen', 'tienda'].includes(row.selectedRole) &&
        row.selectedWarehouses.length === 0
    ) {
        alert('Debes seleccionar al menos un almacén');
        return;
    }

    router.patch(
        `/users/${userId}/roles`,
        {
            role: row.selectedRole,
            warehouse_ids: row.selectedWarehouses.map(Number),
        },
        {
            preserveScroll: true,
        },
    );
};

</script>

<template>
    <Head title="Gestión de roles" />
    ```vue
<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">

        <!-- Encabezado -->
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Asignar roles y almacenes
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Define el rol de cada usuario y los almacenes que puede operar.
            </p>
        </div>

        <!-- Card principal -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <!-- Cabecera de la tabla -->
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-700">
                    Usuarios del sistema
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Modifica los permisos de acceso y guarda los cambios.
                </p>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-100">
                        <tr>
                            <th
                                class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600"
                            >
                                Usuario
                            </th>

                            <th
                                class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600"
                            >
                                Correo
                            </th>

                            <th
                                class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600"
                            >
                                Rol
                            </th>

                            <th
                                class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600"
                            >
                                Almacenes asignados
                            </th>

                            <th
                                class="whitespace-nowrap px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-600"
                            >
                                Acción
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">

                        <tr
                            v-for="user in editableUsers"
                            :key="user.id"
                            class="transition hover:bg-slate-50"
                        >

                            <!-- Usuario -->
                            <td class="whitespace-nowrap px-6 py-5">
                                <div class="flex items-center gap-3">

                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700"
                                    >
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>

                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">
                                            {{ user.name }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            ID: {{ user.id }}
                                        </p>
                                    </div>

                                </div>
                            </td>

                            <!-- Correo -->
                            <td class="whitespace-nowrap px-6 py-5">
                                <span class="text-sm text-slate-600">
                                    {{ user.email }}
                                </span>
                            </td>

                            <!-- Rol -->
                            <td class="whitespace-nowrap px-6 py-5">
                                <select
                                    v-model="user.selectedRole"
                                    @change="onRoleChange(user)"
                                    class="w-full min-w-44 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                >
                                    <option
                                        value=""
                                        disabled
                                    >
                                        Seleccionar rol
                                    </option>

                                    <option
                                        v-for="role in props.roles"
                                        :key="role"
                                        :value="role"
                                    >
                                        {{ role }}
                                    </option>
                                </select>
                            </td>

                            <!-- Almacenes -->
                            <td class="px-6 py-5">
                                <select
                                    v-model="user.selectedWarehouses"
                                    multiple
                                    class="min-h-28 w-full min-w-72 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                >
                                    <option
                                        v-for="warehouse in props.warehouses"
                                        :key="warehouse.id"
                                        :value="String(warehouse.id)"
                                        class="py-1"
                                    >
                                        {{ warehouse.code }} - {{ warehouse.name }}
                                    </option>
                                </select>

                                <p class="mt-2 text-xs text-slate-400">
                                    Mantén presionado Ctrl para seleccionar varios.
                                </p>
                            </td>

                            <!-- Guardar -->
                            <td class="whitespace-nowrap px-6 py-5 text-center">
                                <button
                                    type="button"
                                    @click="updateUserAccess(user.id)"
                                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                >
                                    Guardar
                                </button>
                            </td>

                        </tr>

                        <!-- Sin usuarios -->
                        <tr v-if="editableUsers.length === 0">
                            <td
                                colspan="5"
                                class="px-6 py-14 text-center"
                            >
                                <div class="flex flex-col items-center justify-center">

                                    <div class="mb-3 rounded-full bg-slate-100 p-4">
                                        <span class="text-2xl">👥</span>
                                    </div>

                                    <p class="text-sm font-medium text-slate-700">
                                        No hay usuarios disponibles
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Los usuarios registrados aparecerán aquí.
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
                    Total de usuarios:
                    <span class="font-semibold text-slate-700">
                        {{ editableUsers.length }}
                    </span>
                </p>
            </div>

        </div>
    </div>
</AppLayout>
```

</template>
