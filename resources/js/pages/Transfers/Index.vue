<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

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
    warehouses: Array<{ id: number; name: string; code: string }>;
    products: Array<{ id: number; code_product: string; name_product: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Traslados',
        href: '/transfers',
    },
];

type TransferItemForm = {
    store_id: string;
    kilos: number;
    metros: number;
};

const form = useForm({
    from_warehouse_id: '',
    to_warehouse_id: '',
    notes: '',
    items: [{ store_id: '', kilos: 0, metros: 0 }] as TransferItemForm[],
});

const addItem = () => {
    form.items.push({ store_id: '', kilos: 0, metros: 0 });
};

const removeItem = (index: number) => {
    if (form.items.length === 1) {
        return;
    }

    form.items.splice(index, 1);
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        from_warehouse_id: Number(data.from_warehouse_id),
        to_warehouse_id: Number(data.to_warehouse_id),
        items: data.items.map((item) => ({
            store_id: Number(item.store_id),
            kilos: Number(item.kilos || 0),
            metros: Number(item.metros || 0),
        })),
    })).post('/transfers', {
        preserveScroll: true,
        onSuccess: () =>
            form.reset('from_warehouse_id', 'to_warehouse_id', 'notes', 'items'),
    });
};

</script>

<template>
    <Head title="Traslados" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-xl font-semibold">Traslados entre almacenes</h1>

            <div class="rounded border bg-white p-4">
                <h2 class="mb-3 text-lg font-semibold">Crear traslado</h2>

                <form class="space-y-3" @submit.prevent="submit">
                    <div class="grid gap-3 md:grid-cols-2">
                        <select v-model="form.from_warehouse_id" class="rounded border px-3 py-2" required>
                            <option disabled value="">Almacén origen</option>
                            <option v-for="warehouse in props.warehouses" :key="warehouse.id" :value="String(warehouse.id)">
                                {{ warehouse.code }} - {{ warehouse.name }}
                            </option>
                        </select>

                        <select v-model="form.to_warehouse_id" class="rounded border px-3 py-2" required>
                            <option disabled value="">Almacén destino</option>
                            <option v-for="warehouse in props.warehouses" :key="warehouse.id" :value="String(warehouse.id)">
                                {{ warehouse.code }} - {{ warehouse.name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="grid gap-2 rounded border p-3 md:grid-cols-[2fr_1fr_1fr_auto]"
                        >
                            <select v-model="item.store_id" class="rounded border px-3 py-2" required>
                                <option disabled value="">Producto</option>
                                <option v-for="product in props.products" :key="product.id" :value="String(product.id)">
                                    {{ product.code_product }} - {{ product.name_product }}
                                </option>
                            </select>

                            <input
                                v-model.number="item.kilos"
                                class="rounded border px-3 py-2"
                                min="0"
                                placeholder="Kilos"
                                step="0.001"
                                type="number"
                            />

                            <input
                                v-model.number="item.metros"
                                class="rounded border px-3 py-2"
                                min="0"
                                placeholder="Metros"
                                step="0.001"
                                type="number"
                            />

                            <button
                                class="rounded bg-red-100 px-3 py-2 text-red-600"
                                type="button"
                                @click="removeItem(index)"
                            >
                                Quitar
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button class="rounded bg-slate-600 px-3 py-2 text-white" type="button" @click="addItem">Agregar item</button>
                        <button class="rounded bg-blue-600 px-3 py-2 text-white" :disabled="form.processing" type="submit">Crear traslado</button>
                    </div>

                    <textarea
                        v-model="form.notes"
                        class="w-full rounded border px-3 py-2"
                        maxlength="1000"
                        placeholder="Notas (opcional)"
                        rows="2"
                    />

                    <p v-if="form.errors.items" class="text-sm text-red-600">{{ form.errors.items }}</p>
                    <p v-if="form.errors.from_warehouse_id" class="text-sm text-red-600">{{ form.errors.from_warehouse_id }}</p>
                    <p v-if="form.errors.to_warehouse_id" class="text-sm text-red-600">{{ form.errors.to_warehouse_id }}</p>

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
