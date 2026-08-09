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
    { title: 'Clientes', href: '/customers' },
    { title: props.customer.name, href: `/customers/${props.customer.id}` },
];
</script>

<template>
    <Head :title="`Cliente ${props.customer.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <section class="flex items-center justify-between rounded-xl border bg-white p-5 shadow-sm">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">
                        {{ props.customer.name }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        {{ props.customer.dni }}
                    </p>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase text-slate-500">Teléfono</p>
                    <p class="mt-2 text-sm font-medium">{{ props.customer.phone || 'No registrado' }}</p>
                </div>
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase text-slate-500">Correo</p>
                    <p class="mt-2 text-sm font-medium">{{ props.customer.email || 'No registrado' }}</p>
                </div>
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase text-slate-500">Dirección</p>
                    <p class="mt-2 text-sm font-medium">{{ props.customer.address || 'No registrada' }}</p>
                </div>
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <p class="text-xs uppercase text-slate-500">Cargo</p>
                    <p class="mt-2 text-sm font-medium">{{ props.customer.position || 'No registrado' }}</p>
                </div>
            </section>

            <section class="flex gap-3">
                <Link href="/customers" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                    Volver
                </Link>
            </section>
        </div>
    </AppLayout>
</template>
