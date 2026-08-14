<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    supplier: {
        id: number;
        name: string;
        company_name: string;
        ruc: string;
        category: string;
        phone: string;
        email: string;
        address: string;
        city: string;
        country: string;
        account: string;
        bank_name: string;
        bank_address: string;
        bank_city: string;
        bank_country: string;
        bank_cod_swift: string;
        cod_swift: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Proveedores', href: '/suppliers' },
    {
        title: props.supplier.company_name,
        href: `/suppliers/${props.supplier.id}`,
    },
];
</script>

<template>
    <Head :title="`Proveedor ${props.supplier.company_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">

            <!-- CABECERA -->
            <section
                class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">
                        {{ props.supplier.company_name }}
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        RUC: {{ props.supplier.ruc || 'No registrado' }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Categoría:
                        {{ props.supplier.category || 'No registrada' }}
                    </p>
                </div>
            </section>

            <!-- INFORMACIÓN GENERAL -->
            <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">

                <!-- Empresa -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Información de empresa
                    </p>

                    <div class="mt-3 space-y-2">
                        <div>
                            <p class="text-xs text-slate-400">
                                Razón social
                            </p>
                            <p class="text-sm font-medium text-slate-900">
                                {{ props.supplier.company_name || 'No registrada' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                RUC
                            </p>
                            <p class="text-sm font-medium text-slate-900">
                                {{ props.supplier.ruc || 'No registrado' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Categoría
                            </p>
                            <p class="text-sm font-medium text-slate-900">
                                {{ props.supplier.category || 'No registrada' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- CONTACTO -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Contacto
                    </p>

                    <div class="mt-3 space-y-2">
                        <div>
                            <p class="text-xs text-slate-400">
                                Nombre de contacto
                            </p>
                            <p class="text-sm font-medium text-slate-900">
                                {{ props.supplier.name || 'No registrado' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Teléfono
                            </p>
                            <p class="text-sm text-slate-700">
                                {{ props.supplier.phone || 'Sin teléfono' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Correo electrónico
                            </p>
                            <p class="text-sm text-slate-700">
                                {{ props.supplier.email || 'Sin correo' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- DIRECCIÓN -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p class="text-xs font-semibold uppercase text-slate-500">
                        Dirección
                    </p>

                    <div class="mt-3 space-y-2">
                        <div>
                            <p class="text-xs text-slate-400">
                                Dirección
                            </p>
                            <p class="text-sm font-medium text-slate-900">
                                {{ props.supplier.address || 'No registrada' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                Ciudad
                            </p>
                            <p class="text-sm text-slate-700">
                                {{ props.supplier.city || 'No registrada' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400">
                                País
                            </p>
                            <p class="text-sm text-slate-700">
                                {{ props.supplier.country || 'No registrado' }}
                            </p>
                        </div>
                    </div>
                </div>

            </section>

            <!-- INFORMACIÓN BANCARIA -->
            <section>
                <h2 class="mb-4 text-lg font-semibold text-slate-900">
                    Información bancaria
                </h2>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">

                    <!-- CUENTA -->
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Cuenta bancaria
                        </p>

                        <div class="mt-3 space-y-2">
                            <div>
                                <p class="text-xs text-slate-400">
                                    Número de cuenta
                                </p>

                                <p class="text-sm font-medium text-slate-900">
                                    {{ props.supplier.account || 'No registrada' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Banco
                                </p>

                                <p class="text-sm text-slate-700">
                                    {{ props.supplier.bank_name || 'Sin banco' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- SWIFT -->
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Códigos bancarios
                        </p>

                        <div class="mt-3 space-y-2">
                            <div>
                                <p class="text-xs text-slate-400">
                                    Código SWIFT
                                </p>

                                <p class="text-sm font-medium text-slate-900">
                                    {{
                                        props.supplier.cod_swift ||
                                        props.supplier.bank_cod_swift ||
                                        'No registrado'
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    SWIFT bancario
                                </p>

                                <p class="text-sm text-slate-700">
                                    {{ props.supplier.bank_cod_swift || 'No registrado' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- DIRECCIÓN DEL BANCO -->
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <p class="text-xs font-semibold uppercase text-slate-500">
                            Dirección del banco
                        </p>

                        <div class="mt-3 space-y-2">
                            <div>
                                <p class="text-xs text-slate-400">
                                    Dirección
                                </p>

                                <p class="text-sm font-medium text-slate-900">
                                    {{ props.supplier.bank_address || 'No registrada' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Ciudad
                                </p>

                                <p class="text-sm text-slate-700">
                                    {{ props.supplier.bank_city || 'No registrada' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    País
                                </p>

                                <p class="text-sm text-slate-700">
                                    {{ props.supplier.bank_country || 'No registrado' }}
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- RESUMEN COMPLETO -->
            <section
                class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <h2 class="text-lg font-semibold text-slate-900">
                    Resumen del proveedor
                </h2>

                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-4">

                    <div>
                        <p class="text-xs text-slate-400">Empresa</p>
                        <p class="text-sm font-medium">
                            {{ props.supplier.company_name || '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Contacto</p>
                        <p class="text-sm font-medium">
                            {{ props.supplier.name || '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Teléfono</p>
                        <p class="text-sm font-medium">
                            {{ props.supplier.phone || '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Correo</p>
                        <p class="text-sm font-medium">
                            {{ props.supplier.email || '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Ciudad</p>
                        <p class="text-sm font-medium">
                            {{ props.supplier.city || '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">País</p>
                        <p class="text-sm font-medium">
                            {{ props.supplier.country || '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Banco</p>
                        <p class="text-sm font-medium">
                            {{ props.supplier.bank_name || '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-400">Cuenta</p>
                        <p class="text-sm font-medium">
                            {{ props.supplier.account || '-' }}
                        </p>
                    </div>

                </div>
            </section>

            <!-- BOTÓN -->
            <section class="flex gap-3">
                <Link
                    href="/suppliers"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                >
                    Volver
                </Link>

                <Link
                    :href="`/suppliers/${props.supplier.id}/edit`"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Editar proveedor
                </Link>
            </section>

        </div>
    </AppLayout>
</template>