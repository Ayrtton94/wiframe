<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clientes',
        href: '/customers',
    },
];

const props = defineProps<{
    customers: Array<{
        id: number;
        name: string;
        email: string;
        phone: string;
        position: string;
        dni: string;
        is_active: boolean;
    }>;
}>();
</script>

<template>
    <Head title="Listar Cliente" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div
            class="flex h-full flex-1 flex-col gap-6
                   overflow-x-auto rounded-xl
                   bg-slate-50 p-4
                   dark:bg-slate-950"
        >

            <!-- =========================================
                 ENCABEZADO
            ========================================== -->

            <div
                class="flex flex-col gap-2
                       sm:flex-row sm:items-center
                       sm:justify-between"
            >

                <div>

                    <h1
                        class="text-2xl font-bold
                               text-slate-800
                               dark:text-slate-100"
                    >
                        Clientes
                    </h1>

                    <p
                        class="text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Administra la información de tus clientes
                        registrados.
                    </p>

                </div>

                <Link
                    href="/customers/create"
                    class="inline-flex items-center
                           justify-center rounded-lg
                           bg-blue-600 px-5 py-2.5
                           text-sm font-medium
                           text-white shadow-sm
                           transition
                           hover:bg-blue-700
                           focus:outline-none
                           focus:ring-2
                           focus:ring-blue-500
                           focus:ring-offset-2
                           dark:focus:ring-offset-slate-950"
                >
                    + Crear Nuevo
                </Link>

            </div>


            <!-- =========================================
                 TABLA
            ========================================== -->

            <div
                class="overflow-hidden rounded-xl
                       border border-slate-200
                       bg-white shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >

                <div class="overflow-x-auto">

                    <table
                        class="min-w-full divide-y
                               divide-slate-200
                               dark:divide-slate-700"
                    >

                        <!-- =====================================
                             CABECERA
                        ====================================== -->

                        <thead
                            class="bg-slate-100
                                   dark:bg-slate-800"
                        >

                            <tr>

                                <!-- DNI -->

                                <th
                                    class="whitespace-nowrap
                                           px-6 py-4 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Número de identificación
                                </th>

                                <!-- NOMBRE -->

                                <th
                                    class="whitespace-nowrap
                                           px-6 py-4 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Razón Social / Nombre
                                </th>

                                <!-- TELEFONO -->

                                <th
                                    class="whitespace-nowrap
                                           px-6 py-4 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Teléfono
                                </th>

                                <!-- CORREO -->

                                <th
                                    class="whitespace-nowrap
                                           px-6 py-4 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Correo Electrónico
                                </th>

                                <!-- CARGO -->

                                <th
                                    class="whitespace-nowrap
                                           px-6 py-4 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Cargo
                                </th>

                                <!-- ESTADO -->

                                <th
                                    class="whitespace-nowrap
                                           px-6 py-4 text-center
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Estado
                                </th>

                                <!-- ACCIONES -->

                                <th
                                    class="whitespace-nowrap
                                           px-6 py-4 text-center
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <!-- =====================================
                             CUERPO
                        ====================================== -->

                        <tbody
                            class="divide-y
                                   divide-slate-200
                                   bg-white
                                   dark:divide-slate-700
                                   dark:bg-slate-900"
                        >

                            <tr
                                v-for="customer in props.customers"
                                :key="customer.id"
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >

                                <!-- DNI -->

                                <td
                                    class="whitespace-nowrap
                                           px-6 py-4 text-sm
                                           font-medium
                                           text-slate-800
                                           dark:text-slate-100"
                                >
                                    {{ customer.dni }}
                                </td>


                                <!-- NOMBRE -->

                                <td
                                    class="px-6 py-4 text-sm
                                           font-medium
                                           text-slate-800
                                           dark:text-slate-100"
                                >
                                    {{ customer.name }}
                                </td>


                                <!-- TELEFONO -->

                                <td
                                    class="whitespace-nowrap
                                           px-6 py-4 text-sm
                                           text-slate-600
                                           dark:text-slate-400"
                                >
                                    {{ customer.phone }}
                                </td>


                                <!-- CORREO -->

                                <td
                                    class="px-6 py-4 text-sm
                                           text-slate-600
                                           dark:text-slate-400"
                                >
                                    {{ customer.email }}
                                </td>


                                <!-- CARGO -->

                                <td
                                    class="whitespace-nowrap
                                           px-6 py-4 text-sm
                                           text-slate-600
                                           dark:text-slate-400"
                                >
                                    {{ customer.position }}
                                </td>


                                <!-- =================================
                                     ESTADO
                                ================================== -->

                                <td class="px-6 py-4 text-center">

                                    <!-- ACTIVO -->

                                    <span
                                        v-if="customer.is_active"
                                        class="inline-flex items-center
                                               rounded-full
                                               bg-green-100
                                               px-3 py-1
                                               text-xs font-semibold
                                               text-green-700
                                               dark:bg-green-900/30
                                               dark:text-green-400"
                                    >
                                        Activo
                                    </span>

                                    <!-- DESACTIVADO -->

                                    <span
                                        v-else
                                        class="inline-flex items-center
                                               rounded-full
                                               bg-red-100
                                               px-3 py-1
                                               text-xs font-semibold
                                               text-red-700
                                               dark:bg-red-900/30
                                               dark:text-red-400"
                                    >
                                        Desactivado
                                    </span>

                                </td>


                                <!-- =================================
                                     ACCIONES
                                ================================== -->

                                <td
                                    class="whitespace-nowrap
                                           px-6 py-4 text-center"
                                >

                                    <div
                                        class="flex items-center
                                               justify-center
                                               gap-3"
                                    >

                                        <!-- VER -->

                                        <Link
                                            :href="
                                                `/customers/${customer.id}`
                                            "
                                            class="font-medium
                                                   text-green-600
                                                   transition
                                                   hover:text-green-800
                                                   dark:text-green-400
                                                   dark:hover:text-green-300"
                                        >
                                            Ver
                                        </Link>


                                        <!-- EDITAR -->

                                        <Link
                                            :href="
                                                `/customers/${customer.id}/edit`
                                            "
                                            class="font-medium
                                                   text-blue-600
                                                   transition
                                                   hover:text-blue-800
                                                   dark:text-blue-400
                                                   dark:hover:text-blue-300"
                                        >
                                            Editar
                                        </Link>


                                        <!-- =================================
                                             DESACTIVAR / ACTIVAR
                                        ================================== -->

                                        <Link
                                            :href="
                                                `/customers/${customer.id}/toggle-status`
                                            "
                                            method="patch"
                                            as="button"
                                            class="font-medium
                                                   transition"
                                            :class="
                                                customer.is_active
                                                    ? 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300'
                                                    : 'text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300'
                                            "
                                        >
                                            {{
                                                customer.is_active
                                                    ? 'Desactivar'
                                                    : 'Activar'
                                            }}
                                        </Link>

                                    </div>

                                </td>

                            </tr>


                            <!-- =====================================
                                 SIN CLIENTES
                            ====================================== -->

                            <tr
                                v-if="
                                    props.customers.length === 0
                                "
                            >

                                <td
                                    colspan="7"
                                    class="px-6 py-14 text-center"
                                >

                                    <div
                                        class="flex flex-col
                                               items-center
                                               justify-center"
                                    >

                                        <div
                                            class="mb-3 rounded-full
                                                   bg-slate-100 p-4
                                                   dark:bg-slate-800"
                                        >

                                            <span class="text-2xl">
                                                👥
                                            </span>

                                        </div>

                                        <p
                                            class="text-sm
                                                   font-medium
                                                   text-slate-700
                                                   dark:text-slate-200"
                                        >
                                            No hay clientes
                                            registrados
                                        </p>

                                        <p
                                            class="mt-1 text-sm
                                                   text-slate-500
                                                   dark:text-slate-400"
                                        >
                                            Los clientes que registres
                                            aparecerán aquí.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- =========================================
                     FOOTER
                ========================================== -->

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
                        Total de clientes:

                        <span
                            class="font-semibold
                                   text-slate-700
                                   dark:text-slate-200"
                        >
                            {{ props.customers.length }}
                        </span>

                    </p>

                </div>

            </div>

        </div>

    </AppLayout>
</template>