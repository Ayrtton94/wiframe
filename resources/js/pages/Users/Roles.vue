<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';

interface UserRow {
    id: number;
    name: string;
    email: string;
    role: string | null;
}

const props = defineProps<{
    users: UserRow[];
    roles: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles de usuarios',
        href: '/users/roles',
    },
];

const updateRole = (userId: number, role: string) => {
    router.patch(
        `/users/${userId}/roles`,
        { role },
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
                <h1 class="mb-1 text-lg font-semibold">Asignar roles</h1>
                <p class="mb-4 text-sm text-gray-600">
                    Como administrador, puedes cambiar el rol de cada usuario.
                </p>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Correo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Rol actual</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nuevo rol</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="user in props.users" :key="user.id">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ user.name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ user.email }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">{{ user.role ?? 'Sin rol' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <select
                                    class="rounded border px-3 py-2"
                                    :value="user.role ?? ''"
                                    @change="updateRole(user.id, ($event.target as HTMLSelectElement).value)"
                                >
                                    <option value="" disabled>Seleccionar rol</option>
                                    <option v-for="role in props.roles" :key="role" :value="role">
                                        {{ role }}
                                    </option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
