<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

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
        can_approve: boolean;
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

type ReceiveItemForm = {
    transfer_item_id: number;
    kilos_received: number;
    metros_received: number;
};

const shipForm = useForm<{ notes: string }>({ notes: '' });
const approveForm = useForm<{ notes: string }>({ notes: '' });
const receiveForm = useForm<{ notes: string; items: ReceiveItemForm[] }>({
    notes: '',
    items: props.transfer.items.map((item) => ({
        transfer_item_id: item.id,
        kilos_received: Number(item.kilos_shipped || 0),
        metros_received: Number(item.metros_shipped || 0),
    })),
});

const approveErrors = computed(() => approveForm.errors as Record<string, string>);
const shipErrors = computed(() => shipForm.errors as Record<string, string>);
const receiveErrors = computed(() => receiveForm.errors as Record<string, string>);

const shipTransfer = () => {
    shipForm.post(`/transfers/${props.transfer.id}/ship`, {
        preserveScroll: true,
    });
};

const approveTransfer = () => {
    approveForm.post(`/transfers/${props.transfer.id}/approve`, {
        preserveScroll: true,
    });
};

const receiveTransfer = () => {
    receiveForm.post(`/transfers/${props.transfer.id}/receive`, {
        preserveScroll: true,
    });
};

const clearInvalidReceiveMessage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    target.setCustomValidity('');
};

const setInvalidReceiveMessage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.validity.rangeUnderflow) {
        target.setCustomValidity('El valor no puede ser negativo');
    } else if (target.validity.rangeOverflow) {
        target.setCustomValidity('No puedes recepcionar más que lo despachado');
    } else {
        target.setCustomValidity('');
    }
};

const clampReceivedField = (item: ReceiveItemForm, field: 'kilos_received' | 'metros_received', event: Event) => {
    clearInvalidReceiveMessage(event);
    const value = Number(item[field] || 0);
    if (value < 0) {
        item[field] = 0 as any;
        return;
    }

    const receiveItems = receiveForm.items as ReceiveItemForm[];
    const index = receiveItems.findIndex((receiveItem) => receiveItem.transfer_item_id === item.transfer_item_id);
    const shipped = props.transfer.items[index];

    if (! shipped) {
        return;
    }

    const maxValue = field === 'kilos_received'
        ? shipped.kilos_shipped
        : shipped.metros_shipped;

    if (value > maxValue) {
        item[field] = maxValue as any;
    }
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
                        </tr>
                    </tbody>
                </table>
            </div>
             <section
                v-if="props.permissions.can_approve"
                class="space-y-2 rounded border bg-white p-4"
            >
                <h2 class="text-lg font-semibold">Aprobar traslado (almacén destino)</h2>
                <textarea
                    v-model="approveForm.notes"
                    rows="2"
                    placeholder="Notas de aprobación (opcional)"
                    class="w-full rounded border px-3 py-2"
                />
                <p v-if="approveErrors.transfer" class="text-sm text-red-600">
                    {{ approveErrors.transfer }}
                </p>
                <button
                    type="button"
                    class="rounded bg-amber-600 px-3 py-2 text-white"
                    :disabled="approveForm.processing"
                    @click="approveTransfer"
                >
                    Aceptar traslado
                </button>
            </section>

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
                <p v-if="shipErrors.transfer" class="text-sm text-red-600">
                    {{ shipErrors.transfer }}
                </p>
                <p v-if="shipErrors.stock" class="text-sm text-red-600">
                    {{ shipErrors.stock }}
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
                        :max="props.transfer.items[index].kilos_shipped"
                        step="0.001"
                        class="rounded border px-3 py-2"
                        placeholder="Kilos recibidos"
                        @input="clampReceivedField(item, 'kilos_received', $event)"
                        @invalid="setInvalidReceiveMessage"
                    />
                    <input
                        v-model.number="item.metros_received"
                        type="number"
                        min="0"
                        :max="props.transfer.items[index].metros_shipped"
                        step="0.001"
                        class="rounded border px-3 py-2"
                        placeholder="Metros recibidos"
                        @input="clampReceivedField(item, 'metros_received', $event)"
                        @invalid="setInvalidReceiveMessage"
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
                <p v-if="receiveErrors.transfer" class="text-sm text-red-600">
                    {{ receiveErrors.transfer }}
                </p>
                <p v-if="receiveErrors.items" class="text-sm text-red-600">
                    {{ receiveErrors.items }}
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
