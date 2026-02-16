<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    transfer: {
        id: number;
        code: string;
        status: string;
        notes: string | null;
        from_warehouse: { name: string; code: string };
        to_warehouse: { name: string; code: string };
        items: Array<{
            id: number;
            kilos_requested: number;
            metros_requested: number;
            kilos_shipped: number;
            metros_shipped: number;
            kilos_received: number;
            metros_received: number;
            store: { code_product: string; name_product: string };
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Traslados',
        href: '/transfers',
    },
    {
        title: 'Detalle',
        href: `/transfers/${props.transfer.id}`,
    },
];
</script>

<template>
    <Head :title="`Traslado ${props.transfer.code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-xl font-semibold">{{ props.transfer.code }}</h1>
            <p class="text-sm text-gray-600">
                {{ props.transfer.from_warehouse.name }} → {{ props.transfer.to_warehouse.name }}
                ({{ props.transfer.status }})
            </p>

            <div class="overflow-x-auto rounded border bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Producto</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Solicitado</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Despachado</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Recibido</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="item in props.transfer.items" :key="item.id">
                            <td class="px-4 py-3 text-sm">{{ item.store.code_product }} - {{ item.store.name_product }}</td>
                            <td class="px-4 py-3 text-sm">{{ item.kilos_requested }} kg / {{ item.metros_requested }} m</td>
                            <td class="px-4 py-3 text-sm">{{ item.kilos_shipped }} kg / {{ item.metros_shipped }} m</td>
                            <td class="px-4 py-3 text-sm">{{ item.kilos_received }} kg / {{ item.metros_received }} m</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
