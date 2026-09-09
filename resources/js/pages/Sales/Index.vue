<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

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
    color: string | null;
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
                id: number;
                name: string;
                dni: string;
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
                    color: string;
                };
            }>;

            created_at: string;
        }>;
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            from: number | null;
            to: number | null;

            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
    };

    customers: Array<{
        id: number;
        name: string;
        dni: string;
    }>;

    // ✅ AQUÍ
    defaultCustomer: {
        id: number;
        name: string;
        dni: string;
    } | null;

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
    rollos: number;
    metros: number;
    price_type: PriceType;
    search_text: string;
};

const defaultWarehouseId = props.defaultWarehouseId
    ? String(props.defaultWarehouseId)
    : props.warehouses.length === 1
        ? String(props.warehouses[0].id)
        : '';

const form = useForm({
    customer_id: props.defaultCustomer
        ? String(props.defaultCustomer.id)
        : '',
    warehouse_id: defaultWarehouseId,
    notes: '',
    items: [
        {
            store_id: '',
            rollos: 0,
            metros: 0,
            price_type: 'public' as PriceType,
            search_text: '',
        },
    ] as SaleItemForm[],
});

const customerSearch = ref(
    props.defaultCustomer
        ? props.defaultCustomer.dni
        : '',
);

const isSearchingCustomer = ref(false);

const isQuickSale = ref(false);

const startQuickSale = () => {
    if (!props.defaultCustomer) {
        window.alert(
            'No existe el cliente CONSUMIDOR FINAL. Créalo antes de realizar una salida rápida.',
        );

        return;
    }

    isQuickSale.value = true;

    form.customer_id = String(
        props.defaultCustomer.id,
    );

    customerSearch.value = '';

    if (
        !form.warehouse_id &&
        props.warehouses.length === 1
    ) {
        form.warehouse_id = String(
            props.warehouses[0].id,
        );
    }
};

const cancelQuickSale = () => {
    isQuickSale.value = false;

    form.customer_id = props.defaultCustomer
        ? String(props.defaultCustomer.id)
        : '';

    customerSearch.value =
        props.defaultCustomer?.dni ?? '';
};


const selectedCustomer = computed(() => {
    return (
        props.customers.find(
            (customer) =>
                String(customer.id) ===
                String(form.customer_id),
        ) ?? null
    );
});

const filteredCustomers = computed(() => {
    const term = customerSearch.value
        .trim()
        .toLowerCase();

    if (!term) {
        return [];
    }

    return props.customers
        .filter((customer) =>
            customer.dni
                .toLowerCase()
                .includes(term),
        )
        .slice(0, 8);
});

const selectCustomer = (customer: {
    id: number;
    name: string;
    dni: string;
}) => {
    form.customer_id = String(customer.id);

    customerSearch.value = customer.dni;

    // Ocultar resultados después de seleccionar
    isSearchingCustomer.value = false;
};

const onCustomerSearch = () => {
    const term = customerSearch.value.trim();

    // Si el usuario empieza a escribir,
    // activar resultados de búsqueda
    isSearchingCustomer.value = term.length > 0;

    // Si cambia manualmente el DNI/nombre,
    // quitamos la selección anterior
    if (
        selectedCustomer.value &&
        customerSearch.value !== selectedCustomer.value.dni
    ) {
        form.customer_id = '';
    }
};

const resetToDefaultCustomer = () => {
    if (!props.defaultCustomer) {
        form.customer_id = '';
        customerSearch.value = '';
        return;
    }

    form.customer_id = String(
        props.defaultCustomer.id,
    );

    customerSearch.value =
        props.defaultCustomer.dni;
};



const addItem = () => {
    form.items.push({
        store_id: '',
        rollos: 0,
        metros: 0,
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

                const availableRollos = stock?.kilos_available ?? 0;
                const availableMetros = stock?.metros_available ?? 0;

                if (!stock || (availableRollos <= 0 && availableMetros <= 0)) {
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
    const product = productMap.value.get(
        Number(item.store_id),
    );

    if (!product) {
        return 0;
    }

    switch (item.price_type) {
        case 'price':
            return Number(product.price ?? 0);

        case 'public':
            return Number(
                product.public_price ?? 0,
            );

        case 'wholesale':
            return Number(
                product.wholesale_price ?? 0,
            );

        case 'price_roll':
            return Number(
                product.price_roll ?? 0,
            );

        case 'special':
            return Number(
                product.special_price ?? 0,
            );

        default:
            return Number(
                product.public_price ?? 0,
            );
    }
};

const estimateLineTotal = (
    item: SaleItemForm,
) => {
    const price = getSelectedProductPrice(item);
    const rollos = Number(item.rollos || 0);
    const metros = Number(item.metros || 0);

    const totalRollos = rollos * price;
    const totalMetros = metros * price;

    return (totalRollos + totalMetros).toFixed(2);
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
                `${product.code_product} ${product.name_product} ${product.color}`.toLowerCase();

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
    const product = props.products.find(
        (entry: ProductOption) =>
            entry.id === productId,
    );

    if (!product) {
        return;
    }

    const priceOptions =
        getProductPriceOptions(product);

    // Preferir siempre el precio público.
    // Si no existe, usar el primer precio disponible.
    const defaultPriceType =
        priceOptions.find(
            (option) => option.value === 'public',
        )?.value
        ?? priceOptions[0]?.value
        ?? 'public';

    // Actualizar TODA la información de la fila
    item.store_id = String(product.id);

    item.search_text =
        `${product.code_product} - ${product.name_product}`;

    item.price_type = defaultPriceType;

    item.rollos = 0;
    item.metros = 0;
};

const getAvailableForItem = (
    item: SaleItemForm,
) => {
    const stock = getStockForItem(item);

    if (!stock) {
        return { rollos: 0, metros: 0 };
    }

    return {
        rollos: Number(stock.kilos_available || 0),
        metros: Number(stock.metros_available || 0),
    };
};

const getStockMessage = (
    item: SaleItemForm,
) => {
    const stock = getStockForItem(item);

    if (!stock) {
        return 'Sin stock configurado para esta ubicación';
    }

    return `Stock disponible: ${formatNumber(stock.kilos_available)} rollos · ${formatNumber(stock.metros_available)} metros`;
};

const hasStockForItem = (
    item: SaleItemForm,
) => {
    const stock = getStockForItem(item);

    if (!stock) {
        return false;
    }

    return Number(stock.kilos_available || 0) > 0 ||
        Number(stock.metros_available || 0) > 0;
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

const clampSaleItemRollos = (
    item: SaleItemForm,
    event: Event,
) => {
    clearSaleQuantityInvalidMessage(event);

    let rollos = Number(item.rollos || 0);
    if (!Number.isFinite(rollos) || rollos < 0) {
        rollos = 0;
    }

    const stock = getStockForItem(item);
    const available = Number(stock?.kilos_available || 0);

    item.rollos = Math.min(Math.floor(rollos), Math.floor(available));
};

const clampSaleItemMetros = (
    item: SaleItemForm,
    event: Event,
) => {
    clearSaleQuantityInvalidMessage(event);

    let metros = Number(item.metros || 0);
    if (!Number.isFinite(metros) || metros < 0) {
        metros = 0;
    }

    const stock = getStockForItem(item);
    const available = Number(stock?.metros_available || 0);

    item.metros = Math.min(metros, available);
};

const validateItemStock = (
    item: SaleItemForm,
) => {
    if (!form.warehouse_id) {
        window.alert('Selecciona un almacén o tienda antes de elegir productos.');
        return false;
    }

    if (!item.store_id) {
        window.alert('Selecciona un producto.');
        return false;
    }

    const stock = getStockForItem(item);

    if (!stock) {
        window.alert('El producto seleccionado no tiene stock configurado en la ubicación seleccionada.');
        return false;
    }

    const rollos = Number(item.rollos || 0);
    const metros = Number(item.metros || 0);
    const availableRollos = Number(stock.kilos_available || 0);
    const availableMetros = Number(stock.metros_available || 0);

    if (rollos <= 0 && metros <= 0) {
        window.alert('Debes ingresar al menos una cantidad de rollos o metros.');
        return false;
    }

    if (rollos > availableRollos) {
        window.alert(`Los rollos solicitados superan el stock disponible (${availableRollos}).`);
        return false;
    }

    if (metros > availableMetros) {
        window.alert(`Los metros solicitados superan el stock disponible (${availableMetros}).`);
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

                rollos:
                    Number(item.rollos || 0),

                metros:
                    Number(item.metros || 0),

                price_type:
                    item.price_type,
            }),
        ),
    })).post('/sales', {
        preserveScroll: true,
        onSuccess: () => {
        form.reset(
            'customer_id',
            'warehouse_id',
            'notes',
            'items',
        );

        form.customer_id = props.defaultCustomer
            ? String(props.defaultCustomer.id)
            : '';

        customerSearch.value =
            props.defaultCustomer?.dni ?? '';
    },
    });
};

const expanded = ref<number | null>(null);

const toggleDetail = (id: number) => {
    expanded.value =
        expanded.value === id
            ? null
            : id;
};

const deleteSale = (saleId: number) => {
    if (
        window.confirm(
            '¿Estás seguro de que deseas eliminar esta salida?',
        )
    ) {
        router.delete(`/sales/${saleId}`, {
            preserveScroll: true,
        });
    }
};

const goToPage = (url: string | null) => {
    if (!url) return;

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    });
};

const formatDateTime = (date: string) => {
    if (!date) return '-';

    const d = new Date(date);

    return d.toLocaleString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
};

const formatNumber = (value: number | string) => {
    const number = Number(value ?? 0);

    return Number.isInteger(number)
        ? String(number)
        : number.toFixed(2).replace(/\.?0+$/, '');
};

const formatMoney = (value: number | string) => {
    return `S/ ${Number(value ?? 0).toFixed(2)}`;
};

</script>

<template>
    <Head title="Salidas" />

    <AppLayout :breadcrumbs="breadcrumbs">
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
            <p
                class="mt-2 text-sm font-medium text-slate-600
                    dark:text-slate-300"
            >
                Fecha: {{ new Date().toLocaleDateString('es-PE') }}
            </p>

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
                        <!-- CLIENTE NORMAL -->
                        <div
                            v-if="!isQuickSale"
                            class="relative"
                        >
                            <label
                                class="mb-1 block text-sm
                                       font-medium
                                       text-slate-700
                                       dark:text-slate-300"
                            >
                                Cliente
                            </label>

                            <!-- BUSCADOR -->
                            <input
                                v-model="customerSearch"
                                @input="onCustomerSearch"
                                type="text"
                                inputmode="numeric"
                                autocomplete="off"
                                placeholder="Buscar por DNI..."
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
                                @focus="
                                    customerSearch === props.defaultCustomer?.dni
                                        ? (customerSearch = '')
                                        : null
                                "
                            />

                            <!-- RESULTADOS -->
<div
    v-if="
        isSearchingCustomer &&
        filteredCustomers.length > 0
    "
    class="absolute left-0 right-0
           z-50 mt-1 max-h-56
           overflow-y-auto
           rounded-lg border border-slate-200
           bg-white p-1 shadow-lg
           dark:border-slate-700
           dark:bg-slate-800"
>
    <button
        v-for="customer in filteredCustomers"
        :key="customer.id"
        type="button"
        class="flex w-full
               items-center
               justify-between
               rounded-lg
               px-3 py-2
               text-left text-sm
               transition
               hover:bg-slate-100
               dark:hover:bg-slate-700"
        @click="selectCustomer(customer)"
    >
        <span>
            <span
                class="font-medium
                       text-slate-900
                       dark:text-slate-100"
            >
                {{ customer.dni }}
            </span>

            <span
                class="ml-2
                       text-slate-600
                       dark:text-slate-300"
            >
                {{ customer.name }}
            </span>
        </span>

        <span
            class="text-xs
                   text-blue-600
                   dark:text-blue-400"
        >
            Seleccionar
        </span>
    </button>
</div>

                            <!-- CLIENTE ACTUAL -->
                            <div
                                v-if="selectedCustomer"
                                class="mt-2 flex items-center
                                       justify-between
                                       rounded-lg
                                       border border-slate-200
                                       bg-slate-50 px-3 py-2
                                       dark:border-slate-700
                                       dark:bg-slate-800"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium
                                               text-slate-900
                                               dark:text-slate-100"
                                    >
                                        {{ selectedCustomer.name }}
                                    </p>

                                    <p
                                        class="text-xs
                                               text-slate-500
                                               dark:text-slate-400"
                                    >
                                        DNI:
                                        {{ selectedCustomer.dni }}
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    class="text-xs font-medium
                                           text-blue-600
                                           hover:text-blue-800
                                           dark:text-blue-400
                                           dark:hover:text-blue-300"
                                    @click="resetToDefaultCustomer"
                                >
                                    Consumidor final
                                </button>
                            </div>
                        </div>

                        <!-- CLIENTE SALIDA RÁPIDA -->
                        <div
                            v-if="isQuickSale"
                            class="rounded-lg border
                                   border-emerald-200
                                   bg-emerald-50 p-4
                                   dark:border-emerald-500/30
                                   dark:bg-emerald-500/10"
                        >
                            <p
                                class="text-xs font-medium uppercase
                                       text-emerald-700
                                       dark:text-emerald-400"
                            >
                                Cliente
                            </p>

                            <p
                                class="mt-1 text-sm font-semibold
                                       text-slate-900
                                       dark:text-slate-100"
                            >
                                {{
                                    props.defaultCustomer?.name
                                    ?? 'Consumidor final'
                                }}
                            </p>

                            <p
                                class="text-xs text-slate-500
                                       dark:text-slate-400"
                            >
                                DNI:
                                {{
                                    props.defaultCustomer?.dni
                                    ?? '00000000'
                                }}
                            </p>
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
                                v-model="form.warehouse_id"
                                required
                                class="w-full rounded-lg
                                       border border-slate-300
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
                                    :value="String(warehouse.id)"
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
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="grid gap-3
                                   rounded-xl
                                   border
                                   border-slate-200
                                   bg-slate-50 p-4
                                   dark:border-slate-700
                                   dark:bg-slate-800
                                   md:grid-cols-[minmax(320px,2fr)_minmax(110px,1fr)_minmax(110px,1fr)_minmax(210px,1.4fr)_auto]"
                        >
                            <!-- BUSCAR PRODUCTO -->
                            <div class="space-y-2">
                                <label
                                    class="mb-1 block text-sm
                                           font-medium
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Producto
                                </label>

                                <input
                                    v-model="item.search_text"
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
                                               rounded-lg
                                               px-2 py-2
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
                                        <div class="flex flex-col">
                                            <span
                                                class="font-medium text-slate-800
                                                    dark:text-slate-100"
                                            >
                                                {{ product.code_product }}
                                                -
                                                {{ product.name_product }}
                                            </span>

                                            <span
                                                v-if="product.color"
                                                class="text-xs text-slate-500
                                                    dark:text-slate-400"
                                            >
                                                Color: {{ product.color }}
                                            </span>
                                        </div>

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
                                    v-if="getSelectedProduct(item)"
                                    class="text-sm text-slate-600 dark:text-slate-300"
                                >
                                    Producto seleccionado:

                                    <span class="font-medium">
                                        {{ getSelectedProduct(item)?.code_product }}
                                        -
                                        {{ getSelectedProduct(item)?.name_product }}
                                    </span>

                                    <span
                                        v-if="getSelectedProduct(item)?.color"
                                        class="ml-2 font-medium text-blue-600
                                            dark:text-blue-400"
                                    >
                                        · {{ getSelectedProduct(item)?.color }}
                                    </span>
                                </p>
                            </div>

                            <!-- ROLLOS -->
                            <div>
                                <label
                                    class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Rollos
                                </label>

                                <input
                                    v-model.number="item.rollos"
                                    type="number"
                                    min="0"
                                    :max="getAvailableForItem(item).rollos || undefined"
                                    step="1"
                                    placeholder="Rollos"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                                    @input="clampSaleItemRollos(item, $event)"
                                    @invalid="setSaleQuantityInvalidMessage"
                                />
                            </div>

                            <!-- METROS -->
                            <div>
                                <label
                                    class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Metros
                                </label>

                                <input
                                    v-model.number="item.metros"
                                    type="number"
                                    min="0"
                                    :max="getAvailableForItem(item).metros || undefined"
                                    step="0.01"
                                    placeholder="Metros"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                                    @input="clampSaleItemMetros(item, $event)"
                                    @invalid="setSaleQuantityInvalidMessage"
                                />
                            </div>

                            <!-- PRECIO -->
                                <div class="w-full min-w-0">
                                    <label
                                        class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Precio
                                    </label>

                                    <select
                                        v-if="item.store_id"
                                        v-model="item.price_type"
                                        class="h-10 w-full min-w-0 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                                    >
                                        <option
                                            v-for="option in getProductPriceOptions(
                                                productMap.get(Number(item.store_id)),
                                            )"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }} — S/ {{ Number(option.price).toFixed(2) }}
                                        </option>
                                    </select>

                                    <div
                                        v-else
                                        class="flex h-10 w-full items-center rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-400 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-500"
                                    >
                                        Selecciona un producto
                                    </div>

                                    <p
                                        v-if="item.store_id"
                                        class="mt-1 truncate text-xs leading-4 text-slate-500 dark:text-slate-400"
                                    >
                                        Precio seleccionado:
                                        <span class="font-semibold text-slate-700 dark:text-slate-200">
                                            S/ {{ getSelectedProductPrice(item).toFixed(2) }}
                                        </span>
                                    </p>
                                </div>

                            <!-- QUITAR -->
                            <div class="flex items-end justify-start">
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
                                    @click="removeItem(index)"
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
                                class="text-sm md:col-span-6"
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
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="addItem"
                            class="rounded-lg
                                   bg-slate-700 px-4 py-2
                                   font-medium text-white
                                   hover:bg-slate-800
                                   dark:bg-slate-600"
                        >
                            Agregar producto
                        </button>

                        <button
                            v-if="!isQuickSale"
                            type="button"
                            @click="startQuickSale"
                            class="rounded-lg
                                   bg-emerald-600 px-4 py-2
                                   text-sm font-medium text-white
                                   hover:bg-emerald-700
                                   dark:hover:bg-emerald-500"
                        >
                            Salida rápida
                        </button>

                        <button
                            v-if="isQuickSale"
                            type="button"
                            @click="cancelQuickSale"
                            class="rounded-lg
                                   border border-slate-300
                                   bg-white px-4 py-2
                                   text-sm font-medium
                                   text-slate-700
                                   hover:bg-slate-50
                                   dark:border-slate-600
                                   dark:bg-slate-800
                                   dark:text-slate-300
                                   dark:hover:bg-slate-700"
                        >
                            Salida normal
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg
                                   bg-blue-600 px-4 py-2
                                   font-medium text-white
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
                            Ingresar motivo: REPOSICIÓN Y/O SALIDA
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
                            Object.keys(form.errors).length
                        "
                        class="space-y-1 rounded-lg
                               border border-red-200
                               bg-red-50 p-4
                               dark:border-red-500/30
                               dark:bg-red-500/10"
                    >
                        <p
                            v-if="form.errors.customer_id"
                            class="text-sm
                                   text-red-600
                                   dark:text-red-400"
                        >
                            {{ form.errors.customer_id }}
                        </p>

                        <p
                            v-if="form.errors.warehouse_id"
                            class="text-sm
                                   text-red-600
                                   dark:text-red-400"
                        >
                            {{ form.errors.warehouse_id }}
                        </p>

                        <p
                            v-if="form.errors.items"
                            class="text-sm
                                   text-red-600
                                   dark:text-red-400"
                        >
                            {{ form.errors.items }}
                        </p>
                    </div>
                </form>
            </section>

            <!-- SALIDAS REGISTRADAS -->
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div
    class="overflow-x-auto rounded-xl border
           border-slate-200
           dark:border-slate-700"
>
    <table
        class="min-w-full divide-y
               divide-slate-200
               dark:divide-slate-700"
    >

        <!-- =====================================================
             ENCABEZADO
        ====================================================== -->

        <thead
            class="bg-slate-50
                   dark:bg-slate-800"
        >
            <tr>

                <!-- FECHA -->

                <th
                    class="whitespace-nowrap px-4 py-3
                           text-left text-xs
                           font-semibold uppercase
                           tracking-wider
                           text-slate-600
                           dark:text-slate-300"
                >
                    Fecha / Hora
                </th>

                <!-- CÓDIGO -->

                <th
                    class="whitespace-nowrap px-4 py-3
                           text-left text-xs
                           font-semibold uppercase
                           tracking-wider
                           text-slate-600
                           dark:text-slate-300"
                >
                    Código salida
                </th>

                <!-- ALMACÉN -->

                <th
                    class="whitespace-nowrap px-4 py-3
                           text-left text-xs
                           font-semibold uppercase
                           tracking-wider
                           text-slate-600
                           dark:text-slate-300"
                >
                    Almacén
                </th>

                <!-- CLIENTE -->

                <th
                    class="whitespace-nowrap px-4 py-3
                           text-left text-xs
                           font-semibold uppercase
                           tracking-wider
                           text-slate-600
                           dark:text-slate-300"
                >
                    Cliente
                </th>

                <!-- RESPONSABLE -->

                <th
                    class="whitespace-nowrap px-4 py-3
                           text-left text-xs
                           font-semibold uppercase
                           tracking-wider
                           text-slate-600
                           dark:text-slate-300"
                >
                    Responsable
                </th>

                <!-- ITEMS -->

                <th
                    class="whitespace-nowrap px-4 py-3
                           text-center text-xs
                           font-semibold uppercase
                           tracking-wider
                           text-slate-600
                           dark:text-slate-300"
                >
                    Ítems
                </th>

                <!-- TOTAL -->

                <th
                    class="whitespace-nowrap px-4 py-3
                           text-right text-xs
                           font-semibold uppercase
                           tracking-wider
                           text-slate-600
                           dark:text-slate-300"
                >
                    Total
                </th>

                <!-- MOTIVO -->

                <th
                    class="whitespace-nowrap px-4 py-3
                           text-left text-xs
                           font-semibold uppercase
                           tracking-wider
                           text-slate-600
                           dark:text-slate-300"
                >
                    Motivo
                </th>

                <!-- ACCIONES -->

                <th
                    class="whitespace-nowrap px-4 py-3
                           text-center text-xs
                           font-semibold uppercase
                           tracking-wider
                           text-slate-600
                           dark:text-slate-300"
                >
                    Acciones
                </th>

            </tr>
        </thead>


        <!-- =====================================================
             CUERPO
        ====================================================== -->

        <tbody
            class="divide-y
                   divide-slate-200
                   dark:divide-slate-700"
        >

            <template
                v-for="sale in props.sales.data"
                :key="sale.id"
            >

                <!-- =================================================
                     FILA DE LA SALIDA
                ================================================== -->

                <tr
                    class="transition
                           hover:bg-slate-50
                           dark:hover:bg-slate-800/50"
                >

                    <!-- FECHA / HORA -->

                    <td
                        class="whitespace-nowrap
                               px-4 py-4 text-sm
                               text-slate-600
                               dark:text-slate-300"
                    >
                        {{ formatDateTime(sale.created_at) }}
                    </td>


                    <!-- CÓDIGO -->

                    <td
                        class="whitespace-nowrap
                               px-4 py-4"
                    >
                        <span
                            class="font-mono text-sm
                                   font-semibold
                                   text-blue-600
                                   dark:text-blue-400"
                        >
                            {{ sale.code }}
                        </span>
                    </td>


                    <!-- ALMACÉN -->

                    <td
                        class="px-4 py-4 text-sm
                               text-slate-700
                               dark:text-slate-300"
                    >
                        {{ sale.warehouse?.name ?? '-' }}
                    </td>


                    <!-- CLIENTE -->

                    <td
                        class="px-4 py-4 text-sm
                               text-slate-700
                               dark:text-slate-300"
                    >
                        {{
                            sale.customer?.name
                                ?? 'CONSUMIDOR FINAL'
                        }}
                    </td>


                    <!-- RESPONSABLE -->

                    <td
                        class="px-4 py-4 text-sm
                               text-slate-700
                               dark:text-slate-300"
                    >
                        {{ sale.seller?.name ?? '-' }}
                    </td>


                    <!-- ÍTEMS -->

                    <td
                        class="px-4 py-4 text-center"
                    >
                        <span
                            class="inline-flex items-center
                                   rounded-full
                                   bg-blue-100
                                   px-2.5 py-1
                                   text-xs font-semibold
                                   text-blue-700
                                   dark:bg-blue-900/30
                                   dark:text-blue-300"
                        >
                            {{ sale.items?.length ?? 0 }}

                            {{
                                (sale.items?.length ?? 0) === 1
                                    ? 'producto'
                                    : 'productos'
                            }}
                        </span>
                    </td>


                    <!-- TOTAL -->

                    <td
                        class="whitespace-nowrap
                               px-4 py-4
                               text-right text-sm
                               font-semibold
                               text-slate-800
                               dark:text-slate-100"
                    >
                        {{ formatMoney(sale.total) }}
                    </td>


                    <!-- MOTIVO -->

                    <td
                        class="px-4 py-4"
                    >
                        <span
                            class="inline-flex
                                   rounded-full
                                   bg-amber-100
                                   px-2.5 py-1
                                   text-xs font-semibold
                                   text-amber-700
                                   dark:bg-amber-900/30
                                   dark:text-amber-300"
                        >
                            {{ sale.notes ?? 'SALIDA' }}
                        </span>
                    </td>


                    <!-- =================================================
                         ACCIONES
                    ================================================== -->

                    <td
                        class="px-4 py-4"
                    >

                        <div
                            class="flex flex-wrap
                                   items-center
                                   justify-center
                                   gap-2"
                        >

                            <!-- EDITAR -->

                            <Link
                                :href="
                                    `/sales/${sale.id}/edit`
                                "
                                class="rounded-lg border
                                       border-amber-200
                                       bg-amber-50
                                       px-3 py-1.5
                                       text-xs
                                       font-semibold
                                       text-amber-700
                                       transition
                                       hover:bg-amber-100
                                       dark:border-amber-800
                                       dark:bg-amber-900/20
                                       dark:text-amber-300
                                       dark:hover:bg-amber-900/40"
                            >
                                Editar
                            </Link>


                            <!-- ELIMINAR -->

                            <button
                                v-if="isAdmin"
                                type="button"
                                @click="deleteSale(sale.id)"
                                class="rounded-lg border
                                       border-red-200
                                       bg-red-50
                                       px-3 py-1.5
                                       text-xs
                                       font-semibold
                                       text-red-700
                                       transition
                                       hover:bg-red-100
                                       dark:border-red-800
                                       dark:bg-red-900/20
                                       dark:text-red-300
                                       dark:hover:bg-red-900/40"
                            >
                                Eliminar
                            </button>

                            <!-- VER DETALLE -->

                            <button
                                type="button"
                                @click="
                                    toggleDetail(sale.id)
                                "
                                class="rounded-lg border
                                       border-blue-200
                                       bg-blue-50
                                       px-3 py-1.5
                                       text-xs
                                       font-semibold
                                       text-blue-700
                                       transition
                                       hover:bg-blue-100
                                       dark:border-blue-800
                                       dark:bg-blue-900/20
                                       dark:text-blue-300
                                       dark:hover:bg-blue-900/40"
                            >
                                {{
                                    expanded === sale.id
                                        ? 'Ocultar'
                                        : 'Ver detalle'
                                }}
                            </button>
                        </div>

                    </td>

                </tr>


                <!-- =================================================
                     DETALLE
                ================================================== -->

                <tr
                    v-if="expanded === sale.id"
                    class="bg-slate-50
                           dark:bg-slate-900/50"
                >

                    <td
                        colspan="9"
                        class="px-4 py-5"
                    >

                        <div
                            class="overflow-hidden
                                   rounded-xl border
                                   border-slate-200
                                   bg-white shadow-sm
                                   dark:border-slate-700
                                   dark:bg-slate-800"
                        >

                            <!-- CABECERA DEL DETALLE -->

                            <div
                                class="flex flex-wrap
                                       items-center
                                       justify-between
                                       gap-3
                                       border-b
                                       border-slate-200
                                       px-5 py-4
                                       dark:border-slate-700"
                            >

                                <div>

                                    <h3
                                        class="text-base
                                               font-semibold
                                               text-slate-800
                                               dark:text-slate-100"
                                    >
                                        Detalle de salida
                                    </h3>

                                    <p
                                        class="mt-1 text-xs
                                               text-slate-500
                                               dark:text-slate-400"
                                    >
                                        Código:

                                        <span
                                            class="font-semibold
                                                   text-slate-700
                                                   dark:text-slate-200"
                                        >
                                            {{ sale.code }}
                                        </span>
                                    </p>

                                </div>

                                <span
                                    class="text-xs
                                           text-slate-500
                                           dark:text-slate-400"
                                >
                                    {{
                                        sale.items?.length ?? 0
                                    }}
                                    {{
                                        (sale.items?.length ?? 0) === 1
                                            ? 'producto'
                                            : 'productos'
                                    }}
                                </span>

                            </div>


                            <!-- TABLA DE PRODUCTOS -->

                            <div class="overflow-x-auto">

                                <table
                                    class="min-w-full
                                           divide-y
                                           divide-slate-200
                                           dark:divide-slate-700"
                                >

                                    <thead
                                        class="bg-slate-50
                                               dark:bg-slate-900"
                                    >

                                        <tr>

                                            <th
                                                class="px-3 py-3
                                                       text-left
                                                       text-xs
                                                       font-semibold
                                                       uppercase
                                                       text-slate-600
                                                       dark:text-slate-300"
                                            >
                                                Código producto
                                            </th>

                                            <th
                                                class="px-3 py-3
                                                       text-left
                                                       text-xs
                                                       font-semibold
                                                       uppercase
                                                       text-slate-600
                                                       dark:text-slate-300"
                                            >
                                                Producto
                                            </th>

                                            <th
                                                class="px-3 py-3
                                                       text-right
                                                       text-xs
                                                       font-semibold
                                                       uppercase
                                                       text-slate-600
                                                       dark:text-slate-300"
                                            >
                                                Cantidad
                                            </th>

                                            <th
                                                class="px-3 py-3
                                                       text-left
                                                       text-xs
                                                       font-semibold
                                                       uppercase
                                                       text-slate-600
                                                       dark:text-slate-300"
                                            >
                                                Unidad
                                            </th>

                                            <th
                                                class="px-3 py-3
                                                       text-right
                                                       text-xs
                                                       font-semibold
                                                       uppercase
                                                       text-slate-600
                                                       dark:text-slate-300"
                                            >
                                                Precio unitario
                                            </th>

                                            <th
                                                class="px-3 py-3
                                                       text-right
                                                       text-xs
                                                       font-semibold
                                                       uppercase
                                                       text-slate-600
                                                       dark:text-slate-300"
                                            >
                                                Subtotal
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody
                                        class="divide-y
                                               divide-slate-200
                                               dark:divide-slate-700"
                                    >

                                        <tr
                                            v-for="
                                                item in sale.items
                                            "
                                            :key="item.id"
                                            class="hover:bg-slate-50
                                                   dark:hover:bg-slate-700/30"
                                        >

                                            <!-- CÓDIGO -->

                                            <td
                                                class="whitespace-nowrap
                                                       px-3 py-3
                                                       text-sm
                                                       font-medium
                                                       text-slate-700
                                                       dark:text-slate-200"
                                            >
                                                {{
                                                    item.store
                                                        ?.code_product
                                                        ?? '-'
                                                }}
                                            </td>


                                            <!-- PRODUCTO -->

                                            <td
                                                class="px-3 py-3
                                                       text-sm
                                                       text-slate-700
                                                       dark:text-slate-300"
                                            >
                                                {{
                                                    item.store
                                                        ?.name_product
                                                        ?? '-'
                                                }}
                                            </td>


                                            <!-- CANTIDAD -->

                                            <td
                                                class="px-3 py-3
                                                       text-right
                                                       text-sm
                                                       font-semibold
                                                       text-slate-700
                                                       dark:text-slate-200"
                                            >
                                                {{
                                                    formatNumber(
                                                        item.quantity
                                                    )
                                                }}
                                            </td>


                                            <!-- UNIDAD -->

                                            <td
                                                class="px-3 py-3
                                                       text-sm"
                                            >

                                                <span
                                                    class="inline-flex
                                                        rounded-full
                                                        bg-slate-100
                                                        px-2 py-1
                                                        text-xs
                                                        font-semibold
                                                        text-slate-700
                                                        dark:bg-slate-700
                                                        dark:text-slate-200"
                                                >
                                                    {{
                                                        String(item.unit).toLowerCase() === 'kilos'
                                                            ? 'ROLLOS'
                                                            : 'METROS'
                                                    }}
                                                </span>

                                            </td>


                                            <!-- PRECIO UNITARIO -->

                                            <td
                                                class="whitespace-nowrap
                                                       px-3 py-3
                                                       text-right
                                                       text-sm
                                                       text-slate-700
                                                       dark:text-slate-300"
                                            >
                                                {{
                                                    formatMoney(
                                                        item.unit_price
                                                    )
                                                }}
                                            </td>


                                            <!-- SUBTOTAL -->

                                            <td
                                                class="whitespace-nowrap
                                                       px-3 py-3
                                                       text-right
                                                       text-sm
                                                       font-semibold
                                                       text-slate-800
                                                       dark:text-slate-100"
                                            >
                                                {{
                                                    formatMoney(
                                                        item.line_total
                                                    )
                                                }}
                                            </td>

                                        </tr>


                                        <!-- SIN PRODUCTOS -->

                                        <tr
                                            v-if="
                                                !sale.items ||
                                                sale.items.length === 0
                                            "
                                        >

                                            <td
                                                colspan="6"
                                                class="px-4 py-8
                                                       text-center
                                                       text-sm
                                                       text-slate-500
                                                       dark:text-slate-400"
                                            >
                                                No hay productos
                                                registrados en esta
                                                salida.
                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </td>

                </tr>

            </template>


            <!-- =====================================================
                 SIN RESULTADOS
            ====================================================== -->

            <tr
                v-if="
                    !props.sales.data ||
                    props.sales.data.length === 0
                "
            >

                <td
                    colspan="9"
                    class="px-6 py-10 text-center"
                >

                    <p
                        class="text-sm font-medium
                               text-slate-600
                               dark:text-slate-300"
                    >
                        No se encontraron salidas.
                    </p>

                    <p
                        class="mt-1 text-xs
                               text-slate-400"
                    >
                        Prueba cambiando los filtros.
                    </p>

                </td>

            </tr>

        </tbody>

    </table>
</div>
<!-- PAGINACIÓN -->
<div
    v-if="props.sales.last_page > 1"
    class="flex flex-col gap-3 border-t
           border-slate-200 px-4 py-4
           dark:border-slate-700
           sm:flex-row sm:items-center
           sm:justify-between"
>
    <p class="text-sm text-slate-600 dark:text-slate-400">
        Mostrando
        <span class="font-semibold text-slate-800 dark:text-slate-200">
            {{ props.sales.from ?? 0 }}
        </span>
        -
        <span class="font-semibold text-slate-800 dark:text-slate-200">
            {{ props.sales.to ?? 0 }}
        </span>
        de
        <span class="font-semibold text-slate-800 dark:text-slate-200">
            {{ props.sales.total }}
        </span>
        salidas
    </p>

    <div class="flex flex-wrap items-center gap-1">
        <button
            v-for="link in props.sales.links"
            :key="link.label"
            type="button"
            :disabled="!link.url"
            @click="goToPage(link.url)"
            v-html="link.label"
            class="min-w-[40px] rounded-lg border
                   px-3 py-2 text-sm font-medium
                   transition
                   disabled:cursor-not-allowed
                   disabled:opacity-40"
            :class="
                link.active
                    ? 'border-blue-600 bg-blue-600 text-white'
                    : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'
            "
        ></button>
    </div>
</div>

            </section>
        </div>
    </AppLayout>
</template>