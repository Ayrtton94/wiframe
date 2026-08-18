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

const clampReceivedField = (
    item: ReceiveItemForm,
    field: 'kilos_received' | 'metros_received',
    event: Event,
) => {
    clearInvalidReceiveMessage(event);

    const value = Number(item[field] || 0);

    if (value < 0) {
        item[field] = 0 as any;
        return;
    }

    const receiveItems =
        receiveForm.items as ReceiveItemForm[];

    const index = receiveItems.findIndex(
        (receiveItem) =>
            receiveItem.transfer_item_id ===
            item.transfer_item_id,
    );

    const shipped = props.transfer.items[index];

    if (!shipped) {
        return;
    }

    const maxValue =
        field === 'kilos_received'
            ? shipped.kilos_shipped
            : shipped.metros_shipped;

    if (value > maxValue) {
        item[field] = maxValue as any;
    }
};

const formatNumber = (
    value: number | string,
    decimals = 3,
) => {
    const number = Number(value);

    if (!Number.isFinite(number)) {
        return '0';
    }

    return number
        .toFixed(decimals)
        .replace(/\.?0+$/, '');
};

</script>
<template>
    <Head
        :title="`Traslado ${props.transfer.code}`"
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-5
                   rounded-xl bg-slate-50 p-4
                   dark:bg-slate-950"
        >
            <!-- ENCABEZADO -->
            <section
                class="rounded-xl border
                       border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div
                    class="flex flex-col gap-3
                           sm:flex-row
                           sm:items-center
                           sm:justify-between"
                >
                    <div>
                        <h1
                            class="text-2xl font-bold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            {{ props.transfer.code }}
                        </h1>

                        <p
                            class="mt-1 text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            {{ props.transfer.from_warehouse.name }}
                            →
                            {{ props.transfer.to_warehouse.name }}
                        </p>
                    </div>

                    <span
                        class="inline-flex w-fit
                               rounded-full px-3 py-1
                               text-xs font-semibold"
                        :class="{
                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400':
                                props.transfer.status ===
                                'pending',

                            'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400':
                                props.transfer.status ===
                                'completed',

                            'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400':
                                props.transfer.status ===
                                'approved',

                            'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400':
                                props.transfer.status ===
                                'cancelled',
                        }"
                    >
                        {{ props.transfer.status }}
                    </span>
                </div>
            </section>

            <!-- DETALLE DE PRODUCTOS -->
            <section
                class="overflow-hidden rounded-xl
                       border border-slate-200
                       bg-white shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div
                    class="border-b border-slate-200
                           bg-slate-50 px-5 py-4
                           dark:border-slate-700
                           dark:bg-slate-800"
                >
                    <h2
                        class="text-lg font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        Productos del traslado
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y
                               divide-slate-200
                               dark:divide-slate-700"
                    >
                        <thead
                            class="bg-slate-100
                                   dark:bg-slate-800"
                        >
                            <tr>
                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase tracking-wide
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Producto
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase tracking-wide
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Solicitado
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase tracking-wide
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Despachado
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y
                                   divide-slate-200
                                   dark:divide-slate-700"
                        >
                            <tr
                                v-for="item in props.transfer.items"
                                :key="item.id"
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
                                <td
                                    class="px-4 py-4 text-sm"
                                >
                                    <p
                                        class="font-medium
                                               text-slate-900
                                               dark:text-slate-100"
                                    >
                                        {{
                                            item.store.code_product
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-sm
                                               text-slate-500
                                               dark:text-slate-400"
                                    >
                                        {{
                                            item.store.name_product
                                        }}
                                    </p>
                                </td>

                                <td
                                    class="px-4 py-4 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    <div>
                                        {{ formatNumber(item.kilos_requested) }}
                                        rollos
                                    </div>

                                    <div
                                        class="mt-1 text-xs
                                               text-slate-500
                                               dark:text-slate-400"
                                    >
                                        {{ formatNumber(item.metros_requested) }}
                                        metros
                                    </div>
                                </td>

                                <td
                                    class="px-4 py-4 text-sm
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    <div>
                                        {{ formatNumber(item.kilos_shipped) }}
                                        rollos
                                    </div>

                                    <div
                                        class="mt-1 text-xs
                                               text-slate-500
                                               dark:text-slate-400"
                                    >
                                        {{ formatNumber(item.metros_shipped) }}
                                        metros
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    props.transfer.items.length ===
                                    0
                                "
                            >
                                <td
                                    colspan="3"
                                    class="px-4 py-8 text-center
                                           text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay productos registrados
                                    en este traslado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- APROBAR -->
            <section
                v-if="props.permissions.can_approve"
                class="space-y-3 rounded-xl
                       border border-amber-200
                       bg-white p-5 shadow-sm
                       dark:border-amber-500/30
                       dark:bg-slate-900"
            >
                <div>
                    <h2
                        class="text-lg font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        Aprobar traslado
                    </h2>

                    <p
                        class="mt-1 text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Acepta el traslado desde el almacén destino.
                    </p>
                </div>

                <textarea
                    v-model="approveForm.notes"
                    rows="2"
                    placeholder="Notas de aprobación (opcional)"
                    class="w-full rounded-lg
                           border border-slate-300
                           bg-white px-3 py-2
                           text-sm text-slate-900
                           placeholder:text-slate-400
                           focus:border-amber-500
                           focus:outline-none
                           focus:ring-2
                           focus:ring-amber-500/20
                           dark:border-slate-600
                           dark:bg-slate-800
                           dark:text-slate-100
                           dark:placeholder:text-slate-500"
                ></textarea>

                <p
                    v-if="approveErrors.transfer"
                    class="text-sm text-red-600
                           dark:text-red-400"
                >
                    {{ approveErrors.transfer }}
                </p>

                <button
                    type="button"
                    class="rounded-lg bg-amber-600
                           px-4 py-2.5 text-sm
                           font-medium text-white
                           transition hover:bg-amber-700
                           disabled:cursor-not-allowed
                           disabled:opacity-50
                           dark:hover:bg-amber-500"
                    :disabled="approveForm.processing"
                    @click="approveTransfer"
                >
                    {{
                        approveForm.processing
                            ? 'Procesando...'
                            : 'Aceptar traslado'
                    }}
                </button>
            </section>

            <!-- DESPACHAR -->
            <section
                v-if="props.permissions.can_ship"
                class="space-y-3 rounded-xl
                       border border-blue-200
                       bg-white p-5 shadow-sm
                       dark:border-blue-500/30
                       dark:bg-slate-900"
            >
                <div>
                    <h2
                        class="text-lg font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        Despachar traslado
                    </h2>

                    <p
                        class="mt-1 text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Confirma el despacho del traslado.
                    </p>
                </div>

                <textarea
                    v-model="shipForm.notes"
                    rows="2"
                    placeholder="Notas del despacho (opcional)"
                    class="w-full rounded-lg
                           border border-slate-300
                           bg-white px-3 py-2
                           text-sm text-slate-900
                           placeholder:text-slate-400
                           focus:border-blue-500
                           focus:outline-none
                           focus:ring-2
                           focus:ring-blue-500/20
                           dark:border-slate-600
                           dark:bg-slate-800
                           dark:text-slate-100
                           dark:placeholder:text-slate-500"
                ></textarea>

                <p
                    v-if="shipErrors.transfer"
                    class="text-sm text-red-600
                           dark:text-red-400"
                >
                    {{ shipErrors.transfer }}
                </p>

                <p
                    v-if="shipErrors.stock"
                    class="text-sm text-red-600
                           dark:text-red-400"
                >
                    {{ shipErrors.stock }}
                </p>

                <button
                    type="button"
                    class="rounded-lg bg-blue-600
                           px-4 py-2.5 text-sm
                           font-medium text-white
                           transition hover:bg-blue-700
                           disabled:cursor-not-allowed
                           disabled:opacity-50
                           dark:hover:bg-blue-500"
                    :disabled="shipForm.processing"
                    @click="shipTransfer"
                >
                    {{
                        shipForm.processing
                            ? 'Procesando...'
                            : 'Confirmar despacho'
                    }}
                </button>
            </section>

            <!-- RECEPCIONAR -->
            <section
                v-if="props.permissions.can_receive"
                class="space-y-4 rounded-xl
                       border border-emerald-200
                       bg-white p-5 shadow-sm
                       dark:border-emerald-500/30
                       dark:bg-slate-900"
            >
                <div>
                    <h2
                        class="text-lg font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        Recepcionar traslado
                    </h2>

                    <p
                        class="mt-1 text-sm
                               text-slate-500
                               dark:text-slate-400"
                    >
                        Registra las cantidades realmente recibidas.
                    </p>
                </div>

                <div
                    v-for="(item, index) in receiveForm.items"
                    :key="item.transfer_item_id"
                    class="grid gap-3 rounded-xl
                           border border-slate-200
                           bg-slate-50 p-4
                           dark:border-slate-700
                           dark:bg-slate-800
                           md:grid-cols-2"
                >
                    <!-- ROLLOS RECIBIDOS -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Rollos recibidos
                        </label>

                        <input
                            v-model.number="
                                item.kilos_received
                            "
                            type="number"
                            min="0"
                            :max="
                                props.transfer.items[index]
                                    .kilos_shipped
                            "
                            step="0.001"
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-sm
                                   text-slate-900
                                   focus:border-emerald-500
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-emerald-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-900
                                   dark:text-slate-100"
                            placeholder="Rollos recibidos"
                            @input="
                                clampReceivedField(
                                    item,
                                    'kilos_received',
                                    $event,
                                )
                            "
                            @invalid="
                                setInvalidReceiveMessage
                            "
                        />
                    </div>

                    <!-- METROS RECIBIDOS -->
                    <div>
                        <label
                            class="mb-1 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Metros recibidos
                        </label>

                        <input
                            v-model.number="
                                item.metros_received
                            "
                            type="number"
                            min="0"
                            :max="
                                props.transfer.items[index]
                                    .metros_shipped
                            "
                            step="0.001"
                            class="w-full rounded-lg
                                   border border-slate-300
                                   bg-white px-3 py-2
                                   text-sm
                                   text-slate-900
                                   focus:border-emerald-500
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-emerald-500/20
                                   dark:border-slate-600
                                   dark:bg-slate-900
                                   dark:text-slate-100"
                            placeholder="Metros recibidos"
                            @input="
                                clampReceivedField(
                                    item,
                                    'metros_received',
                                    $event,
                                )
                            "
                            @invalid="
                                setInvalidReceiveMessage
                            "
                        />
                    </div>

                    <p
                        class="text-xs
                               text-slate-500
                               dark:text-slate-400
                               md:col-span-2"
                    >
                        Item {{ index + 1 }} · máximo:
                        {{
                            props.transfer.items[index]
                                .kilos_shipped
                        }}
                        rollos /
                        {{
                            props.transfer.items[index]
                                .metros_shipped
                        }}
                        metros
                    </p>
                </div>

                <!-- NOTAS RECEPCIÓN -->
                <textarea
                    v-model="receiveForm.notes"
                    rows="2"
                    placeholder="Notas de recepción (opcional)"
                    class="w-full rounded-lg
                           border border-slate-300
                           bg-white px-3 py-2
                           text-sm text-slate-900
                           placeholder:text-slate-400
                           focus:border-emerald-500
                           focus:outline-none
                           focus:ring-2
                           focus:ring-emerald-500/20
                           dark:border-slate-600
                           dark:bg-slate-800
                           dark:text-slate-100
                           dark:placeholder:text-slate-500"
                ></textarea>

                <p
                    v-if="receiveErrors.transfer"
                    class="text-sm text-red-600
                           dark:text-red-400"
                >
                    {{ receiveErrors.transfer }}
                </p>

                <p
                    v-if="receiveErrors.items"
                    class="text-sm text-red-600
                           dark:text-red-400"
                >
                    {{ receiveErrors.items }}
                </p>

                <button
                    type="button"
                    class="rounded-lg bg-green-600
                           px-4 py-2.5 text-sm
                           font-medium text-white
                           transition hover:bg-green-700
                           disabled:cursor-not-allowed
                           disabled:opacity-50
                           dark:hover:bg-green-500"
                    :disabled="receiveForm.processing"
                    @click="receiveTransfer"
                >
                    {{
                        receiveForm.processing
                            ? 'Procesando...'
                            : 'Confirmar recepción'
                    }}
                </button>
            </section>
        </div>
    </AppLayout>
</template>