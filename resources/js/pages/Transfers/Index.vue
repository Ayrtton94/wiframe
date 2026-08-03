<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const roles = (page.props.auth?.roles ?? []) as string[];
const isAdmin = roles.includes('admin');
const isAlmacen = roles.includes('almacen');

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
    sourceWarehouses: Array<{ id: number; name: string; code: string }>;
    destinationWarehouses: Array<{ id: number; name: string; code: string }>;
    defaultSourceWarehouseId: number | null;
    products: Array<{ id: number; code_product: string; name_product: string }>;
    warehouseStocks: Array<{
        warehouse_id: number;
        store_id: number;
        kilos_available: number | string;
        metros_available: number | string;
    }>;
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

const defaultSourceWarehouseId = props.defaultSourceWarehouseId ? String(props.defaultSourceWarehouseId) : '';

const form = useForm({
    from_warehouse_id: defaultSourceWarehouseId,
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

const warehouseStockMap = computed(() => {
    const map = new Map<string, { kilos_available: number; metros_available: number }>();

    props.warehouseStocks.forEach((stock) => {
        map.set(`${stock.warehouse_id}:${stock.store_id}`, {
            kilos_available: Number(stock.kilos_available || 0),
            metros_available: Number(stock.metros_available || 0),
        });
    });

    return map;
});

const availableProducts = computed(() => {
    if (!form.from_warehouse_id) {
        return props.products;
    }

    const warehouseId = Number(form.from_warehouse_id);

    return props.products.filter((product) => {
        const stock = warehouseStockMap.value.get(`${warehouseId}:${product.id}`);
        return stock ? stock.kilos_available > 0 || stock.metros_available > 0 : false;
    });
});

const getStockForItem = (item: TransferItemForm) => {
    if (!form.from_warehouse_id || !item.store_id) {
        return null;
    }

    const warehouseId = Number(form.from_warehouse_id);
    const storeId = Number(item.store_id);

    return warehouseStockMap.value.get(`${warehouseId}:${storeId}`) ?? null;
};

const getProductLabel = (productId: string) => {
    const product = props.products.find((product) => String(product.id) === productId);
    return product ? `${product.code_product} - ${product.name_product}` : 'Producto';
};

const stockErrors = computed(() => {
    return form.items.flatMap((item) => {
        const errors: string[] = [];
        const stock = getStockForItem(item);

        if (!stock || !item.store_id) {
            return errors;
        }

        const kilos = Number(item.kilos || 0);
        const metros = Number(item.metros || 0);

        if (kilos > stock.kilos_available) {
            errors.push(`${getProductLabel(item.store_id)}: Rollos no puede ser mayor a ${formatNumber(stock.kilos_available, 3)}`);
        }

        if (metros > stock.metros_available) {
            errors.push(`${getProductLabel(item.store_id)}: Metros no puede ser mayor a ${formatNumber(stock.metros_available, 3)}`);
        }

        return errors;
    });
});

const hasStockError = computed(() => stockErrors.value.length > 0);

const setNegativeOrOverflowMessage = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (target.validity.rangeUnderflow) {
        target.setCustomValidity('El valor no puede ser negativo');
    } else if (target.validity.rangeOverflow) {
        target.setCustomValidity('El valor no puede ser mayor al stock disponible');
    } else {
        target.setCustomValidity('');
    }
};

const clearInvalidMessage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    target.setCustomValidity('');
};

const clampTransferField = (field: 'kilos' | 'metros', item: TransferItemForm, event: Event) => {
    clearInvalidMessage(event);

    if (Number(item[field]) < 0) {
        item[field] = 0 as any;
        return;
    }

    const stock = getStockForItem(item);
    if (!stock) {
        return;
    }

    const maxValue = field === 'kilos' ? stock.kilos_available : stock.metros_available;
    if (Number(item[field]) > maxValue) {
        item[field] = maxValue as any;
    }
};

const formatNumber = (value: number | string, fractionDigits = 0) => {
    const numberValue = Number(value);

    if (Number.isNaN(numberValue)) {
        return String(value);
    }

    if (fractionDigits <= 0 && Number.isInteger(numberValue)) {
        return numberValue.toString();
    }

    return numberValue.toFixed(fractionDigits).replace(/\.0+$/, '');
};

const submit = () => {
    if (hasStockError.value) {
        window.alert(stockErrors.value.join('\n'));
        return;
    }

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
        <div class="min-h-full bg-slate-100 p-6">

            <!-- Encabezado -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">
                        Traslados entre almacenes
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Administra los traslados de productos entre almacenes.
                    </p>
                </div>
            </div>

            <!-- FORMULARIO -->
            <div class="mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b bg-slate-50 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-800">
                        Nuevo traslado
                    </h2>

                    <p class="text-sm text-slate-500">
                        Complete la información del traslado.
                    </p>
                </div>

                <form
                    class="space-y-6 p-6"
                    @submit.prevent="submit"
                >

                    <!-- Almacenes -->
                    <div class="grid gap-4 md:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Almacén origen
                            </label>

                            <select
                                v-model="form.from_warehouse_id"
                                required
                                class="h-11 w-full rounded-lg border border-slate-300 px-3 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                                <option disabled value="">
                                    Seleccione...
                                </option>

                                <option
                                    v-for="warehouse in props.sourceWarehouses"
                                    :key="warehouse.id"
                                    :value="String(warehouse.id)"
                                >
                                    {{ warehouse.code }} - {{ warehouse.name }}
                                </option>

                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">
                                Almacén destino
                            </label>

                            <select
                                v-model="form.to_warehouse_id"
                                required
                                class="h-11 w-full rounded-lg border border-slate-300 px-3 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                                <option disabled value="">
                                    Seleccione...
                                </option>

                                <option
                                    v-for="warehouse in props.destinationWarehouses"
                                    :key="warehouse.id"
                                    :value="String(warehouse.id)"
                                >
                                    {{ warehouse.code }} - {{ warehouse.name }}
                                </option>

                            </select>
                        </div>

                    </div>

                    <!-- Productos -->
                    <div>

                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold text-slate-700">
                                Productos
                            </h3>

                            <button
                                type="button"
                                @click="addItem"
                                class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
                            >
                                + Agregar producto
                            </button>
                        </div>

                        <div class="space-y-4">

                            <div
                                v-for="(item,index) in form.items"
                                :key="index"
                                class="grid gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[2fr_1fr_1fr_auto]"
                            >

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        Producto
                                    </label>

                                    <select
                                        v-model="item.store_id"
                                        required
                                        class="h-11 w-full rounded-lg border border-slate-300 px-3"
                                    >
                                        <option disabled value="">
                                            Seleccione...
                                        </option>

                                        <option
                                            v-for="product in availableProducts"
                                            :key="product.id"
                                            :value="String(product.id)"
                                        >
                                            {{ product.code_product }} -
                                            {{ product.name_product }}
                                        </option>

                                    </select>

                                    <p v-if="getStockForItem(item)"
                                        class="mt-2 text-sm text-slate-500"
                                    >
                                        Stock origen:
                                        {{ formatNumber(getStockForItem(item)?.kilos_available ?? 0, 3) }}
                                        rollos ·
                                        {{ formatNumber(getStockForItem(item)?.metros_available ?? 0, 3) }}
                                        metros
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        Rollos
                                    </label>

                                    <input
                                        v-model.number="item.kilos"
                                        type="number"
                                        min="0"
                                        :max="getStockForItem(item)?.kilos_available ?? undefined"
                                        step="0.001"
                                        class="h-11 w-full rounded-lg border border-slate-300 px-3"
                                        @input="clampTransferField('kilos', item, $event)"
                                        @invalid="setNegativeOrOverflowMessage"
                                    >
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        Metros
                                    </label>

                                    <input
                                        v-model.number="item.metros"
                                        type="number"
                                        min="0"
                                        :max="getStockForItem(item)?.metros_available ?? undefined"
                                        step="0.001"
                                        class="h-11 w-full rounded-lg border border-slate-300 px-3"
                                        @input="clampTransferField('metros', item, $event)"
                                        @invalid="setNegativeOrOverflowMessage"
                                    >
                                </div>

                                <div class="flex items-end">

                                    <button
                                        type="button"
                                        @click="removeItem(index)"
                                        class="h-11 rounded-lg bg-red-100 px-4 font-medium text-red-600 transition hover:bg-red-200"
                                    >
                                        Eliminar
                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- Notas -->
                    <div v-if="hasStockError" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <p class="font-semibold">No se puede crear el traslado porque la cantidad excede el stock disponible:</p>
                        <ul class="mt-2 list-disc pl-5">
                            <li v-for="error in stockErrors" :key="error">{{ error }}</li>
                        </ul>
                    </div>

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            Observaciones
                        </label>

                        <textarea
                            v-model="form.notes"
                            rows="4"
                            maxlength="1000"
                            placeholder="Notas del traslado..."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2"
                        />

                    </div>

                    <!-- Botón -->
                    <div class="flex justify-end">

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-8 py-3 font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50"
                        >
                            Crear traslado
                        </button>

                    </div>

                    <!-- Errores -->
                    <div
                        v-if="Object.keys(form.errors).length"
                        class="rounded-lg border border-red-200 bg-red-50 p-4"
                    >
                        <p
                            v-for="(error,key) in form.errors"
                            :key="key"
                            class="text-sm text-red-600"
                        >
                            {{ error }}
                        </p>
                    </div>

                </form>

            </div>

            <!-- TABLA -->

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b bg-slate-50 px-6 py-4">

                    <h2 class="text-lg font-semibold">
                        Historial de traslados
                    </h2>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-slate-100">

                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    Código
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    Origen
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    Destino
                                </th>

                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    Estado
                                </th>

                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    Acción
                                </th>
                            </tr>

                        </thead>

                        <tbody>

                            <tr
                                v-for="transfer in transfers.data"
                                :key="transfer.id"
                                class="border-t transition hover:bg-slate-50"
                            >

                                <td class="px-6 py-4 font-medium">
                                    {{ transfer.code }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ transfer.from_warehouse.name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ transfer.to_warehouse.name }}
                                </td>

                                <td class="px-6 py-4">

                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="{
                                            'bg-yellow-100 text-yellow-700': transfer.status=='pending',
                                            'bg-green-100 text-green-700': transfer.status=='completed',
                                            'bg-blue-100 text-blue-700': transfer.status=='approved',
                                            'bg-red-100 text-red-700': transfer.status=='cancelled'
                                        }"
                                    >
                                        {{ transfer.status }}
                                    </span>

                                </td>

                                <td class="px-6 py-4 text-right">

                                    <Link
                                        :href="`/transfers/${transfer.id}`"
                                        class="rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100"
                                    >
                                        Ver detalle
                                    </Link>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>
    </AppLayout>
</template>