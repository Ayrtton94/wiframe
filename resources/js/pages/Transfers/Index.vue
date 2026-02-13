<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    transfers: {
        data: Array<{
            id: number;
            code: string;
            status: string;
            from_warehouse: { name: string };
            to_warehouse: { name: string };
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Traslados',
        href: '/transfers',
    },
];
</script>

<template>
    <Head title="Traslados" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-xl font-semibold">Traslados entre almacenes</h1>

            <div class="overflow-x-auto rounded border bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase">Código</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase">Origen</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase">Destino</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="transfer in props.transfers.data" :key="transfer.id">
                            <td class="px-4 py-3 text-sm">{{ transfer.code }}</td>
                            <td class="px-4 py-3 text-sm">{{ transfer.from_warehouse.name }}</td>
                            <td class="px-4 py-3 text-sm">{{ transfer.to_warehouse.name }}</td>
                            <td class="px-4 py-3 text-sm">{{ transfer.status }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Link :href="`/transfers/${transfer.id}`" class="text-blue-600">Ver detalle</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
