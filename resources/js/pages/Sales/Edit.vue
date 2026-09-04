<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

type PriceType =
    | 'price'
    | 'public'
    | 'wholesale'
    | 'price_roll'
    | 'special';

interface Product {
    id: number;
    code_product: string;
    name_product: string;

    price: number | string;
    public_price: number | string;
    wholesale_price: number | string;
    price_roll: number | string;
    special_price: number | string;

    kilos: number | string;
    metros: number | string;
}

interface SaleItem {
    id: number;
    store_id: number;
    unit: string;
    quantity: number | string;
    unit_price: number | string;
    line_total: number | string;

    store: {
        id: number;
        code_product: string;
        name_product: string;

        price: number | string;
        public_price: number | string;
        wholesale_price: number | string;
        price_roll: number | string;
        special_price: number | string;
    };
}

const props = defineProps<{
    sale: {
        id: number;
        code: string;
        customer_id: number;
        warehouse_id: number;
        status: string;
        subtotal: number | string;
        total: number | string;
        notes: string | null;

        customer: {
            id: number;
            name: string;
            dni: string;
        };

        warehouse: {
            id: number;
            name: string;
            code: string;
        };

        warehouseStocks: Array<{
            warehouse_id: number;
            store_id: number;
            kilos_available: number | string;
            metros_available: number | string;
        }>;

        items: SaleItem[];
    };

    customers: Array<{
        id: number;
        name: string;
        dni: string;
    }>;

    warehouses: Array<{
        id: number;
        name: string;
        code: string;
    }>;

    products: Product[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ventas',
        href: '/sales',
    },
    {
        title: `Editar ${props.sale.code}`,
        href: `/sales/${props.sale.id}/edit`,
    },
];

const productMap = computed(
    () => new Map(props.products.map((product) => [product.id, product])),
);

const warehouseStockMap = computed(() => {
    const map = new Map<
        string,
        {
            kilos_available: number;
            metros_available: number;
        }
    >();

    (props.sale.warehouseStocks ?? []).forEach((stock) => {
        map.set(`${stock.warehouse_id}:${stock.store_id}`, {
            kilos_available: Number(stock.kilos_available || 0),
            metros_available: Number(stock.metros_available || 0),
        });
    });

    return map;
});

const originalSaleStockMap = computed(() => {
    const map = new Map<string, number>();

    props.sale.items.forEach((item) => {
        const unit = item.unit === 'kilos' ? 'rollos' : item.unit;
        const key = `${item.store_id}:${unit}`;

        map.set(
            key,
            (map.get(key) || 0) + Number(item.quantity || 0),
        );
    });

    return map;
});

const getProductPriceOptions = (product?: Product) => {
    if (!product) {
        return [];
    }

    const options: Array<{
        label: string;
        value: PriceType;
        price: number;
    }> = [
        {
            label: 'Precio base',
            value: 'price',
            price: Number(product.price || 0),
        },
        {
            label: 'Precio público',
            value: 'public',
            price: Number(product.public_price || 0),
        },
        {
            label: 'Precio mayorista',
            value: 'wholesale',
            price: Number(product.wholesale_price || 0),
        },
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

const resolveItemPriceType = (
    item: SaleItem,
): PriceType => {
    const product = productMap.value.get(
        Number(item.store_id),
    );

    const unitPrice = Number(item.unit_price || 0);

    return (
        getProductPriceOptions(product).find(
            (option) =>
                Number(option.price.toFixed(2)) ===
                Number(unitPrice.toFixed(2)),
        )?.value ?? 'public'
    );
};

const form = useForm({
    customer_id: String(props.sale.customer_id),

    warehouse_id: String(props.sale.warehouse_id),

    notes: props.sale.notes ?? '',

    items: props.sale.items.map((item) => ({
        store_id: item.store_id,

        unit:
            item.unit === 'kilos'
                ? 'rollos'
                : item.unit,

        quantity: Number(item.quantity),

        price_type:
            resolveItemPriceType(item),
    })),
});

const availableProducts = computed(() => {
    return props.products;
});

const getProduct = (storeId: number) => {
    return productMap.value.get(Number(storeId));
};

const getUnitLabel = (unit: string) => {
    return unit === 'kilos'
        ? 'rollos'
        : unit;
};

const getAvailableForItem = (item: {
    store_id: number;
    unit: string;
}) => {
    const stock = warehouseStockMap.value.get(
        `${Number(form.warehouse_id)}:${Number(item.store_id)}`,
    );

    const stockQuantity =
        item.unit === 'metros'
            ? Number(stock?.metros_available || 0)
            : Number(stock?.kilos_available || 0);

    const originalKey =
        `${Number(item.store_id)}:${
            item.unit === 'metros'
                ? 'metros'
                : 'rollos'
        }`;

    const originalQuantity =
        originalSaleStockMap.value.get(
            originalKey,
        ) || 0;

    return stockQuantity + originalQuantity;
};

const normalizeItemAfterProductChange = (item: {
    store_id: number;
    unit: string;
    quantity: number;
    price_type: PriceType;
}) => {
    const product = getProduct(item.store_id);

    const priceOptions =
        getProductPriceOptions(product);

    if (
        !priceOptions.some(
            (option) =>
                option.value === item.price_type,
        )
    ) {
        item.price_type =
            priceOptions[0]?.value ?? 'public';
    }

    normalizeQuantity(item);
};

const normalizeQuantity = (item: {
    store_id: number;
    unit: string;
    quantity: number;
}) => {
    let quantity = Number(item.quantity);

    if (!Number.isFinite(quantity)) {
        quantity = 0;
    }

    if (quantity < 0) {
        quantity = 0;
    }

    const available =
        getAvailableForItem(item);

    if (quantity > available) {
        quantity = available;
    }

    item.quantity = quantity;
};

const hasInsufficientStock = (item: {
    store_id: number;
    unit: string;
    quantity: number;
}) => {
    const quantity = Number(item.quantity || 0);
    const available = getAvailableForItem(item);

    return quantity > available;
};

watch(
    () =>
        form.items
            .map(
                (item) =>
                    `${item.store_id}:${item.unit}`,
            )
            .join('|'),
    () => {
        form.items.forEach(
            normalizeItemAfterProductChange,
        );
    },
);

watch(
    () =>
        form.items.map(
            (item) => item.quantity,
        ),
    () => {
        form.items.forEach(
            normalizeQuantity,
        );
    },
    {
        deep: true,
    },
);

watch(
    () => form.warehouse_id,
    () => {
        form.items.forEach(
            normalizeQuantity,
        );
    },
);

const getSelectedProductPrice = (item: {
    store_id: number;
    price_type: PriceType;
}) => {
    const product = getProduct(item.store_id);

    if (!product) {
        return 0;
    }

    switch (item.price_type) {
        case 'wholesale':
            return Number(
                product.wholesale_price || 0,
            );

        case 'price_roll':
            return Number(
                product.price_roll || 0,
            );

        case 'special':
            return Number(
                product.special_price || 0,
            );

        case 'price':
            return Number(
                product.price || 0,
            );

        default:
            return Number(
                product.public_price || 0,
            );
    }
};

const estimateLineTotal = (item: {
    store_id: number;
    quantity: number;
    price_type: PriceType;
}) => {
    return (
        Number(item.quantity || 0) *
        getSelectedProductPrice(item)
    ).toFixed(2);
};

const addItem = () => {
    form.items.push({
        store_id: 0,
        unit: 'rollos',
        quantity: 0,
        price_type: 'public' as PriceType,
    });
};

const removeItem = (index: number) => {
    if (form.items.length <= 1) {
        return;
    }

    form.items.splice(index, 1);
};

const submit = () => {
    // Validación adicional antes de enviar
    for (
        let index = 0;
        index < form.items.length;
        index++
    ) {
        const item = form.items[index];

        if (Number(item.quantity) <= 0) {
            form.setError(
                `items.${index}.quantity`,
                'La cantidad debe ser mayor a 0.',
            );

            return;
        }

        if (hasInsufficientStock(item)) {
            form.setError(
                `items.${index}.quantity`,
                `Stock insuficiente. Disponible: ${getAvailableForItem(item)} ${getUnitLabel(item.unit)}.`,
            );

            return;
        }
    }

    form.transform((data: any) => ({
        ...data,

        customer_id:
            Number(data.customer_id),

        warehouse_id:
            Number(data.warehouse_id),

        items: data.items.map(
            (item: any) => ({
                store_id:
                    Number(item.store_id),

                unit:
                    item.unit === 'rollos'
                        ? 'kilos'
                        : item.unit,

                quantity:
                    Number(item.quantity),

                price_type:
                    item.price_type,
            }),
        ),
    })).put(
        `/sales/${props.sale.id}`,
    );
};
</script>
<template>
    <Head :title="`Editar venta ${props.sale.code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-1 flex-col gap-6 rounded-xl
                   bg-slate-50 p-4
                   dark:bg-slate-950"
        >
        <div>
            <h1
                class="text-2xl font-bold text-slate-800
                    dark:text-slate-100"
            >
                Crear Producto
            </h1>

            <p
                class="mt-1 text-sm text-slate-500
                    dark:text-slate-400"
            >
                Registra un nuevo producto y configura
                su stock, precios y disponibilidad.
            </p>

            <p
                class="mt-2 text-sm font-medium text-slate-600
                    dark:text-slate-300"
            >
                Fecha: {{ new Date().toLocaleDateString('es-PE') }}
            </p>
        </div>

            <!-- CABECERA -->
            <section
                class="rounded-xl
                       border border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div
                    class="flex items-center justify-between"
                >
                    <div>
                        <h1
                            class="text-2xl font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            Editar venta
                        </h1>

                        <p
                            class="mt-1 text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            {{ props.sale.code }}
                        </p>
                    </div>

                    <Link
                        href="/sales"
                        class="rounded-lg
                               bg-slate-200 px-4 py-2
                               text-sm font-medium
                               text-slate-700
                               transition
                               hover:bg-slate-300
                               dark:bg-slate-800
                               dark:text-slate-200
                               dark:hover:bg-slate-700"
                    >
                        Volver
                    </Link>
                </div>
            </section>

            <!-- CLIENTE Y ALMACÉN -->
            <section
                class="grid gap-4 rounded-xl
                       border border-slate-200
                       bg-white p-5 shadow-sm
                       md:grid-cols-2
                       dark:border-slate-700
                       dark:bg-slate-900"
            >

                <!-- CLIENTE -->
                <div>
                    <label
                        class="mb-1 block text-sm font-medium
                               text-slate-700
                               dark:text-slate-300"
                    >
                        Cliente
                    </label>

                    <select
                        v-model="form.customer_id"
                        class="w-full rounded-lg
                               border border-slate-300
                               bg-white px-3 py-2
                               text-slate-900
                               transition
                               focus:border-blue-500
                               focus:outline-none
                               focus:ring-2
                               focus:ring-blue-500/20
                               dark:border-slate-600
                               dark:bg-slate-800
                               dark:text-slate-100"
                    >
                        <option value="">
                            Seleccionar cliente
                        </option>

                        <option
                            v-for="customer in props.customers"
                            :key="customer.id"
                            :value="String(customer.id)"
                        >
                            {{ customer.name }} -
                            {{ customer.dni }}
                        </option>
                    </select>

                    <p
                        v-if="form.errors.customer_id"
                        class="mt-1 text-sm text-red-600
                               dark:text-red-400"
                    >
                        {{ form.errors.customer_id }}
                    </p>
                </div>

                <!-- ALMACÉN -->
                <div>
                    <label
                        class="mb-1 block text-sm font-medium
                               text-slate-700
                               dark:text-slate-300"
                    >
                        Almacén
                    </label>

                    <select
                        v-model="form.warehouse_id"
                        class="w-full rounded-lg
                               border border-slate-300
                               bg-white px-3 py-2
                               text-slate-900
                               transition
                               focus:border-blue-500
                               focus:outline-none
                               focus:ring-2
                               focus:ring-blue-500/20
                               dark:border-slate-600
                               dark:bg-slate-800
                               dark:text-slate-100"
                    >
                        <option value="">
                            Seleccionar almacén
                        </option>

                        <option
                            v-for="warehouse in props.warehouses"
                            :key="warehouse.id"
                            :value="String(warehouse.id)"
                        >
                            {{ warehouse.code }} -
                            {{ warehouse.name }}
                        </option>
                    </select>

                    <p
                        v-if="form.errors.warehouse_id"
                        class="mt-1 text-sm text-red-600
                               dark:text-red-400"
                    >
                        {{ form.errors.warehouse_id }}
                    </p>
                </div>
            </section>

            <!-- PRODUCTOS -->
            <section
                class="rounded-xl
                       border border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div
                    class="mb-4 flex items-center justify-between"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            Productos
                        </h2>

                        <p
                            class="text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            Modifica los productos o
                            cantidades de la venta.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="addItem"
                        class="rounded-lg
                               bg-green-600 px-4 py-2
                               text-sm font-medium
                               text-white
                               transition
                               hover:bg-green-700
                               dark:hover:bg-green-500"
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
                        class="rounded-lg
                               border border-slate-200
                               bg-slate-50 p-4
                               dark:border-slate-700
                               dark:bg-slate-800"
                    >

                        <div
                            class="grid gap-4
                                   md:grid-cols-5"
                        >

                            <!-- PRODUCTO -->
                            <div
                                class="md:col-span-2"
                            >
                                <label
                                    class="mb-1 block text-sm
                                           font-medium
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Producto
                                </label>

                                <select
                                    v-model.number="item.store_id"
                                    class="w-full rounded-lg
                                           border border-slate-300
                                           bg-white px-3 py-2
                                           text-slate-900
                                           transition
                                           focus:border-blue-500
                                           focus:outline-none
                                           focus:ring-2
                                           focus:ring-blue-500/20
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                >
                                    <option :value="0">
                                        Seleccionar producto
                                    </option>

                                    <option
                                        v-for="product in availableProducts"
                                        :key="product.id"
                                        :value="product.id"
                                    >
                                        {{ product.code_product }}
                                        -
                                        {{ product.name_product }}
                                    </option>
                                </select>
                            </div>

                            <!-- UNIDAD -->
                            <div>
                                <label
                                    class="mb-1 block text-sm
                                           font-medium
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Unidad
                                </label>

                                <select
                                    v-model="item.unit"
                                    class="w-full rounded-lg
                                           border border-slate-300
                                           bg-white px-3 py-2
                                           text-slate-900
                                           transition
                                           focus:border-blue-500
                                           focus:outline-none
                                           focus:ring-2
                                           focus:ring-blue-500/20
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                >
                                    <option value="metros">
                                        Metros
                                    </option>

                                    <option value="rollos">
                                        Rollos
                                    </option>
                                </select>
                            </div>

                            <!-- CANTIDAD -->
                            <div>
                                <label
                                    class="mb-1 block text-sm
                                           font-medium
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Cantidad
                                </label>

                                <input
                                    v-model.number="item.quantity"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    :max="
                                        getAvailableForItem(
                                            item,
                                        )
                                    "
                                    @input="
                                        normalizeQuantity(
                                            item,
                                        )
                                    "
                                    class="w-full rounded-lg
                                           border border-slate-300
                                           bg-white px-3 py-2
                                           text-slate-900
                                           transition
                                           placeholder:text-slate-400
                                           focus:border-blue-500
                                           focus:outline-none
                                           focus:ring-2
                                           focus:ring-blue-500/20
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100
                                           dark:placeholder:text-slate-500"
                                />

                                <p
                                    class="mt-1 text-xs
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    Disponible:
                                    {{
                                        getAvailableForItem(item)
                                    }}
                                    {{
                                        getUnitLabel(item.unit)
                                    }}
                                </p>

                                <p
                                    v-if="
                                        hasInsufficientStock(
                                            item,
                                        )
                                    "
                                    class="mt-1 text-sm
                                           font-medium
                                           text-red-600
                                           dark:text-red-400"
                                >
                                    Stock insuficiente.
                                    Disponible:
                                    {{
                                        getAvailableForItem(item)
                                    }}
                                    {{
                                        getUnitLabel(item.unit)
                                    }}.
                                </p>

                                <p
                                    v-if="
                                        form.errors.items?.[
                                            `${index}.quantity`
                                        ]
                                    "
                                    class="mt-1 text-sm
                                           text-red-600
                                           dark:text-red-400"
                                >
                                    {{
                                        form.errors.items[
                                            `${index}.quantity`
                                        ]
                                    }}
                                </p>
                            </div>

                            <!-- PRECIO -->
                            <div>
                                <label
                                    class="mb-1 block text-sm
                                           font-medium
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Tipo de precio
                                </label>

                                <select
                                    v-if="
                                        getProductPriceOptions(
                                            getProduct(
                                                item.store_id,
                                            ),
                                        ).length > 1
                                    "
                                    v-model="item.price_type"
                                    class="w-full rounded-lg
                                           border border-slate-300
                                           bg-white px-3 py-2
                                           text-slate-900
                                           transition
                                           focus:border-blue-500
                                           focus:outline-none
                                           focus:ring-2
                                           focus:ring-blue-500/20
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                >
                                    <option
                                        v-for="
                                            option in getProductPriceOptions(
                                                getProduct(
                                                    item.store_id,
                                                ),
                                            )
                                        "
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>

                                <div
                                    v-else
                                    class="flex h-10 items-center
                                           text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    Precio: S/
                                    {{
                                        getSelectedProductPrice(
                                            item,
                                        )
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- INFORMACIÓN -->
                        <div
                            v-if="
                                getProduct(
                                    item.store_id,
                                )
                            "
                            class="mt-4 rounded-lg
                                   bg-slate-50 p-3
                                   dark:bg-slate-800"
                        >
                            <div
                                class="grid gap-3 text-sm
                                       md:grid-cols-4"
                            >

                                <!-- PRODUCTO -->
                                <div>
                                    <span
                                        class="text-slate-500
                                               dark:text-slate-400"
                                    >
                                        Producto
                                    </span>

                                    <p
                                        class="font-medium
                                               text-slate-800
                                               dark:text-slate-100"
                                    >
                                        {{
                                            getProduct(
                                                item.store_id,
                                            )
                                                ?.name_product
                                        }}
                                    </p>
                                </div>

                                <!-- PRECIO UNITARIO -->
                                <div>
                                    <span
                                        class="text-slate-500
                                               dark:text-slate-400"
                                    >
                                        Precio unitario
                                    </span>

                                    <p
                                        class="font-medium
                                               text-slate-800
                                               dark:text-slate-100"
                                    >
                                        S/
                                        {{
                                            getSelectedProductPrice(
                                                item,
                                            ).toFixed(2)
                                        }}
                                    </p>
                                </div>

                                <!-- CANTIDAD -->
                                <div>
                                    <span
                                        class="text-slate-500
                                               dark:text-slate-400"
                                    >
                                        Cantidad
                                    </span>

                                    <p
                                        class="font-medium
                                               text-slate-800
                                               dark:text-slate-100"
                                    >
                                        {{ item.quantity }}
                                        {{
                                            getUnitLabel(
                                                item.unit,
                                            )
                                        }}
                                    </p>
                                </div>

                                <!-- TOTAL -->
                                <div>
                                    <span
                                        class="text-slate-500
                                               dark:text-slate-400"
                                    >
                                        Total
                                    </span>

                                    <p
                                        class="font-medium
                                               text-slate-800
                                               dark:text-slate-100"
                                    >
                                        S/
                                        {{
                                            estimateLineTotal(
                                                item,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ELIMINAR -->
                        <div
                            class="mt-4 flex justify-end"
                        >
                            <button
                                type="button"
                                @click="
                                    removeItem(index)
                                "
                                class="text-sm font-medium
                                       text-red-600
                                       hover:text-red-800
                                       dark:text-red-400
                                       dark:hover:text-red-300"
                            >
                                Eliminar producto
                            </button>
                        </div>
                    </div>
                </div>

                <p
                    v-if="form.errors.items"
                    class="mt-3 text-sm
                           text-red-600
                           dark:text-red-400"
                >
                    {{ form.errors.items }}
                </p>
            </section>

            <!-- OBSERVACIONES -->
            <section
                class="rounded-xl
                       border border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <label
                    class="mb-1 block text-sm
                           font-medium
                           text-slate-700
                           dark:text-slate-300"
                >
                    Motivo / Observaciones
                </label>

                <textarea
                    v-model="form.notes"
                    rows="3"
                    class="w-full rounded-lg
                           border border-slate-300
                           bg-white px-3 py-2
                           text-slate-900
                           placeholder:text-slate-400
                           transition
                           focus:border-blue-500
                           focus:outline-none
                           focus:ring-2
                           focus:ring-blue-500/20
                           dark:border-slate-600
                           dark:bg-slate-800
                           dark:text-slate-100
                           dark:placeholder:text-slate-500"
                    placeholder="Observaciones de la venta..."
                ></textarea>
            </section>

            <!-- BOTONES -->
            <section
                class="flex justify-end gap-3"
            >
                <Link
                    href="/sales/"
                    class="rounded-lg
                           bg-slate-200 px-5 py-2.5
                           text-sm font-medium
                           text-slate-700
                           transition
                           hover:bg-slate-300
                           dark:bg-slate-800
                           dark:text-slate-200
                           dark:hover:bg-slate-700"
                >
                    Cancelar
                </Link>

                <button
                    type="button"
                    @click="submit"
                    :disabled="form.processing"
                    class="rounded-lg
                           bg-blue-600 px-5 py-2.5
                           text-sm font-medium
                           text-white
                           transition
                           hover:bg-blue-700
                           disabled:opacity-50
                           dark:hover:bg-blue-500"
                >
                    {{
                        form.processing
                            ? 'Guardando...'
                            : 'Guardar cambios'
                    }}
                </button>
            </section>
        </div>
    </AppLayout>
</template>