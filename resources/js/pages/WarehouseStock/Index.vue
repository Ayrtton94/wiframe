<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    stocks: {
        data: Array<{
            id: number;
            warehouse_id: number;
            store_id: number;
            kilos_available: number;
            metros_available: number;
            kilos_reserved: number;
            metros_reserved: number;
            warehouse: { name: string; code: string };
            store: { code_product: string; name_product: string };
        }>;
    };
    warehouses: Array<{ id: number; name: string; code: string }>;
    products: Array<{ id: number; code_product: string; name_product: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Stock por almacén', href: '/warehouse-stocks' },
];

const form = useForm({
    warehouse_id: '',
    store_id: '',
    kilos_available: 0,
    metros_available: 0,
    kilos_reserved: 0,
    metros_reserved: 0,
});

const saveStock = () => {
    form.post('/warehouse-stocks', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const removeStock = (stockId: number) => {
    router.delete(`/warehouse-stocks/${stockId}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Stock por almacén" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="rounded border bg-white p-4">
                <h2 class="mb-3 text-lg font-semibold">Asignar stock inicial / actualización</h2>
                <form class="grid gap-3 md:grid-cols-3" @submit.prevent="saveStock">
                    <select v-model="form.warehouse_id" class="rounded border px-3 py-2" required>
                        <option disabled value="">Selecciona almacén</option>
                        <option v-for="warehouse in props.warehouses" :key="warehouse.id" :value="warehouse.id">
                            {{ warehouse.code }} - {{ warehouse.name }}
                        </option>
                    </select>

                    <select v-model="form.store_id" class="rounded border px-3 py-2" required>
                        <option disabled value="">Selecciona producto</option>
                        <option v-for="product in props.products" :key="product.id" :value="product.id">
                            {{ product.code_product }} - {{ product.name_product }}
                        </option>
                    </select>

                    <input v-model.number="form.kilos_available" min="0" step="0.001" class="rounded border px-3 py-2" type="number" placeholder="Kilos disponibles" />
                    <input v-model.number="form.metros_available" min="0" step="0.001" class="rounded border px-3 py-2" type="number" placeholder="Metros disponibles" />
                    <input v-model.number="form.kilos_reserved" min="0" step="0.001" class="rounded border px-3 py-2" type="number" placeholder="Kilos reservados" />
                    <input v-model.number="form.metros_reserved" min="0" step="0.001" class="rounded border px-3 py-2" type="number" placeholder="Metros reservados" />

                    <button class="w-fit rounded bg-blue-600 px-3 py-2 text-white" :disabled="form.processing" type="submit">
                        Guardar stock
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto rounded border bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs uppercase">Almacén</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Producto</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Disponibles</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Reservados</th>
                            <th class="px-4 py-3 text-left text-xs uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="stock in props.stocks.data" :key="stock.id">
                            <td class="px-4 py-3 text-sm">{{ stock.warehouse.code }} - {{ stock.warehouse.name }}</td>
                            <td class="px-4 py-3 text-sm">{{ stock.store.code_product }} - {{ stock.store.name_product }}</td>
                            <td class="px-4 py-3 text-sm">{{ stock.kilos_available }} kg / {{ stock.metros_available }} m</td>
                            <td class="px-4 py-3 text-sm">{{ stock.kilos_reserved }} kg / {{ stock.metros_reserved }} m</td>
                            <td class="px-4 py-3 text-sm">
                                <button class="text-red-600" @click="removeStock(stock.id)">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
