<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    customer: {
        id: number;
        dni: string;
        name: string;
        phone: string | null;
        email: string;
        address: string | null;
        position: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clientes',
        href: '/customers',
    },
    {
        title: props.customer.name,
        href: `/customers/${props.customer.id}`,
    },
];
</script>

<template>
    <Head
        :title="`Cliente ${props.customer.name}`"
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6
                   rounded-xl bg-slate-50 p-4
                   dark:bg-slate-950"
        >
            <!-- CABECERA -->
            <section
                class="flex flex-col gap-4 rounded-xl
                       border border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900
                       sm:flex-row sm:items-center
                       sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{ props.customer.name }}
                    </h1>

                    <p
                        class="mt-1 text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        DNI / Identificación:
                        {{ props.customer.dni }}
                    </p>
                </div>

                <Link
                    href="/customers"
                    class="inline-flex items-center
                           justify-center rounded-lg
                           border border-slate-300
                           bg-white px-5 py-2.5
                           text-sm font-medium
                           text-slate-700 shadow-sm
                           transition hover:bg-slate-50
                           dark:border-slate-600
                           dark:bg-slate-800
                           dark:text-slate-300
                           dark:hover:bg-slate-700"
                >
                    ← Volver
                </Link>
            </section>

            <!-- INFORMACIÓN DEL CLIENTE -->
            <section
                class="grid gap-4
                       md:grid-cols-2"
            >
                <!-- TELÉFONO -->
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-5 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-xs font-semibold
                               uppercase tracking-wide
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Teléfono
                    </p>

                    <p
                        class="mt-2 text-base font-medium
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{
                            props.customer.phone ||
                            'No registrado'
                        }}
                    </p>
                </div>

                <!-- CORREO -->
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-5 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-xs font-semibold
                               uppercase tracking-wide
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Correo electrónico
                    </p>

                    <p
                        class="mt-2 text-base font-medium
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{
                            props.customer.email ||
                            'No registrado'
                        }}
                    </p>
                </div>

                <!-- DIRECCIÓN -->
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-5 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-xs font-semibold
                               uppercase tracking-wide
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Dirección
                    </p>

                    <p
                        class="mt-2 text-base font-medium
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{
                            props.customer.address ||
                            'No registrada'
                        }}
                    </p>
                </div>

                <!-- CARGO -->
                <div
                    class="rounded-xl border
                           border-slate-200
                           bg-white p-5 shadow-sm
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <p
                        class="text-xs font-semibold
                               uppercase tracking-wide
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Cargo
                    </p>

                    <p
                        class="mt-2 text-base font-medium
                               text-slate-900
                               dark:text-slate-100"
                    >
                        {{
                            props.customer.position ||
                            'No registrado'
                        }}
                    </p>
                </div>
            </section>

            <!-- RESUMEN -->
            <section
                class="rounded-xl border
                       border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <h2
                    class="text-lg font-semibold
                           text-slate-900
                           dark:text-slate-100"
                >
                    Resumen del cliente
                </h2>

                <div
                    class="mt-4 grid gap-4
                           sm:grid-cols-2
                           lg:grid-cols-4"
                >
                    <div
                        class="rounded-lg bg-slate-50 p-4
                               dark:bg-slate-800"
                    >
                        <p
                            class="text-xs uppercase
                                   tracking-wide
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Identificación
                        </p>

                        <p
                            class="mt-1 font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ props.customer.dni }}
                        </p>
                    </div>

                    <div
                        class="rounded-lg bg-slate-50 p-4
                               dark:bg-slate-800"
                    >
                        <p
                            class="text-xs uppercase
                                   tracking-wide
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Cliente
                        </p>

                        <p
                            class="mt-1 font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ props.customer.name }}
                        </p>
                    </div>

                    <div
                        class="rounded-lg bg-slate-50 p-4
                               dark:bg-slate-800"
                    >
                        <p
                            class="text-xs uppercase
                                   tracking-wide
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Teléfono
                        </p>

                        <p
                            class="mt-1 font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{
                                props.customer.phone ||
                                '-'
                            }}
                        </p>
                    </div>

                    <div
                        class="rounded-lg bg-slate-50 p-4
                               dark:bg-slate-800"
                    >
                        <p
                            class="text-xs uppercase
                                   tracking-wide
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Cargo
                        </p>

                        <p
                            class="mt-1 font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{
                                props.customer.position ||
                                '-'
                            }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- BOTÓN -->
            <section class="flex flex-wrap gap-3">
                <Link
                    href="/customers"
                    class="inline-flex items-center
                           justify-center rounded-lg
                           bg-slate-900 px-5 py-2.5
                           text-sm font-medium
                           text-white transition
                           hover:bg-slate-800
                           dark:bg-slate-700
                           dark:hover:bg-slate-600"
                >
                    Volver a clientes
                </Link>
            </section>
        </div>
    </AppLayout>
</template>