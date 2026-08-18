<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

type PriceType =
    | 'price'
    | 'public'
    | 'wholesale'
    | 'price_roll'
    | 'special';

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

const page = usePage();

const roles = (page.props.auth?.roles ?? []) as string[];

const isAdmin = roles.includes('admin');
const isAlmacen = roles.includes('almacen');

const props = defineProps<{
    sales: {
        data: Array<{
            id: number;
            code: string;
            status: string;
            total: number;
            notes: string | null;

            customer: {
                name: string;
            };

            warehouse: {
                name: string;
                code: string;
            };

            seller: {
                name: string;
            };

            items: Array<{
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
                };
            }>;

            created_at: string;
        }>;
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

    defaultWarehouseId: number | null;

    warehouseStocks: Array<{
        warehouse_id: number;
        store_id: number;
        kilos_available: number | string;
        metros_available: number | string;
    }>;

    products: ProductOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Salidas',
        href: '/sales',
    },
];

type SaleItemForm = {
    store_id: string;
    unit: 'kilos' | 'metros';
    quantity: number;
    price_type: PriceType;
    search_text: string;
};

const defaultWarehouseId = props.defaultWarehouseId
    ? String(props.defaultWarehouseId)
    : props.warehouses.length === 1
        ? String(props.warehouses[0].id)
        : '';

const form = useForm({
    customer_id: '',
    warehouse_id: defaultWarehouseId,
    notes: '',
    items: [
        {
            store_id: '',
            unit: 'metros',
            quantity: 1,
            price_type: 'public' as PriceType,
            search_text: '',
        },
    ] as SaleItemForm[],
});

const addItem = () => {
    form.items.push({
        store_id: '',
        unit: 'metros',
        quantity: 1,
        price_type: 'public',
        search_text: '',
    });
};

const removeItem = (index: number) => {
    if (form.items.length === 1) {
        return;
    }

    form.items.splice(index, 1);
};

const productMap = computed(
    () =>
        new Map(
            props.products.map(
                (product: ProductOption) => [
                    product.id,
                    product,
                ],
            ),
        ),
);

const warehouseStockMap = computed(() => {
    const map = new Map<
        string,
        {
            kilos_available: number;
            metros_available: number;
        }
    >();

    (props.warehouseStocks ?? []).forEach(
        (
            stock: {
                warehouse_id: number;
                store_id: number;
                kilos_available: number | string;
                metros_available: number | string;
            },
        ) => {
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
        },
    );

    return map;
});

watch(
    () => form.warehouse_id,
    (warehouseId) => {
        form.items.forEach(
            (item: SaleItemForm) => {
                if (!item.store_id) {
                    return;
                }

                if (!warehouseId) {
                    item.store_id = '';
                    item.search_text = '';
                    return;
                }

                const stock =
                    getStockForItem(item);

                const available =
                    item.unit === 'kilos'
                        ? stock?.kilos_available ?? 0
                        : stock?.metros_available ?? 0;

                if (!stock || available <= 0) {
                    item.store_id = '';
                    item.search_text = '';
                }
            },
        );
    },
);

const getProductPriceOptions = (
    product?: ProductOption,
) => {
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
            price: Number(
                product.price || 0,
            ),
        },
        {
            label: 'Precio público',
            value: 'public',
            price: Number(
                product.public_price || 0,
            ),
        },
        {
            label: 'Precio mayorista',
            value: 'wholesale',
            price: Number(
                product.wholesale_price || 0,
            ),
        },
    ];

    if (
        Number(product.price_roll || 0) > 0
    ) {
        options.push({
            label: 'Precio por rollo',
            value: 'price_roll',
            price: Number(
                product.price_roll || 0,
            ),
        });
    }

    if (
        Number(product.special_price || 0) > 0
    ) {
        options.push({
            label: 'Precio especial',
            value: 'special',
            price: Number(
                product.special_price || 0,
            ),
        });
    }

    return options.filter(
        (option) => option.price > 0,
    );
};

const getSelectedProductPrice = (
    item: SaleItemForm,
) => {
    const product =
        productMap.value.get(
            Number(item.store_id),
        );

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

const estimateLineTotal = (
    item: SaleItemForm,
) => {
    return (
        Number(item.quantity || 0) *
        getSelectedProductPrice(item)
    ).toFixed(2);
};

const getStockForItem = (
    item: SaleItemForm,
) => {
    const warehouseId = Number(
        form.warehouse_id,
    );

    const storeId = Number(
        item.store_id,
    );

    if (!storeId) {
        return null;
    }

    if (!warehouseId) {
        const product =
            props.products.find(
                (
                    entry: ProductOption,
                ) =>
                    entry.id === storeId,
            );

        return product
            ? {
                  kilos_available: Number(
                      product.kilos || 0,
                  ),
                  metros_available: Number(
                      product.metros || 0,
                  ),
              }
            : null;
    }

    const stockFromMap =
        warehouseStockMap.value.get(
            `${warehouseId}:${storeId}`,
        );

    if (stockFromMap) {
        return stockFromMap;
    }

    return null;
};

const getAvailableProductsForWarehouse = (
    item: SaleItemForm,
) => {
    if (!form.warehouse_id) {
        return props.products;
    }

    const warehouseId = Number(
        form.warehouse_id,
    );

    return props.products.filter(
        (product: ProductOption) => {
            const stock =
                warehouseStockMap.value.get(
                    `${warehouseId}:${product.id}`,
                );

            return stock
                ? stock.kilos_available > 0 ||
                      stock.metros_available > 0
                : false;
        },
    );
};

const getFilteredProducts = (
    item: SaleItemForm,
) => {
    const term = (
        item.search_text || ''
    )
        .trim()
        .toLowerCase();

    const sourceProducts =
        getAvailableProductsForWarehouse(
            item,
        );

    if (!term) {
        return sourceProducts;
    }

    return sourceProducts.filter(
        (product: ProductOption) => {
            const haystack =
                `${product.code_product} ${product.name_product}`.toLowerCase();

            return haystack.includes(term);
        },
    );
};

const getSelectedProduct = (
    item: SaleItemForm,
) => {
    if (!item.store_id) {
        return null;
    }

    return (
        props.products.find(
            (product: ProductOption) =>
                String(product.id) ===
                item.store_id,
        ) ?? null
    );
};

const selectProduct = (
    item: SaleItemForm,
    productId: number,
) => {
    const product =
        props.products.find(
            (entry: ProductOption) =>
                entry.id === productId,
        );

    if (!product) {
        return;
    }

    item.store_id = String(
        product.id,
    );

    item.search_text =
        `${product.code_product} - ${product.name_product}`;
};

const getAvailableForItem = (
    item: SaleItemForm,
) => {
    const stock =
        getStockForItem(item);

    if (!stock) {
        return 0;
    }

    return item.unit === 'kilos'
        ? stock.kilos_available
        : stock.metros_available;
};

const getStockMessage = (
    item: SaleItemForm,
) => {
    const stock =
        getStockForItem(item);

    if (!stock) {
        return 'Sin stock configurado para esta ubicación';
    }

    const unitLabel =
        item.unit === 'kilos'
            ? 'rollos'
            : 'metros';

    const available =
        item.unit === 'kilos'
            ? stock.kilos_available
            : stock.metros_available;

    return `Stock disponible: ${available} ${unitLabel}`;
};

const hasStockForItem = (
    item: SaleItemForm,
) => {
    const stock =
        getStockForItem(item);

    if (!stock) {
        return false;
    }

    const available =
        item.unit === 'kilos'
            ? stock.kilos_available
            : stock.metros_available;

    return available > 0;
};

const clearSaleQuantityInvalidMessage = (
    event: Event,
) => {
    const target =
        event.target as HTMLInputElement;

    target.setCustomValidity('');
};

const setSaleQuantityInvalidMessage = (
    event: Event,
) => {
    const target =
        event.target as HTMLInputElement;

    if (target.validity.rangeUnderflow) {
        target.setCustomValidity(
            'El valor no puede ser negativo',
        );
    } else if (
        target.validity.rangeOverflow
    ) {
        target.setCustomValidity(
            'No puedes solicitar más del stock disponible',
        );
    } else {
        target.setCustomValidity('');
    }
};

const clampSaleItemQuantity = (
    item: SaleItemForm,
    event: Event,
) => {
    clearSaleQuantityInvalidMessage(
        event,
    );

    const quantity = Number(
        item.quantity || 0,
    );

    if (quantity < 0) {
        item.quantity = 0;
        return;
    }

    const available =
        getAvailableForItem(item);

    if (quantity > available) {
        item.quantity = available;
    }
};

const validateItemStock = (
    item: SaleItemForm,
) => {
    if (!form.warehouse_id) {
        window.alert(
            'Selecciona un almacén o tienda antes de elegir productos.',
        );

        return false;
    }

    if (!item.store_id) {
        window.alert(
            'Selecciona un producto.',
        );

        return false;
    }

    const stock =
        getStockForItem(item);

    if (!stock) {
        window.alert(
            'El producto seleccionado no tiene stock configurado en la ubicación seleccionada.',
        );

        return false;
    }

    const available =
        item.unit === 'kilos'
            ? stock.kilos_available
            : stock.metros_available;

    const requested = Number(
        item.quantity || 0,
    );

    if (requested <= 0) {
        window.alert(
            'La cantidad debe ser mayor a 0.',
        );

        return false;
    }

    if (available <= 0) {
        window.alert(
            'El producto seleccionado no tiene stock disponible en esta ubicación.',
        );

        return false;
    }

    if (requested > available) {
        window.alert(
            `La cantidad solicitada supera el stock disponible (${available} ${
                item.unit === 'kilos'
                    ? 'rollos'
                    : 'metros'
            }).`,
        );

        return false;
    }

    return true;
};

const submit = () => {
    const hasValidStock =
        form.items.every(
            (item: SaleItemForm) =>
                validateItemStock(item),
        );

    if (!hasValidStock) {
        return;
    }

    form.transform((data: any) => ({
        ...data,

        customer_id:
            Number(data.customer_id),

        warehouse_id:
            Number(data.warehouse_id),

        items: data.items.map(
            (item: SaleItemForm) => ({
                store_id:
                    Number(item.store_id),

                unit: item.unit,

                quantity:
                    Number(item.quantity),

                price_type:
                    item.price_type,
            }),
        ),
    })).post('/sales', {
        preserveScroll: true,
        onSuccess: () =>
            form.reset(
                'customer_id',
                'warehouse_id',
                'notes',
                'items',
            ),
    });
};
</script>

<template>
    <Head title="Salidas" />

    <AppLayout
        :breadcrumbs="breadcrumbs"
    >
        <div
            class="flex h-full flex-1 flex-col gap-6
                   bg-slate-50 p-4
                   dark:bg-slate-950"
        >
            <h1
                class="text-2xl font-semibold
                       text-slate-900
                       dark:text-slate-100"
            >
                Salidas
            </h1>

            <!-- REGISTRAR SALIDA -->
            <section
                class="rounded-xl border
                       border-slate-200
                       bg-white p-5 shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <h2
                    class="mb-4 text-lg font-semibold
                           text-slate-900
                           dark:text-slate-100"
                >
                    Registrar salida
                </h2>

                <form
                    class="space-y-4"
                    @submit.prevent="submit"
                >
                    <!-- CLIENTE / ALMACÉN -->
                    <div
                        class="grid gap-3 md:grid-cols-2"
                    >
                        <!-- CLIENTE -->
                        <div>
                            <label
                                class="mb-1 block text-sm
                                       font-medium
                                       text-slate-700
                                       dark:text-slate-300"
                            >
                                Cliente
                            </label>

                            <select
                                v-model="
                                    form.customer_id
                                "
                                required
                                class="w-full rounded-lg
                                       border
                                       border-slate-300
                                       bg-white px-3 py-2
                                       text-slate-900
                                       focus:border-blue-500
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-blue-500/20
                                       dark:border-slate-600
                                       dark:bg-slate-800
                                       dark:text-slate-100"
                            >
                                <option
                                    disabled
                                    value=""
                                >
                                    Selecciona cliente
                                </option>

                                <option
                                    v-for="customer in props.customers"
                                    :key="customer.id"
                                    :value="
                                        String(
                                            customer.id,
                                        )
                                    "
                                >
                                    {{ customer.dni }}
                                    -
                                    {{ customer.name }}
                                </option>
                            </select>
                        </div>

                        <!-- ALMACÉN -->
                        <div>
                            <label
                                class="mb-1 block text-sm
                                       font-medium
                                       text-slate-700
                                       dark:text-slate-300"
                            >
                                Almacén / tienda
                            </label>

                            <select
                                v-model="
                                    form.warehouse_id
                                "
                                required
                                class="w-full rounded-lg
                                       border
                                       border-slate-300
                                       bg-white px-3 py-2
                                       text-slate-900
                                       focus:border-blue-500
                                       focus:outline-none
                                       focus:ring-2
                                       focus:ring-blue-500/20
                                       dark:border-slate-600
                                       dark:bg-slate-800
                                       dark:text-slate-100"
                            >
                                <option
                                    disabled
                                    value=""
                                >
                                    Selecciona almacén / tienda
                                </option>

                                <option
                                    v-for="warehouse in props.warehouses"
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
                    <div class="space-y-3">
                        <div
                            v-for="(
                                item, index
                            ) in form.items"
                            :key="index"
                            class="grid gap-3 rounded-xl
                                   border
                                   border-slate-200
                                   bg-slate-50 p-4
                                   dark:border-slate-700
                                   dark:bg-slate-800
                                   md:grid-cols-[2fr_1fr_1fr_1fr_auto]"
                        >
                            <!-- BUSCAR PRODUCTO -->
                            <div
                                class="space-y-2"
                            >
                                <label
                                    class="mb-1 block text-sm
                                           font-medium
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Producto
                                </label>

                                <input
                                    v-model="
                                        item.search_text
                                    "
                                    type="text"
                                    class="w-full rounded-lg
                                           border
                                           border-slate-300
                                           bg-white px-3 py-2
                                           text-slate-900
                                           placeholder:text-slate-400
                                           focus:border-blue-500
                                           focus:outline-none
                                           focus:ring-2
                                           focus:ring-blue-500/20
                                           dark:border-slate-600
                                           dark:bg-slate-900
                                           dark:text-slate-100
                                           dark:placeholder:text-slate-500"
                                    placeholder="Buscar por código o nombre"
                                />

                                <div
                                    class="max-h-40
                                           overflow-y-auto
                                           rounded-lg border
                                           border-slate-200
                                           bg-slate-50 p-2
                                           dark:border-slate-700
                                           dark:bg-slate-900"
                                >
                                    <p
                                        class="mb-2 text-xs
                                               font-medium
                                               uppercase
                                               text-slate-500
                                               dark:text-slate-400"
                                    >
                                        Resultados
                                    </p>

                                    <button
                                        v-for="product in getFilteredProducts(item)"
                                        :key="product.id"
                                        type="button"
                                        class="mb-1 flex w-full
                                               items-center
                                               justify-between
                                               rounded-lg px-2 py-2
                                               text-left text-sm
                                               text-slate-700
                                               transition
                                               hover:bg-slate-200
                                               dark:text-slate-200
                                               dark:hover:bg-slate-800"
                                        @click="
                                            selectProduct(
                                                item,
                                                product.id,
                                            )
                                        "
                                    >
                                        <span>
                                            {{
                                                product.code_product
                                            }}
                                            -
                                            {{
                                                product.name_product
                                            }}
                                        </span>

                                        <span
                                            class="text-xs
                                                   text-slate-500
                                                   dark:text-slate-400"
                                        >
                                            Seleccionar
                                        </span>
                                    </button>

                                    <p
                                        v-if="
                                            !getFilteredProducts(
                                                item,
                                            ).length
                                        "
                                        class="text-sm
                                               text-slate-500
                                               dark:text-slate-400"
                                    >
                                        No hay productos que
                                        coincidan con la búsqueda.
                                    </p>
                                </div>

                                <p
                                    v-if="
                                        getSelectedProduct(
                                            item,
                                        )
                                    "
                                    class="text-sm
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Producto seleccionado:
                                    {{
                                        getSelectedProduct(
                                            item,
                                        )
                                            ?.code_product
                                    }}
                                    -
                                    {{
                                        getSelectedProduct(
                                            item,
                                        )
                                            ?.name_product
                                    }}
                                </p>
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
                                           border
                                           border-slate-300
                                           bg-white px-3 py-2
                                           text-slate-900
                                           dark:border-slate-600
                                           dark:bg-slate-900
                                           dark:text-slate-100"
                                >
                                    <option
                                        value="metros"
                                    >
                                        Metros
                                    </option>

                                    <option
                                        value="kilos"
                                    >
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
                                    v-model.number="
                                        item.quantity
                                    "
                                    type="number"
                                    min="0.001"
                                    :max="
                                        getAvailableForItem(
                                            item,
                                        ) || undefined
                                    "
                                    step="0.001"
                                    placeholder="Cantidad"
                                    required
                                    class="w-full rounded-lg
                                           border
                                           border-slate-300
                                           bg-white px-3 py-2
                                           text-slate-900
                                           placeholder:text-slate-400
                                           focus:border-blue-500
                                           focus:outline-none
                                           focus:ring-2
                                           focus:ring-blue-500/20
                                           dark:border-slate-600
                                           dark:bg-slate-900
                                           dark:text-slate-100
                                           dark:placeholder:text-slate-500"
                                    @input="
                                        clampSaleItemQuantity(
                                            item,
                                            $event,
                                        )
                                    "
                                    @invalid="
                                        setSaleQuantityInvalidMessage
                                    "
                                />
                            </div>

                            <!-- PRECIO -->
                            <div>
                                <label
                                    class="mb-1 block text-sm
                                           font-medium
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Precio
                                </label>

                                <div
                                    v-if="
                                        getProductPriceOptions(
                                            productMap.get(
                                                Number(
                                                    item.store_id,
                                                ),
                                            ),
                                        ).length > 1
                                    "
                                    class="w-full"
                                >
                                    <select
                                        v-model="
                                            item.price_type
                                        "
                                        class="w-full rounded-lg
                                               border
                                               border-slate-300
                                               bg-white px-3 py-2
                                               text-slate-900
                                               dark:border-slate-600
                                               dark:bg-slate-900
                                               dark:text-slate-100"
                                    >
                                        <option
                                            v-for="option in getProductPriceOptions(
                                                productMap.get(
                                                    Number(
                                                        item.store_id,
                                                    ),
                                                ),
                                            )"
                                            :key="
                                                option.value
                                            "
                                            :value="
                                                option.value
                                            "
                                        >
                                            {{
                                                option.label
                                            }}
                                        </option>
                                    </select>
                                </div>

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

                            <!-- QUITAR -->
                            <div
                                class="flex items-end"
                            >
                                <button
                                    type="button"
                                    class="rounded-lg
                                           bg-red-100 px-3 py-2
                                           font-medium
                                           text-red-600
                                           transition
                                           hover:bg-red-200
                                           dark:bg-red-500/10
                                           dark:text-red-400
                                           dark:hover:bg-red-500/20"
                                    @click="
                                        removeItem(index)
                                    "
                                >
                                    Quitar
                                </button>
                            </div>

                            <!-- STOCK -->
                            <p
                                :class="
                                    hasStockForItem(item)
                                        ? 'text-slate-500 dark:text-slate-400'
                                        : 'text-red-600 dark:text-red-400'
                                "
                                class="text-sm md:col-span-5"
                            >
                                {{ getStockMessage(item) }}

                                <span
                                    class="font-medium
                                           text-slate-700
                                           dark:text-slate-200"
                                >
                                    · Estimado: S/
                                    {{
                                        estimateLineTotal(
                                            item,
                                        )
                                    }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- BOTONES -->
                    <div
                        class="flex flex-wrap gap-2"
                    >
                        <button
                            type="button"
                            class="rounded-lg
                                   bg-slate-700 px-4 py-2
                                   font-medium text-white
                                   transition
                                   hover:bg-slate-800
                                   dark:bg-slate-600
                                   dark:hover:bg-slate-500"
                            @click="addItem"
                        >
                            Agregar producto
                        </button>

                        <button
                            type="submit"
                            :disabled="
                                form.processing
                            "
                            class="rounded-lg
                                   bg-blue-600 px-4 py-2
                                   font-medium text-white
                                   transition
                                   hover:bg-blue-700
                                   disabled:cursor-not-allowed
                                   disabled:opacity-50
                                   dark:hover:bg-blue-500"
                        >
                            {{
                                form.processing
                                    ? 'Guardando...'
                                    : 'Guardar salida'
                            }}
                        </button>
                    </div>

                    <!-- MOTIVO -->
                    <div>
                        <label
                            class="mb-2 block text-sm
                                   font-medium
                                   text-slate-700
                                   dark:text-slate-300"
                        >
                            Ingresar motivo: REPOSICIÓN Y/O
                            SALIDA
                        </label>

                        <textarea
                            v-model="form.notes"
                            rows="3"
                            maxlength="1000"
                            placeholder="Notas de la salida (opcional)"
                            class="w-full rounded-lg
                                   border
                                   border-slate-300
                                   bg-white px-3 py-2
                                   text-slate-900
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
                    </div>

                    <!-- ERRORES -->
                    <div
                        v-if="
                            Object.keys(
                                form.errors,
                            ).length
                        "
                        class="space-y-1 rounded-lg
                               border border-red-200
                               bg-red-50 p-4
                               dark:border-red-500/30
                               dark:bg-red-500/10"
                    >
                        <p
                            v-if="
                                form.errors.customer_id
                            "
                            class="text-sm
                                   text-red-600
                                   dark:text-red-400"
                        >
                            {{
                                form.errors.customer_id
                            }}
                        </p>

                        <p
                            v-if="
                                form.errors.warehouse_id
                            "
                            class="text-sm
                                   text-red-600
                                   dark:text-red-400"
                        >
                            {{
                                form.errors.warehouse_id
                            }}
                        </p>

                        <p
                            v-if="form.errors.items"
                            class="text-sm
                                   text-red-600
                                   dark:text-red-400"
                        >
                            {{
                                form.errors.items
                            }}
                        </p>
                    </div>
                </form>
            </section>

            <!-- SALIDAS REGISTRADAS -->
            <section
                class="overflow-hidden rounded-xl
                       border border-slate-200
                       bg-white shadow-sm
                       dark:border-slate-700
                       dark:bg-slate-900"
            >
                <div
                    class="border-b
                           border-slate-200 px-5 py-4
                           dark:border-slate-700"
                >
                    <h2
                        class="text-lg font-semibold
                               text-slate-900
                               dark:text-slate-100"
                    >
                        Salidas registradas
                    </h2>
                </div>

                <div
                    class="overflow-x-auto"
                >
                    <table
                        class="min-w-full
                               divide-y
                               divide-slate-200
                               dark:divide-slate-700"
                    >
                        <thead
                            class="bg-slate-50
                                   dark:bg-slate-800"
                        >
                            <tr>
                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Código de salida
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Cliente
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Ubicación
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Responsable
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Motivo
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Código
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Nombre del producto
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Rollos o Metros
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Estado
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
                                           uppercase
                                           tracking-wider
                                           text-slate-600
                                           dark:text-slate-300"
                                >
                                    Total
                                </th>

                                <th
                                    class="px-4 py-3 text-left
                                           text-xs font-semibold
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
                                   divide-slate-100
                                   dark:divide-slate-700"
                        >
                            <template
                                v-for="sale in props.sales.data"
                                :key="sale.id"
                            >
                                <tr
                                    v-for="item in sale.items"
                                    :key="
                                        `${sale.id}-${item.id}`
                                    "
                                    class="transition
                                           hover:bg-slate-50
                                           dark:hover:bg-slate-800/70"
                                >
                                    <td
                                        class="px-4 py-3 text-sm
                                               font-medium
                                               text-slate-900
                                               dark:text-slate-100"
                                    >
                                        {{
                                            sale.code
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-sm
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{
                                            sale.customer
                                                .name
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-sm
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{
                                            sale.warehouse
                                                .code
                                        }}
                                        -
                                        {{
                                            sale.warehouse
                                                .name
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-sm
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{
                                            sale.seller
                                                .name
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-sm
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{
                                            sale.notes ||
                                            '-'
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-sm
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{
                                            item.store
                                                .code_product
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-sm
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{
                                            item.store
                                                .name_product
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-sm
                                               text-slate-700
                                               dark:text-slate-300"
                                    >
                                        {{
                                            item.quantity
                                        }}
                                        {{
                                            item.unit ===
                                            'kilos'
                                                ? 'rollos'
                                                : item.unit
                                        }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-sm"
                                    >
                                        <span
                                            :class="{
                                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400':
                                                    sale.status ===
                                                    'pending',

                                                'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400':
                                                    sale.status ===
                                                    'completed',

                                                'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400':
                                                    sale.status ===
                                                    'approved',

                                                'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400':
                                                    sale.status ===
                                                    'cancelled',
                                            }"
                                            class="inline-flex
                                                   rounded-full
                                                   px-2.5 py-1
                                                   text-xs
                                                   font-semibold"
                                        >
                                            {{
                                                sale.status
                                            }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-4 py-3 text-sm
                                               font-medium
                                               text-slate-900
                                               dark:text-slate-100"
                                    >
                                        S/
                                        {{
                                            Number(
                                                item.line_total,
                                            ).toFixed(
                                                2,
                                            )
                                        }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap
                                               px-4 py-3 text-sm"
                                    >
                                        <Link
                                            :href="
                                                `/sales/${sale.id}`
                                            "
                                            class="font-medium
                                                   text-blue-600
                                                   hover:text-blue-800
                                                   dark:text-blue-400
                                                   dark:hover:text-blue-300"
                                        >
                                            Ver detalle
                                        </Link>

                                        <Link
                                            :href="
                                                `/sales/${sale.id}/edit`
                                            "
                                            class="ml-2 font-medium
                                                   text-green-600
                                                   hover:text-green-800
                                                   dark:text-green-400
                                                   dark:hover:text-green-300"
                                        >
                                            Editar
                                        </Link>
                                    </td>
                                </tr>
                            </template>

                            <tr
                                v-if="
                                    props.sales.data
                                        .length === 0
                                "
                            >
                                <td
                                    colspan="11"
                                    class="px-4 py-8
                                           text-center text-sm
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    No hay salidas
                                    registradas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>