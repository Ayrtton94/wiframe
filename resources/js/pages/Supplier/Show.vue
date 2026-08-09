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
    { title: props.supplier.company_name, href: `/suppliers/${props.supplier.id}` },
];
</script>

<template>
    <Head :title="`Proveedor ${props.supplier.company_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <section class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">
                        {{ props.supplier.company_name }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        {{ props.supplier.ruc }} · {{ props.supplier.category }}
                    </p>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase text-slate-500">Contacto</p>
                    <p class="mt-2 text-sm font-medium">{{ props.supplier.name || 'No registrado' }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ props.supplier.phone || 'Sin teléfono' }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ props.supplier.email || 'Sin correo' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase text-slate-500">Dirección</p>
                    <p class="mt-2 text-sm font-medium">{{ props.supplier.address || 'No registrada' }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ props.supplier.city }} · {{ props.supplier.country }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase text-slate-500">Cuenta bancaria</p>
                    <p class="mt-2 text-sm font-medium">{{ props.supplier.account || 'No registrada' }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ props.supplier.bank_name || 'Sin banco' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase text-slate-500">Swift / Banco</p>
                    <p class="mt-2 text-sm font-medium">{{ props.supplier.cod_swift || props.supplier.bank_cod_swift || 'No registrado' }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ props.supplier.bank_address || 'Sin dirección' }}</p>
                </div>
            </section>

            <section class="flex gap-3">
                <Link href="/suppliers" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">              
                    Volver
                </Link>
            </section>
        </div>
    </AppLayout>
</template>
