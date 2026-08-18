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
            from_warehouse: {
                name: string;
            };
            to_warehouse: {
                name: string;
            };
        }>;
    };

    sourceWarehouses: Array<{
        id: number;
        name: string;
        code: string;
    }>;

    destinationWarehouses: Array<{
        id: number;
        name: string;
        code: string;
    }>;

    defaultSourceWarehouseId: number | null;

    products: Array<{
        id: number;
        code_product: string;
        name_product: string;
    }>;

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

const defaultSourceWarehouseId = props.defaultSourceWarehouseId
    ? String(props.defaultSourceWarehouseId)
    : '';

const form = useForm({
    from_warehouse_id: defaultSourceWarehouseId,
    to_warehouse_id: '',
    notes: '',
    items: [
        {
            store_id: '',
            kilos: 0,
            metros: 0,
        },
    ] as TransferItemForm[],
});

const addItem = () => {
    form.items.push({
        store_id: '',
        kilos: 0,
        metros: 0,
    });
};

const removeItem = (index: number) => {
    if (form.items.length === 1) {
        return;
    }

    form.items.splice(index, 1);
};

const warehouseStockMap = computed(() => {
    const map = new Map<
        string,
        {
            kilos_available: number;
            metros_available: number;
        }
    >();

    (props.warehouseStocks ?? []).forEach((stock) => {
        map.set(
            `${stock.warehouse_id}:${stock.store_id}`,
            {
                kilos_available: Number(
                    stock.kilos_available || 0,
                ),
                metros_available: Number(
                    stock.metros_available || 0,
                ),
            },
        );
    });

    return map;
});

const availableProducts = computed(() => {
    if (!form.from_warehouse_id) {
        return props.products;
    }

    const warehouseId = Number(
        form.from_warehouse_id,
    );

    return props.products.filter((product) => {
        const stock = warehouseStockMap.value.get(
            `${warehouseId}:${product.id}`,
        );

        return stock
            ? stock.kilos_available > 0 ||
                  stock.metros_available > 0
            : false;
    });
});

const getStockForItem = (
    item: TransferItemForm,
) => {
    if (
        !form.from_warehouse_id ||
        !item.store_id
    ) {
        return null;
    }

    const warehouseId = Number(
        form.from_warehouse_id,
    );

    const storeId = Number(item.store_id);

    return (
        warehouseStockMap.value.get(
            `${warehouseId}:${storeId}`,
        ) ?? null
    );
};

const getProductLabel = (
    productId: string,
) => {
    const product = props.products.find(
        (product) =>
            String(product.id) === productId,
    );

    return product
        ? `${product.code_product} - ${product.name_product}`
        : 'Producto';
};

const stockErrors = computed(() => {
    return form.items.flatMap((item) => {
        const errors: string[] = [];

        const stock =
            getStockForItem(item);

        if (!stock || !item.store_id) {
            return errors;
        }

        const kilos = Number(
            item.kilos || 0,
        );

        const metros = Number(
            item.metros || 0,
        );

        if (
            kilos > stock.kilos_available
        ) {
            errors.push(
                `${getProductLabel(item.store_id)}: Rollos no puede ser mayor a ${formatNumber(
                    stock.kilos_available,
                    3,
                )}`,
            );
        }

        if (
            metros > stock.metros_available
        ) {
            errors.push(
                `${getProductLabel(item.store_id)}: Metros no puede ser mayor a ${formatNumber(
                    stock.metros_available,
                    3,
                )}`,
            );
        }

        return errors;
    });
});

const hasStockError = computed(
    () => stockErrors.value.length > 0,
);

const setNegativeOrOverflowMessage = (
    event: Event,
) => {
    const target =
        event.target as HTMLInputElement;

    if (
        target.validity.rangeUnderflow
    ) {
        target.setCustomValidity(
            'El valor no puede ser negativo',
        );
    } else if (
        target.validity.rangeOverflow
    ) {
        target.setCustomValidity(
            'El valor no puede ser mayor al stock disponible',
        );
    } else {
        target.setCustomValidity('');
    }
};

const clearInvalidMessage = (
    event: Event,
) => {
    const target =
        event.target as HTMLInputElement;

    target.setCustomValidity('');
};

const clampTransferField = (
    field: 'kilos' | 'metros',
    item: TransferItemForm,
    event: Event,
) => {
    clearInvalidMessage(event);

    if (Number(item[field]) < 0) {
        item[field] = 0;
        return;
    }

    const stock =
        getStockForItem(item);

    if (!stock) {
        return;
    }

    const maxValue =
        field === 'kilos'
            ? stock.kilos_available
            : stock.metros_available;

    if (Number(item[field]) > maxValue) {
        item[field] = maxValue;
    }
};

const formatNumber = (
    value: number | string,
    fractionDigits = 0,
) => {
    const numberValue = Number(value);

    if (Number.isNaN(numberValue)) {
        return String(value);
    }

    if (
        fractionDigits <= 0 &&
        Number.isInteger(numberValue)
    ) {
        return numberValue.toString();
    }

    return numberValue
        .toFixed(fractionDigits)
        .replace(/\.0+$/, '');
};

const submit = () => {
    if (hasStockError.value) {
        window.alert(
            stockErrors.value.join('\n'),
        );
        return;
    }

    form.transform((data) => ({
        ...data,
        from_warehouse_id: Number(
            data.from_warehouse_id,
        ),
        to_warehouse_id: Number(
            data.to_warehouse_id,
        ),
        items: data.items.map((item) => ({
            store_id: Number(
                item.store_id,
            ),
            kilos: Number(
                item.kilos || 0,
            ),
            metros: Number(
                item.metros || 0,
            ),
        })),
    })).post('/transfers', {
        preserveScroll: true,
        onSuccess: () =>
            form.reset(
                'from_warehouse_id',
                'to_warehouse_id',
                'notes',
                'items',
            ),
    });
};
</script>

<template>
    <Head title="Traslados" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="min-h-full bg-slate-100 p-6
                   dark:bg-slate-950"
        >
            <!-- ENCABEZADO -->
            <div class="mb-6">
                <h1
                    class="text-3xl font-bold
                           text-slate-800
                           dark:text-slate-100"
                >
                    Traslados entre almacenes
                </h1>

                <p
                    class="mt-1 text-sm text-slate-500
                           dark:text-slate-400"
                >
                    Administra los traslados de
                    productos entre almacenes.
                </p>
            </div>

            <!-- FORMULARIO -->
            <div
                class="mb-8 overflow-hidden rounded-2xl
                       border border-slate-200
                       bg-white shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div
                    class="border-b border-slate-200
                           bg-slate-50 px-6 py-4
                           dark:border-slate-700
                           dark:bg-slate-800"
                >
                    <h2
                        class="text-lg font-semibold
                               text-slate-800
                               dark:text-slate-100"
                    >
                        Nuevo traslado
                    </h2>

                    <p
                        class="text-sm text-slate-500
                               dark:text-slate-400"
                    >
                        Complete la información del
                        traslado.
                    </p>
                </div>

                <form
                    class="space-y-6 p-6"
                    @submit.prevent="submit"
                >
                    <!-- ALMACENES -->
                    <div
                        class="grid gap-4 md:grid-cols-2"
                    >
                        <!-- ORIGEN -->
                        <div>
                            <label
                                class="mb-2 block text-sm
                                       font-medium
                                       text-slate-700
                                       dark:text-slate-300"
                            >
                                Almacén origen
                            </label>

                            <select
                                v-model="
                                    form.from_warehouse_id
                                "
                                required
                                class="h-11 w-full
                                       rounded-lg
                                       border
                                       border-slate-300
                                       bg-white px-3
                                       text-slate-900
                                       transition
                                       focus:border-blue-500
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-blue-200
                                       dark:border-slate-600
                                       dark:bg-slate-800
                                       dark:text-slate-100
                                       dark:focus:ring-blue-500/20"
                            >
                                <option
                                    disabled
                                    value=""
                                >
                                    Seleccione...
                                </option>

                                <option
                                    v-for="warehouse in props.sourceWarehouses"
                                    :key="warehouse.id"
                                    :value="
                                        String(
                                            warehouse.id,
                                        )
                                    "
                                >
                                    {{ warehouse.code }}
                                    -
                                    {{ warehouse.name }}
                                </option>
                            </select>
                        </div>

                        <!-- DESTINO -->
                        <div>
                            <label
                                class="mb-2 block text-sm
                                       font-medium
                                       text-slate-700
                                       dark:text-slate-300"
                            >
                                Almacén destino
                            </label>

                            <select
                                v-model="
                                    form.to_warehouse_id
                                "
                                required
                                class="h-11 w-full
                                       rounded-lg
                                       border
                                       border-slate-300
                                       bg-white px-3
                                       text-slate-900
                                       transition
                                       focus:border-blue-500
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-blue-200
                                       dark:border-slate-600
                                       dark:bg-slate-800
                                       dark:text-slate-100
                                       dark:focus:ring-blue-500/20"
                            >
                                <option
                                    disabled
                                    value=""
                                >
                                    Seleccione...
                                </option>

                                <option
                                    v-for="warehouse in props.destinationWarehouses"
                                    :key="warehouse.id"
                                    :value="
                                        String(
                                            warehouse.id,
                                        )
                                    "
                                >
                                    {{ warehouse.code }}
                                    -
                                    {{ warehouse.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- PRODUCTOS -->
                    <div>
                        <div
                            class="mb-4 flex items-center
                                   justify-between"
                        >
                            <h3
                                class="font-semibold
                                       text-slate-700
                                       dark:text-slate-200"
                            >
                                Productos
                            </h3>

                            <button
                                type="button"
                                @click="addItem"
                                class="rounded-lg
                                       bg-slate-700 px-4 py-2
                                       text-sm font-medium
                                       text-white transition
                                       hover:bg-slate-800
                                       dark:bg-slate-600
                                       dark:hover:bg-slate-500"
                            >
                                + Agregar producto
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(
                                    item, index
                                ) in form.items"
                                :key="index"
                                class="grid gap-4 rounded-xl
                                       border
                                       border-slate-200
                                       bg-slate-50 p-4
                                       dark:border-slate-700
                                       dark:bg-slate-800
                                       md:grid-cols-[2fr_1fr_1fr_auto]"
                            >
                                <!-- PRODUCTO -->
                                <div>
                                    <label
                                        class="mb-2 block text-sm
                                               font-medium
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        Producto
                                    </label>

                                    <select
                                        v-model="
                                            item.store_id
                                        "
                                        required
                                        class="h-11 w-full
                                               rounded-lg
                                               border
                                               border-slate-300
                                               bg-white px-3
                                               text-slate-900
                                               dark:border-slate-600
                                               dark:bg-slate-900
                                               dark:text-slate-100"
                                    >
                                        <option
                                            disabled
                                            value=""
                                        >
                                            Seleccione...
                                        </option>

                                        <option
                                            v-for="product in availableProducts"
                                            :key="product.id"
                                            :value="
                                                String(
                                                    product.id,
                                                )
                                            "
                                        >
                                            {{
                                                product.code_product
                                            }}
                                            -
                                            {{
                                                product.name_product
                                            }}
                                        </option>
                                    </select>

                                    <p
                                        v-if="
                                            getStockForItem(
                                                item,
                                            )
                                        "
                                        class="mt-2 text-sm
                                               text-slate-500
                                               dark:text-slate-400"
                                    >
                                        Stock origen:
                                        {{
                                            formatNumber(
                                                getStockForItem(
                                                    item,
                                                )
                                                    ?.kilos_available ??
                                                    0,
                                                3,
                                            )
                                        }}
                                        rollos ·
                                        {{
                                            formatNumber(
                                                getStockForItem(
                                                    item,
                                                )
                                                    ?.metros_available ??
                                                    0,
                                                3,
                                            )
                                        }}
                                        metros
                                    </p>
                                </div>

                                <!-- ROLLOS -->
                                <div>
                                    <label
                                        class="mb-2 block text-sm
                                               font-medium
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        Rollos
                                    </label>

                                    <input
                                        v-model.number="
                                            item.kilos
                                        "
                                        type="number"
                                        min="0"
                                        :max="
                                            getStockForItem(
                                                item,
                                            )
                                                ?.kilos_available ??
                                            undefined
                                        "
                                        step="0.001"
                                        class="h-11 w-full
                                               rounded-lg
                                               border
                                               border-slate-300
                                               bg-white px-3
                                               text-slate-900
                                               dark:border-slate-600
                                               dark:bg-slate-900
                                               dark:text-slate-100"
                                        @input="
                                            clampTransferField(
                                                'kilos',
                                                item,
                                                $event,
                                            )
                                        "
                                        @invalid="
                                            setNegativeOrOverflowMessage
                                        "
                                    />

                                    <p
                                        v-if="
                                            Number(
                                                item.kilos,
                                            ) >
                                            Number(
                                                getStockForItem(
                                                    item,
                                                )
                                                    ?.kilos_available ??
                                                0,
                                            )
                                        "
                                        class="mt-1 text-xs
                                               text-red-600
                                               dark:text-red-400"
                                    >
                                        Stock insuficiente.
                                    </p>
                                </div>

                                <!-- METROS -->
                                <div>
                                    <label
                                        class="mb-2 block text-sm
                                               font-medium
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        Metros
                                    </label>

                                    <input
                                        v-model.number="
                                            item.metros
                                        "
                                        type="number"
                                        min="0"
                                        :max="
                                            getStockForItem(
                                                item,
                                            )
                                                ?.metros_available ??
                                            undefined
                                        "
                                        step="0.001"
                                        class="h-11 w-full
                                               rounded-lg
                                               border
                                               border-slate-300
                                               bg-white px-3
                                               text-slate-900
                                               dark:border-slate-600
                                               dark:bg-slate-900
                                               dark:text-slate-100"
                                        @input="
                                            clampTransferField(
                                                'metros',
                                                item,
                                                $event,
                                            )
                                        "
                                        @invalid="
                                            setNegativeOrOverflowMessage
                                        "
                                    />

                                    <p
                                        v-if="
                                            Number(
                                                item.metros,
                                            ) >
                                            Number(
                                                getStockForItem(
                                                    item,
                                                )
                                                    ?.metros_available ??
                                                0,
                                            )
                                        "
                                        class="mt-1 text-xs
                                               text-red-600
                                               dark:text-red-400"
                                    >
                                        Stock insuficiente.
                                    </p>
                                </div>

                                <!-- ELIMINAR -->
                                <div
                                    class="flex items-end"
                                >
                                    <button
                                        type="button"
                                        @click="
                                            removeItem(
                                                index,
                                            )
                                        "
                                        :disabled="
                                            form.items
                                                .length ===
                                            1
                                        "
                                        class="h-11 rounded-lg
                                               bg-red-100 px-4
                                               font-medium
                                               text-red-600
                                               transition
                                               hover:bg-red-200
                                               disabled:cursor-not-allowed
                                               disabled:opacity-50
                                               dark:bg-red-500/10
                                               dark:text-red-400
                                               dark:hover:bg-red-500/20"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ERRORES STOCK -->
                    <div
                        v-if="hasStockError"
                        class="rounded-lg border
                               border-red-200
                               bg-red-50 p-4 text-sm
                               text-red-700
                               dark:border-red-500/30
                               dark:bg-red-500/10
                               dark:text-red-400"
                    >
                        <p class="font-semibold">
                            No se puede crear el
                            traslado porque la
                            cantidad excede el stock
                            disponible:
                        </p>

                        <ul
                            class="mt-2 list-disc pl-5"
                        >
                            <li
                                v-for="error in stockErrors"
                                :key="error"
                            >
                                {{ error }}
                            </li>
                        </ul>
                    </div>

                    <!-- NOTAS -->
                    <div>
                        <label
                            class="mb-2 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Observaciones
                        </label>

                        <textarea
                            v-model="form.notes"
                            rows="4"
                            maxlength="1000"
                            placeholder="Notas del traslado..."
                            class="w-full rounded-lg
                                   border
                                   border-slate-300
                                   bg-white px-3 py-2
                                   text-slate-900
                                   placeholder:text-slate-400
                                   focus:border-blue-500
                                   focus:outline-none
                                   focus:ring-2
                                   focus:ring-blue-200
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-100
                                   dark:placeholder:text-slate-500
                                   dark:focus:ring-blue-500/20"
                        ></textarea>
                    </div>

                    <!-- BOTÓN -->
                    <div
                        class="flex justify-end"
                    >
                        <button
                            type="submit"
                            :disabled="
                                form.processing ||
                                hasStockError
                            "
                            class="rounded-lg
                                   bg-blue-600 px-8 py-3
                                   font-semibold
                                   text-white transition
                                   hover:bg-blue-700
                                   disabled:cursor-not-allowed
                                   disabled:opacity-50
                                   dark:hover:bg-blue-500"
                        >
                            {{
                                form.processing
                                    ? 'Creando...'
                                    : 'Crear traslado'
                            }}
                        </button>
                    </div>

                    <!-- ERRORES -->
                    <div
                        v-if="
                            Object.keys(
                                form.errors,
                            ).length
                        "
                        class="rounded-lg border
                               border-red-200
                               bg-red-50 p-4
                               dark:border-red-500/30
                               dark:bg-red-500/10"
                    >
                        <p
                            v-for="(
                                error, key
                            ) in form.errors"
                            :key="key"
                            class="text-sm
                                   text-red-600
                                   dark:text-red-400"
                        >
                            {{ error }}
                        </p>
                    </div>
                </form>
            </div>

            <!-- HISTORIAL -->
            <div
                class="overflow-hidden rounded-2xl
                       border border-slate-200
                       bg-white shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div
                    class="border-b
                           border-slate-200
                           bg-slate-50 px-6 py-4
                           dark:border-slate-700
                           dark:bg-slate-800"
                >
                    <h2
                        class="text-lg font-semibold
                               text-slate-800
                               dark:text-slate-100"
                    >
                        Historial de traslados
                    </h2>
                </div>

                <div
                    class="overflow-x-auto"
                >
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
                                    class="px-6 py-4
                                           text-left text-xs
                                           font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Código
                                </th>

                                <th
                                    class="px-6 py-4
                                           text-left text-xs
                                           font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Origen
                                </th>

                                <th
                                    class="px-6 py-4
                                           text-left text-xs
                                           font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Destino
                                </th>

                                <th
                                    class="px-6 py-4
                                           text-left text-xs
                                           font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Estado
                                </th>

                                <th
                                    class="px-6 py-4
                                           text-right text-xs
                                           font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y
                                   divide-slate-200
                                   dark:divide-slate-700"
                        >
                            <tr
                                v-for="transfer in transfers.data"
                                :key="transfer.id"
                                class="transition
                                       hover:bg-slate-50
                                       dark:hover:bg-slate-800/70"
                            >
                                <td
                                    class="px-6 py-4
                                           font-medium
                                           text-slate-900
                                           dark:text-slate-100"
                                >
                                    {{ transfer.code }}
                                </td>

                                <td
                                    class="px-6 py-4
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{
                                        transfer
                                            .from_warehouse
                                            .name
                                    }}
                                </td>

                                <td
                                    class="px-6 py-4
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    {{
                                        transfer
                                            .to_warehouse
                                            .name
                                    }}
                                </td>

                                <td
                                    class="px-6 py-4"
                                >
                                    <span
                                        class="rounded-full
                                               px-3 py-1
                                               text-xs
                                               font-semibold"
                                        :class="{
                                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400':
                                                transfer.status ===
                                                'pending',

                                            'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400':
                                                transfer.status ===
                                                'completed',

                                            'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400':
                                                transfer.status ===
                                                'approved',

                                            'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400':
                                                transfer.status ===
                                                'cancelled',
                                        }"
                                    >
                                        {{
                                            transfer.status
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-6 py-4
                                           text-right"
                                >
                                    <Link
                                        :href="`/transfers/${transfer.id}`"
                                        class="inline-flex
                                               rounded-lg
                                               bg-blue-50 px-4 py-2
                                               text-sm font-medium
                                               text-blue-700
                                               transition
                                               hover:bg-blue-100
                                               dark:bg-blue-500/10
                                               dark:text-blue-400
                                               dark:hover:bg-blue-500/20"
                                    >
                                        Ver detalle
                                    </Link>
                                </td>
                            </tr>

                            <tr
                                v-if="
                                    transfers.data.length ===
                                    0
                                "
                            >
                                <td
                                    colspan="5"
                                    class="px-6 py-8
                                           text-center
                                           text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay traslados
                                    registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>