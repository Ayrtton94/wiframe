<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

type PriceType = 'price' | 'public' | 'wholesale' | 'price_roll' | 'special';

type ProductOption = {
    id: number;
    code_product: string;
    name_product: string;
    price: number;
    public_price: number;
    wholesale_price: number;
    price_roll: number;
    special_price: number;
    kilos: number;
    metros: number;
};

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
    defaultWarehouseId: number | null;
    warehouseStocks: Array<{
        warehouse_id: number;
        store_id: number;
        kilos_available: number | string;
        metros_available: number | string;
    }>;
    products: ProductOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Ventas', href: '/sales' }];

type SaleItemForm = {
    store_id: string;
    unit: 'kilos' | 'metros';
    quantity: number;
    price_type: PriceType;
    search_text: string;
};

const defaultWarehouseId = props.defaultWarehouseId ? String(props.defaultWarehouseId) : props.warehouses.length === 1 ? String(props.warehouses[0].id) : '';

const form = useForm({
    customer_id: '',
    warehouse_id: defaultWarehouseId,
    notes: '',
    items: [{ store_id: '', unit: 'metros', quantity: 1, price_type: 'public' as PriceType, search_text: '' }] as SaleItemForm[],
});

const addItem = () => {
    form.items.push({ store_id: '', unit: 'metros', quantity: 1, price_type: 'public', search_text: '' });
};

const removeItem = (index: number) => {
    if (form.items.length === 1) {
        return;
    }

    form.items.splice(index, 1);
};

const productMap = computed(
    () => new Map(props.products.map((product: ProductOption) => [product.id, product])),
);

const warehouseStockMap = computed(() => {
    const map = new Map<string, { kilos_available: number; metros_available: number }>();

    props.warehouseStocks.forEach((stock: { warehouse_id: number; store_id: number; kilos_available: number | string; metros_available: number | string }) => {
        map.set(`${stock.warehouse_id}:${stock.store_id}`, {
            kilos_available: Number(stock.kilos_available || 0),
            metros_available: Number(stock.metros_available || 0),
        });
    });

    return map;
});

watch(
    () => form.warehouse_id,
    (warehouseId) => {
        form.items.forEach((item: SaleItemForm) => {
            if (!item.store_id) {
                return;
            }

            if (!warehouseId) {
                item.store_id = '';
                item.search_text = '';
                return;
            }

            const stock = getStockForItem(item);
            const available = item.unit === 'kilos' ? stock?.kilos_available ?? 0 : stock?.metros_available ?? 0;

            if (!stock || available <= 0) {
                item.store_id = '';
                item.search_text = '';
            }
        });
    },
);

const getProductPriceOptions = (product?: ProductOption) => {
    if (!product) {
        return [];
    }

    const options: Array<{ label: string; value: PriceType; price: number }> = [
        { label: 'Precio base', value: 'price', price: Number(product.price || 0) },
        { label: 'Precio público', value: 'public', price: Number(product.public_price || 0) },
        { label: 'Precio mayorista', value: 'wholesale', price: Number(product.wholesale_price || 0) },
    ];

    if (Number(product.price_roll || 0) > 0) {
        options.push({
            label: 'Precio por rollo',
            value: 'price_roll',
            price: Number(product.price_roll || 0),
        });
    }

    if (Number(product.special_price || 0) > 0) {
        options.push({
            label: 'Precio especial',
            value: 'special',
            price: Number(product.special_price || 0),
        });
    }

    return options.filter((option) => option.price > 0);
};

const getSelectedProductPrice = (item: SaleItemForm) => {
    const product = productMap.value.get(Number(item.store_id));
    if (!product) {
        return 0;
    }

    switch (item.price_type) {
        case 'wholesale':
            return Number(product.wholesale_price || 0);
        case 'price_roll':
            return Number(product.price_roll || 0);
        case 'special':
            return Number(product.special_price || 0);
        case 'price':
            return Number(product.price || 0);
        default:
            return Number(product.public_price || 0);
    }
};

const estimateLineTotal = (item: SaleItemForm) => {
    return (Number(item.quantity || 0) * getSelectedProductPrice(item)).toFixed(2);
};

const getStockForItem = (item: SaleItemForm) => {
    const warehouseId = Number(form.warehouse_id);
    const storeId = Number(item.store_id);

    if (!storeId) {
        return null;
    }

    if (!warehouseId) {
        const product = props.products.find((entry: ProductOption) => entry.id === storeId);
        return product
            ? {
                  kilos_available: Number(product.kilos || 0),
                  metros_available: Number(product.metros || 0),
              }
            : null;
    }

    const stockFromMap = warehouseStockMap.value.get(`${warehouseId}:${storeId}`);
    if (stockFromMap) {
        return stockFromMap;
    }

    return null;
};

const getAvailableProductsForWarehouse = (item: SaleItemForm) => {
    if (!form.warehouse_id) {
        return props.products;
    }

    const warehouseId = Number(form.warehouse_id);
    return props.products.filter((product: ProductOption) => {
        const stock = warehouseStockMap.value.get(`${warehouseId}:${product.id}`);
        return stock ? stock.kilos_available > 0 || stock.metros_available > 0 : false;
    });
};

const getFilteredProducts = (item: SaleItemForm) => {
    const term = (item.search_text || '').trim().toLowerCase();
    const sourceProducts = getAvailableProductsForWarehouse(item);

    if (!term) {
        return sourceProducts;
    }

    return sourceProducts.filter((product: ProductOption) => {
        const haystack = `${product.code_product} ${product.name_product}`.toLowerCase();
        return haystack.includes(term);
    });
};

const getSelectedProduct = (item: SaleItemForm) => {
    if (!item.store_id) {
        return null;
    }

    return props.products.find((product: ProductOption) => String(product.id) === item.store_id) ?? null;
};

const selectProduct = (item: SaleItemForm, productId: number) => {
    const product = props.products.find((entry: ProductOption) => entry.id === productId);
    if (!product) {
        return;
    }

    item.store_id = String(product.id);
    item.search_text = `${product.code_product} - ${product.name_product}`;
};

const getAvailableForItem = (item: SaleItemForm) => {
    const stock = getStockForItem(item);
    if (!stock) {
        return 0;
    }

    return item.unit === 'kilos' ? stock.kilos_available : stock.metros_available;
};

const getStockMessage = (item: SaleItemForm) => {
    const stock = getStockForItem(item);

    if (!stock) {
        return 'Sin stock configurado para esta ubicación';
    }

    const unitLabel = item.unit === 'kilos' ? 'rollos' : 'metros';
    const available = item.unit === 'kilos' ? stock.kilos_available : stock.metros_available;

    return `Stock disponible: ${available} ${unitLabel}`;
};

const hasStockForItem = (item: SaleItemForm) => {
    const stock = getStockForItem(item);

    if (!stock) {
        return false;
    }

    const available = item.unit === 'kilos' ? stock.kilos_available : stock.metros_available;
    return available > 0;
};

const clearSaleQuantityInvalidMessage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    target.setCustomValidity('');
};

const setSaleQuantityInvalidMessage = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (target.validity.rangeUnderflow) {
        target.setCustomValidity('El valor no puede ser negativo');
    } else if (target.validity.rangeOverflow) {
        target.setCustomValidity('No puedes solicitar más del stock disponible');
    } else {
        target.setCustomValidity('');
    }
};

const clampSaleItemQuantity = (item: SaleItemForm, event: Event) => {
    clearSaleQuantityInvalidMessage(event);
    const quantity = Number(item.quantity || 0);

    if (quantity < 0) {
        item.quantity = 0 as any;
        return;
    }

    const available = getAvailableForItem(item);
    if (quantity > available) {
        item.quantity = available as any;
    }
};

const validateItemStock = (item: SaleItemForm) => {
    if (!form.warehouse_id) {
        window.alert('Selecciona un almacén o tienda antes de elegir productos.');
        return false;
    }

    const stock = getStockForItem(item);

    if (!stock) {
        window.alert('El producto seleccionado no tiene stock configurado en la ubicación seleccionada.');
        return false;
    }

    const available = item.unit === 'kilos' ? stock.kilos_available : stock.metros_available;
    const requested = Number(item.quantity || 0);

    if (available <= 0) {
        window.alert('El producto seleccionado no tiene stock disponible en esta ubicación.');
        return false;
    }

    if (requested > available) {
        window.alert(`La cantidad solicitada supera el stock disponible (${available} ${item.unit === 'kilos' ? 'kilos' : 'metros'}).`);
        return false;
    }

    return true;
};

const submit = () => {
    const hasValidStock = form.items.every((item: SaleItemForm) => validateItemStock(item));

    if (!hasValidStock) {
        return;
    }

    form.transform((data: any) => ({
        ...data,
        customer_id: Number(data.customer_id),
        warehouse_id: Number(data.warehouse_id),
        items: data.items.map((item: SaleItemForm) => ({
            store_id: Number(item.store_id),
            unit: item.unit,
            quantity: Number(item.quantity),
            price_type: item.price_type,
        })),
    })).post('/sales', {
        preserveScroll: true,
        onSuccess: () =>
            form.reset('customer_id', 'warehouse_id', 'notes', 'items'),
    });
};
</script>

<template>
    <Head title="Salidas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <h1 class="text-2xl font-semibold text-slate-900">
                Salidas básicas
            </h1>

            <section class="rounded-xl border bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold">Registrar salida</h2>

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
                            class="grid gap-3 rounded-lg border p-4 md:grid-cols-[2fr_1fr_1fr_1fr_auto]"
                        >
                            <div class="space-y-2">
                                <input
                                    v-model="item.search_text"
                                    type="text"
                                    class="w-full rounded-lg border px-3 py-2"
                                    placeholder="Buscar por código o nombre"
                                />
                                <div class="max-h-40 overflow-y-auto rounded-lg border bg-slate-50 p-2">
                                    <p class="mb-2 text-xs font-medium uppercase text-slate-500">
                                        Resultados
                                    </p>
                                    <button
                                        v-for="product in getFilteredProducts(item)"
                                        :key="product.id"
                                        type="button"
                                        class="mb-1 flex w-full items-center justify-between rounded px-2 py-2 text-left text-sm hover:bg-slate-200"
                                        @click="selectProduct(item, product.id)"
                                    >
                                        <span>{{ product.code_product }} - {{ product.name_product }}</span>
                                        <span class="text-xs text-slate-500">Seleccionar</span>
                                    </button>
                                    <p v-if="!getFilteredProducts(item).length" class="text-sm text-slate-500">
                                        No hay productos que coincidan con la búsqueda.
                                    </p>
                                </div>
                                <p v-if="getSelectedProduct(item)" class="text-sm text-slate-600">
                                    Producto seleccionado: {{ getSelectedProduct(item)?.code_product }} - {{ getSelectedProduct(item)?.name_product }}
                                </p>
                            </div>

                            <select
                                v-model="item.unit"
                                class="rounded-lg border px-3 py-2"
                            >
                                <option value="metros">Metros</option>
                                <option value="kilos">Rollos</option>
                            </select>

                            <input
                                v-model.number="item.quantity"
                                type="number"
                                min="0.001"
                                :max="getAvailableForItem(item) || undefined"
                                step="0.001"
                                placeholder="Cantidad"
                                class="rounded-lg border px-3 py-2"
                                required
                                @input="clampSaleItemQuantity(item, $event)"
                                @invalid="setSaleQuantityInvalidMessage"
                            />

                            <div v-if="getProductPriceOptions(productMap.get(Number(item.store_id))).length > 1" class="w-full">
                                <select
                                    v-model="item.price_type"
                                    class="w-full rounded-lg border px-3 py-2"
                                >
                                    <option
                                        v-for="option in getProductPriceOptions(productMap.get(Number(item.store_id)))"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                            <div v-else class="flex items-center text-sm text-slate-500">
                                Precio: S/ {{ getSelectedProductPrice(item) }}
                            </div>

                            <button
                                type="button"
                                class="rounded-lg bg-red-100 px-3 py-2 text-red-600"
                                @click="removeItem(index)"
                            >
                                Quitar
                            </button>

                            <p :class="['text-sm md:col-span-5', hasStockForItem(item) ? 'text-slate-500' : 'text-red-600']">
                                {{ getStockMessage(item) }} · Estimado: S/ {{ estimateLineTotal(item) }}
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
                            Guardar salida
                        </button>
                    </div>

                    <textarea
                        v-model="form.notes"
                        rows="3"
                        maxlength="1000"
                        placeholder="Notas de la salida (opcional)"
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
                    <h2 class="text-lg font-semibold">Salidas registradas</h2>
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