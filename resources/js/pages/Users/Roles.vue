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

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6
                   overflow-x-auto rounded-xl
                   bg-slate-50 p-4
                   dark:bg-slate-950"
        >
            <!-- ENCABEZADO -->
            <div>
                <h1
                    class="text-2xl font-bold
                           text-slate-800
                           dark:text-slate-100"
                >
                    Asignar roles y almacenes
                </h1>

                <p
                    class="mt-1 text-sm
                           text-slate-500
                           dark:text-slate-400"
                >
                    Define el rol de cada usuario y los almacenes
                    que puede operar.
                </p>
            </div>

            <!-- CARD PRINCIPAL -->
            <div
                class="overflow-hidden rounded-xl
                       border border-slate-200
                       bg-white shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <!-- CABECERA -->
                <div
                    class="border-b
                           border-slate-200
                           bg-slate-50 px-6 py-4
                           dark:border-slate-700
                           dark:bg-slate-800"
                >
                    <h2
                        class="text-sm font-semibold
                               text-slate-700
                               dark:text-slate-200"
                    >
                        Usuarios del sistema
                    </h2>

                    <p
                        class="mt-1 text-xs
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Modifica los permisos de acceso y guarda
                        los cambios.
                    </p>
                </div>

                <!-- TABLA -->
                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y
                               divide-slate-200
                               dark:divide-slate-700"
                    >
                        <!-- ENCABEZADO -->
                        <thead
                            class="bg-slate-100
                                   dark:bg-slate-800"
                        >
                            <tr>
                                <th
                                    class="whitespace-nowrap
                                           px-6 py-4 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Usuario
                                </th>

                                <th
                                    class="whitespace-nowrap
                                           px-6 py-4 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Correo
                                </th>

                                <th
                                    class="whitespace-nowrap
                                           px-6 py-4 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Rol
                                </th>

                                <th
                                    class="px-6 py-4 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Almacenes asignados
                                </th>

                                <th
                                    class="whitespace-nowrap
                                           px-6 py-4 text-center
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <!-- DATOS -->
                        <tbody
                            class="divide-y
                                   divide-slate-200
                                   bg-white
                                   dark:divide-slate-700
                                   dark:bg-slate-900"
                        >
                            <tr
                                v-for="user in editableUsers"
                                :key="user.id"
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
                                <!-- USUARIO -->
                                <td
                                    class="whitespace-nowrap
                                           px-6 py-5"
                                >
                                    <div
                                        class="flex items-center gap-3"
                                    >
                                        <div
                                            class="flex h-9 w-9
                                                   items-center
                                                   justify-center
                                                   rounded-full
                                                   bg-blue-100
                                                   text-sm font-semibold
                                                   text-blue-700
                                                   dark:bg-blue-500/15
                                                   dark:text-blue-400"
                                        >
                                            {{
                                                user.name
                                                    .charAt(0)
                                                    .toUpperCase()
                                            }}
                                        </div>

                                        <div>
                                            <p
                                                class="text-sm
                                                       font-semibold
                                                       text-slate-800
                                                       dark:text-slate-100"
                                            >
                                                {{ user.name }}
                                            </p>

                                            <p
                                                class="text-xs
                                                       text-slate-500
                                                       dark:text-slate-400"
                                            >
                                                ID: {{ user.id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- CORREO -->
                                <td
                                    class="whitespace-nowrap
                                           px-6 py-5"
                                >
                                    <span
                                        class="text-sm
                                               text-slate-600
                                               dark:text-slate-300"
                                    >
                                        {{ user.email }}
                                    </span>
                                </td>

                                <!-- ROL -->
                                <td
                                    class="whitespace-nowrap
                                           px-6 py-5"
                                >
                                    <select
                                        v-model="
                                            user.selectedRole
                                        "
                                        @change="
                                            onRoleChange(user)
                                        "
                                        class="w-full min-w-44
                                               rounded-lg
                                               border
                                               border-slate-300
                                               bg-white px-3 py-2.5
                                               text-sm
                                               text-slate-700
                                               shadow-sm transition
                                               focus:border-blue-500
                                               focus:outline-none
                                               focus:ring-2
                                               focus:ring-blue-500/20
                                               dark:border-slate-600
                                               dark:bg-slate-800
                                               dark:text-slate-100"
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

                                <!-- ALMACENES -->
                                <td
                                    class="px-6 py-5"
                                >
                                    <select
                                        v-model="
                                            user.selectedWarehouses
                                        "
                                        multiple
                                        class="min-h-28 w-full min-w-72
                                               rounded-lg
                                               border
                                               border-slate-300
                                               bg-white px-3 py-2
                                               text-sm
                                               text-slate-700
                                               shadow-sm transition
                                               focus:border-blue-500
                                               focus:outline-none
                                               focus:ring-2
                                               focus:ring-blue-500/20
                                               dark:border-slate-600
                                               dark:bg-slate-800
                                               dark:text-slate-100"
                                    >
                                        <option
                                            v-for="warehouse in props.warehouses"
                                            :key="warehouse.id"
                                            :value="
                                                String(
                                                    warehouse.id,
                                                )
                                            "
                                            class="py-1"
                                        >
                                            {{ warehouse.code }} -
                                            {{ warehouse.name }}
                                        </option>
                                    </select>

                                    <p
                                        class="mt-2 text-xs
                                               text-slate-400
                                               dark:text-slate-500"
                                    >
                                        Mantén presionado Ctrl para
                                        seleccionar varios.
                                    </p>
                                </td>

                                <!-- GUARDAR -->
                                <td
                                    class="whitespace-nowrap
                                           px-6 py-5 text-center"
                                >
                                    <button
                                        type="button"
                                        @click="
                                            updateUserAccess(
                                                user.id,
                                            )
                                        "
                                        class="inline-flex
                                               items-center
                                               justify-center
                                               rounded-lg
                                               bg-blue-600
                                               px-5 py-2.5
                                               text-sm font-medium
                                               text-white shadow-sm
                                               transition
                                               hover:bg-blue-700
                                               focus:outline-none
                                               focus:ring-2
                                               focus:ring-blue-500
                                               focus:ring-offset-2
                                               dark:focus:ring-offset-slate-900
                                               dark:hover:bg-blue-500"
                                    >
                                        Guardar
                                    </button>
                                </td>
                            </tr>

                            <!-- SIN USUARIOS -->
                            <tr
                                v-if="
                                    editableUsers.length ===
                                    0
                                "
                            >
                                <td
                                    colspan="5"
                                    class="px-6 py-14 text-center"
                                >
                                    <div
                                        class="flex flex-col
                                               items-center
                                               justify-center"
                                    >
                                        <div
                                            class="mb-3
                                                   rounded-full
                                                   bg-slate-100 p-4
                                                   dark:bg-slate-800"
                                        >
                                            <span
                                                class="text-2xl"
                                            >
                                                👥
                                            </span>
                                        </div>

                                        <p
                                            class="text-sm
                                                   font-medium
                                                   text-slate-700
                                                   dark:text-slate-200"
                                        >
                                            No hay usuarios disponibles
                                        </p>

                                        <p
                                            class="mt-1 text-sm
                                                   text-slate-500
                                                   dark:text-slate-400"
                                        >
                                            Los usuarios registrados
                                            aparecerán aquí.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- FOOTER -->
                <div
                    class="border-t
                           border-slate-200
                           bg-slate-50 px-6 py-4
                           dark:border-slate-700
                           dark:bg-slate-800/60"
                >
                    <p
                        class="text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Total de usuarios:

                        <span
                            class="font-semibold
                                   text-slate-700
                                   dark:text-slate-200"
                        >
                            {{ editableUsers.length }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>