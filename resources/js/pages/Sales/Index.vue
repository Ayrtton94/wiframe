<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    sales: {
        data: Array<{
            id: number;
            code: string;
            status: string;
            total: number;
            customer: { name: string };
            warehouse: { name: string; code: string };
            seller: { name: string };
            created_at: string;
        }>;
    };
    customers: Array<{ id: number; name: string; dni: string }>;
    warehouses: Array<{ id: number; name: string; code: string }>;
    products: Array<{
        id: number;
        code_product: string;
        name_product: string;
        public_price: number;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Ventas', href: '/sales' }];

type SaleItemForm = {
    store_id: string;
    unit: 'kilos' | 'metros';
    quantity: number;
};

const form = useForm({
    customer_id: '',
    warehouse_id: '',
    notes: '',
    items: [{ store_id: '', unit: 'metros', quantity: 1 }] as SaleItemForm[],
});

const addItem = () => {
    form.items.push({ store_id: '', unit: 'metros', quantity: 1 });
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
        customer_id: Number(data.customer_id),
        warehouse_id: Number(data.warehouse_id),
        items: data.items.map((item) => ({
            store_id: Number(item.store_id),
            unit: item.unit,
            quantity: Number(item.quantity),
        })),
    })).post('/sales', {
        preserveScroll: true,
        onSuccess: () =>
            form.reset('customer_id', 'warehouse_id', 'notes', 'items'),
    });
};

const productMap = computed(
    () => new Map(props.products.map((product) => [product.id, product])),
);

const estimateLineTotal = (item: SaleItemForm) => {
    const product = productMap.value.get(Number(item.store_id));
    if (!product) {
        return 0;
    }

    return (Number(item.quantity || 0) * Number(product.public_price)).toFixed(
        2,
    );
};
</script>

<template>
    <Head title="Ventas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <h1 class="text-2xl font-semibold text-slate-900">
                Ventas básicas
            </h1>

            <section class="rounded-xl border bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold">Registrar venta</h2>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-3 md:grid-cols-2">
                        <select
                            v-model="form.customer_id"
                            class="rounded-lg border px-3 py-2"
                            required
                        >
                            <option disabled value="">
                                Selecciona cliente
                            </option>
                            <option
                                v-for="customer in props.customers"
                                :key="customer.id"
                                :value="String(customer.id)"
                            >
                                {{ customer.dni }} - {{ customer.name }}
                            </option>
                        </select>

                        <select
                            v-model="form.warehouse_id"
                            class="rounded-lg border px-3 py-2"
                            required
                        >
                            <option disabled value="">
                                Selecciona almacén / tienda
                            </option>
                            <option
                                v-for="warehouse in props.warehouses"
                                :key="warehouse.id"
                                :value="String(warehouse.id)"
                            >
                                {{ warehouse.code }} - {{ warehouse.name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="grid gap-3 rounded-lg border p-4 md:grid-cols-[2fr_1fr_1fr_auto]"
                        >
                            <select
                                v-model="item.store_id"
                                class="rounded-lg border px-3 py-2"
                                required
                            >
                                <option disabled value="">
                                    Selecciona producto
                                </option>
                                <option
                                    v-for="product in props.products"
                                    :key="product.id"
                                    :value="String(product.id)"
                                >
                                    {{ product.code_product }} -
                                    {{ product.name_product }}
                                </option>
                            </select>

                            <select
                                v-model="item.unit"
                                class="rounded-lg border px-3 py-2"
                            >
                                <option value="metros">Metros</option>
                                <option value="kilos">Kilos</option>
                            </select>

                            <input
                                v-model.number="item.quantity"
                                type="number"
                                min="0.001"
                                step="0.001"
                                placeholder="Cantidad"
                                class="rounded-lg border px-3 py-2"
                                required
                            />

                            <button
                                type="button"
                                class="rounded-lg bg-red-100 px-3 py-2 text-red-600"
                                @click="removeItem(index)"
                            >
                                Quitar
                            </button>

                            <p class="text-sm text-slate-500 md:col-span-4">
                                Estimado: S/ {{ estimateLineTotal(item) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-lg bg-slate-700 px-4 py-2 text-white"
                            @click="addItem"
                        >
                            Agregar producto
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-white"
                        >
                            Guardar venta
                        </button>
                    </div>

                    <textarea
                        v-model="form.notes"
                        rows="3"
                        maxlength="1000"
                        placeholder="Notas de la venta (opcional)"
                        class="w-full rounded-lg border px-3 py-2"
                    />

                    <p
                        v-if="form.errors.customer_id"
                        class="text-sm text-red-600"
                    >
                        {{ form.errors.customer_id }}
                    </p>
                    <p
                        v-if="form.errors.warehouse_id"
                        class="text-sm text-red-600"
                    >
                        {{ form.errors.warehouse_id }}
                    </p>
                    <p v-if="form.errors.items" class="text-sm text-red-600">
                        {{ form.errors.items }}
                    </p>
                </form>
            </section>

            <section
                class="overflow-hidden rounded-xl border bg-white shadow-sm"
            >
                <div class="border-b px-5 py-4">
                    <h2 class="text-lg font-semibold">Ventas registradas</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Código
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Cliente
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Ubicación
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Vendedor
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Total
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase"
                                >
                                    Acción
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="sale in props.sales.data" :key="sale.id">
                                <td class="px-4 py-3 text-sm">
                                    {{ sale.code }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ sale.customer.name }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ sale.warehouse.code }} -
                                    {{ sale.warehouse.name }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ sale.seller.name }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    S/ {{ sale.total }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ sale.status }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <Link
                                        :href="`/sales/${sale.id}`"
                                        class="text-blue-600"
                                        >Ver detalle</Link
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>