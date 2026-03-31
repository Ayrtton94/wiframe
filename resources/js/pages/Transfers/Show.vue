<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

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

    permissions: {
        can_ship: boolean;
        can_receive: boolean;
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

const shipForm = useForm({ notes: '' });

const receiveForm = useForm({
    notes: '',
    items: props.transfer.items.map((item) => ({
        transfer_item_id: item.id,
        kilos_received: Number(item.kilos_shipped || 0),
        metros_received: Number(item.metros_shipped || 0),
    })),
});

const shipTransfer = () => {
    shipForm.post(`/transfers/${props.transfer.id}/ship`, {
        preserveScroll: true,
    });
};

const receiveTransfer = () => {
    receiveForm.post(`/transfers/${props.transfer.id}/receive`, {
        preserveScroll: true,
    });
};

</script>

<template>
    <Head :title="`Traslado ${props.transfer.code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-xl font-semibold">{{ props.transfer.code }}</h1>
            <p class="text-sm text-gray-600">
                {{ props.transfer.from_warehouse.name }} →
                {{ props.transfer.to_warehouse.name }} ({{ props.transfer.status }})
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
                            <td class="px-4 py-3 text-sm">
                                {{ item.store.code_product }} - {{ item.store.name_product }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ item.kilos_requested }} kg / {{ item.metros_requested }} m
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ item.kilos_shipped }} kg / {{ item.metros_shipped }} m
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ item.kilos_received }} kg / {{ item.metros_received }} m
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <section
                v-if="props.permissions.can_ship"
                class="space-y-2 rounded border bg-white p-4"
            >
                <h2 class="text-lg font-semibold">Despachar traslado</h2>
                <textarea
                    v-model="shipForm.notes"
                    rows="2"
                    placeholder="Notas del despacho (opcional)"
                    class="w-full rounded border px-3 py-2"
                />
                <p v-if="shipForm.errors.transfer" class="text-sm text-red-600">
                    {{ shipForm.errors.transfer }}
                </p>
                <p v-if="shipForm.errors.stock" class="text-sm text-red-600">
                    {{ shipForm.errors.stock }}
                </p>
                <button
                    type="button"
                    class="rounded bg-blue-600 px-3 py-2 text-white"
                    :disabled="shipForm.processing"
                    @click="shipTransfer"
                >
                    Confirmar despacho
                </button>
            </section>

            <section
                v-if="props.permissions.can_receive"
                class="space-y-3 rounded border bg-white p-4"
            >
                <h2 class="text-lg font-semibold">Recepcionar traslado</h2>
                <div
                    v-for="(item, index) in receiveForm.items"
                    :key="item.transfer_item_id"
                    class="grid gap-2 rounded border p-3 md:grid-cols-2"
                >
                    <input
                        v-model.number="item.kilos_received"
                        type="number"
                        min="0"
                        step="0.001"
                        class="rounded border px-3 py-2"
                        placeholder="Kilos recibidos"
                    />
                    <input
                        v-model.number="item.metros_received"
                        type="number"
                        min="0"
                        step="0.001"
                        class="rounded border px-3 py-2"
                        placeholder="Metros recibidos"
                    />
                    <p class="text-xs text-slate-500 md:col-span-2">
                        Item {{ index + 1 }} · máximo: {{ props.transfer.items[index].kilos_shipped }} kg /
                        {{ props.transfer.items[index].metros_shipped }} m
                    </p>
                </div>

                <textarea
                    v-model="receiveForm.notes"
                    rows="2"
                    placeholder="Notas de recepción (opcional)"
                    class="w-full rounded border px-3 py-2"
                />
                <p v-if="receiveForm.errors.transfer" class="text-sm text-red-600">
                    {{ receiveForm.errors.transfer }}
                </p>
                <p v-if="receiveForm.errors.items" class="text-sm text-red-600">
                    {{ receiveForm.errors.items }}
                </p>
                <button
                    type="button"
                    class="rounded bg-green-600 px-3 py-2 text-white"
                    :disabled="receiveForm.processing"
                    @click="receiveTransfer"
                >
                    Confirmar recepción
                </button>
            </section>
        </div>
    </AppLayout>
</template>
