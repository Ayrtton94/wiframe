<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Almacen Editar',
        href: 'stores/edit',
    },
];

const props = defineProps<{
    product: {
        id: number;
        code_product: string;
        name_product: string;
        fabric_type: string;
        color: string;
        proveedor: string;
        kilos: number | string;
        metros: number | string;
        minimum_stock: number | string;
        price: number | string;
        public_price: number | string;
        wholesale_price: number | string;
        price_roll: number | string;
        special_price: number | string;
        location: string;
        description: string;
        is_active: boolean;
        image_path: string | null;

        warehouse_stocks: Array<{
            id: number;
            warehouse_id: number;
            store_id: number;
            kilos_available: number | string;
            metros_available: number | string;
            kilos_reserved: number | string;
            metros_reserved: number | string;
        }>;
    };

    warehouses: Array<{
        id: number;
        name: string;
        code: string;
    }>;

    warehouseSelectionRequired: boolean;

    defaultWarehouseId: number | null;

    suppliers: Array<{
        id: number;
        company_name: string;
    }>;
}>();

const initialWarehouseId = props.defaultWarehouseId;

const initialStock = props.product.warehouse_stocks.find(
    stock => Number(stock.warehouse_id) === Number(initialWarehouseId)
);

const form = useForm({
    code_product: props.product.code_product,
    name_product: props.product.name_product,
    fabric_type: props.product.fabric_type,
    color: props.product.color,
    proveedor: props.product.proveedor,

    kilos: props.product.kilos,
    metros: props.product.metros,

    minimum_stock: props.product.minimum_stock,
    price: props.product.price,
    public_price: props.product.public_price,
    wholesale_price: props.product.wholesale_price,
    price_roll: props.product.price_roll,
    special_price: props.product.special_price,

    location: props.product.location,
    description: props.product.description,

    warehouse_id: initialWarehouseId
        ? String(initialWarehouseId)
        : '',

    image_path: null as File | null,

    is_active: props.product.is_active,
});

const handleImage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files ? target.files[0] : null;
    form.image_path = file;
};

const setNegativeMessage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.validity.rangeUnderflow) {
        target.setCustomValidity('El valor no puede ser negativo');
    }
};

const clearInvalidMessage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    target.setCustomValidity('');
};

const clampNumberField = (field: 'kilos' | 'metros' | 'minimum_stock' | 'price' | 'public_price' | 'wholesale_price' | 'price_roll' | 'special_price', event: Event) => {
    clearInvalidMessage(event);
    if (Number(form[field]) < 0) {
        form[field] = 0 as any;
    }
};

const currentImageSrc = computed(() => {
    if (props.product.image_url) {
        return props.product.image_url;
    }

    if (props.product.image_path) {
        return `/storage/${props.product.image_path}`;
    }

    return null;
});
  

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/stores/${props.product.id}`, {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Almacen Editar" />
<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">

        <!-- Encabezado -->
        <div class="mx-auto w-full max-w-6xl">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h1 class="text-2xl font-bold text-slate-800">
                        Editar Producto
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Actualiza la información, precios y configuración del producto.
                    </p>
                </div>

                <Link
                    href="/stores"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    ← Volver al Inventario
                </Link>

            </div>
        </div>


        <!-- Formulario -->
        <form
            class="mx-auto w-full max-w-6xl space-y-6"
            @submit.prevent="submit"
        >

            <!-- Información del producto -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <h2 class="text-lg font-semibold text-slate-800">
                        Información del producto
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Datos principales del producto.
                    </p>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        <!-- Código -->
                        <div>
                            <Label
                                for="code_product"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Código de Producto
                            </Label>

                            <Input
                                id="code_product"
                                v-model="form.code_product"
                                class="w-full"
                            />

                            <InputError
                                :message="form.errors.code_product"
                                class="mt-1"
                            />
                        </div>


                        <!-- Nombre -->
                        <div>
                            <Label
                                for="name_product"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Nombre del Producto
                            </Label>

                            <Input
                                id="name_product"
                                v-model="form.name_product"
                                class="w-full"
                            />

                            <InputError
                                :message="form.errors.name_product"
                                class="mt-1"
                            />
                        </div>


                        <!-- Tipo de tela -->
                        <div>
                            <Label
                                for="fabric_type"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Tipo de Tela
                            </Label>

                            <Input
                                id="fabric_type"
                                v-model="form.fabric_type"
                                class="w-full"
                            />

                            <InputError
                                :message="form.errors.fabric_type"
                                class="mt-1"
                            />
                        </div>


                        <!-- Color -->
                        <div>
                            <Label
                                for="color"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Color
                            </Label>

                            <Input
                                id="color"
                                v-model="form.color"
                                class="w-full"
                            />

                            <InputError
                                :message="form.errors.color"
                                class="mt-1"
                            />
                        </div>

                    </div>
                </div>
            </div>


            <!-- Proveedor y Stock -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <h2 class="text-lg font-semibold text-slate-800">
                        Proveedor y Stock
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Configura el proveedor y las cantidades del producto.
                    </p>
                </div>

                <div class="space-y-6 p-6">

                    <!-- Proveedor -->
                    <div>
                        <Label
                            for="proveedor"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Proveedor
                        </Label>

                        <select
                            id="proveedor"
                            v-model="form.proveedor"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option
                                value=""
                                disabled
                            >
                                Selecciona un proveedor
                            </option>

                            <option
                                v-for="supplier in props.suppliers"
                                :key="supplier.id"
                                :value="supplier.company_name"
                            >
                                {{ supplier.company_name }}
                            </option>
                        </select>

                        <InputError
                            :message="form.errors.proveedor"
                            class="mt-1"
                        />
                    </div>


                    <!-- Stock -->
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">                       
                        <div>
                            <Label
                                for="kilos"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Rollo
                            </Label>

                            <Input
                                id="kilos"
                                v-model.number="form.kilos"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full"
                                @input="clampNumberField('kilos', $event)"
                                @invalid="setNegativeMessage"
                            />

                            <InputError
                                :message="form.errors.kilos"
                                class="mt-1"
                            />
                        </div>


                        <div>
                            <Label
                                for="metros"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Metros
                            </Label>

                            <Input
                                id="metros"
                                v-model.number="form.metros"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full"
                                @input="clampNumberField('metros', $event)"
                                @invalid="setNegativeMessage"
                            />

                            <InputError
                                :message="form.errors.metros"
                                class="mt-1"
                            />
                        </div>

                        <div>
                            <Label
                                for="minimum_stock"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Kilos
                            </Label>

                            <Input
                                id="minimum_stock"
                                v-model.number="form.minimum_stock"
                                type="number"
                                min="0"
                                class="w-full"
                                @input="clampNumberField('minimum_stock', $event)"
                                @invalid="setNegativeMessage"
                            />

                            <InputError
                                :message="form.errors.minimum_stock"
                                class="mt-1"
                            />
                        </div>

                    </div>
                </div>
            </div>


            <!-- Precios -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <h2 class="text-lg font-semibold text-slate-800">
                        Precios
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Actualiza los precios del producto.
                    </p>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">

                        <div>
                            <Label
                                for="price"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Precio
                            </Label>

                            <Input
                                id="price"
                                v-model.number="form.price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full"
                                @input="clampNumberField('price', $event)"
                                @invalid="setNegativeMessage"
                            />

                            <InputError
                                :message="form.errors.price"
                                class="mt-1"
                            />
                        </div>


                        <div>
                            <Label
                                for="public_price"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Precio Público
                            </Label>

                            <Input
                                id="public_price"
                                v-model.number="form.public_price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full"
                                @input="clampNumberField('public_price', $event)"
                                @invalid="setNegativeMessage"
                            />

                            <InputError
                                :message="form.errors.public_price"
                                class="mt-1"
                            />
                        </div>


                        <div>
                            <Label
                                for="wholesale_price"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Precio Mayorista
                            </Label>

                            <Input
                                id="wholesale_price"
                                v-model.number="form.wholesale_price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full"
                                @input="clampNumberField('wholesale_price', $event)"
                                @invalid="setNegativeMessage"
                            />

                            <InputError
                                :message="form.errors.wholesale_price"
                                class="mt-1"
                            />
                        </div>


                        <div>
                            <Label
                                for="price_roll"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Precio por Rollo
                            </Label>

                            <Input
                                id="price_roll"
                                v-model.number="form.price_roll"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full"
                                @input="clampNumberField('price_roll', $event)"
                                @invalid="setNegativeMessage"
                            />

                            <InputError
                                :message="form.errors.price_roll"
                                class="mt-1"
                            />
                        </div>


                        <div class="sm:col-span-2 lg:col-span-4">

                            <Label
                                for="special_price"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Precio Especial
                            </Label>

                            <Input
                                id="special_price"
                                v-model.number="form.special_price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full"
                                @input="clampNumberField('special_price', $event)"
                                @invalid="setNegativeMessage"
                            />

                            <InputError
                                :message="form.errors.special_price"
                                class="mt-1"
                            />

                        </div>

                    </div>
                </div>
            </div>


            <!-- Configuración -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <h2 class="text-lg font-semibold text-slate-800">
                        Configuración
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Configura la ubicación, estado, imagen y descripción.
                    </p>
                </div>

                <div class="space-y-6 p-6">

                    <!-- Almacén -->
                        <div>
                            <Label
                                for="warehouse_id"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Almacén y/o Tienda
                            </Label>

                            <select
                                v-if="props.warehouseSelectionRequired"
                                id="warehouse_id"
                                v-model="form.warehouse_id"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                            >
                                <option
                                    value=""
                                    disabled
                                >
                                    Selecciona un almacén
                                </option>

                                <option
                                    v-for="warehouse in props.warehouses"
                                    :key="warehouse.id"
                                    :value="String(warehouse.id)"
                                >
                                    {{ warehouse.name }} ({{ warehouse.code }})
                                </option>
                            </select>

                            <div
                                v-else
                                class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700"
                            >
                                Se registrará stock automáticamente en
                                <span class="font-semibold">
                                    {{ props.warehouses[0]?.name || 'el almacén asignado' }}
                                </span>.
                            </div>

                            <InputError
                                :message="form.errors.warehouse_id"
                                class="mt-1"
                            />
                        </div>


                    <!-- Estado -->
                    <label
                        for="is_active"
                        class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-4 transition hover:bg-slate-100"
                    >
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        />

                        <div>
                            <span class="block text-sm font-medium text-slate-700">
                                Producto activo
                            </span>

                            <span class="text-xs text-slate-500">
                                El producto estará disponible para las operaciones del sistema.
                            </span>
                        </div>
                    </label>


                    <!-- Imagen -->
                    <div>
                        <Label
                            for="image"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Imagen del producto
                        </Label>

                        <div
                            v-if="currentImageSrc"
                            class="mb-4 flex items-center gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4"
                        >
                            <img
                                :src="currentImageSrc"
                                alt="Imagen actual"
                                class="h-24 w-24 rounded-lg border border-slate-200 object-cover shadow-sm"
                            />

                            <div>
                                <p class="text-sm font-medium text-slate-700">
                                    Imagen actual
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Selecciona una nueva imagen si deseas reemplazarla.
                                </p>
                            </div>
                        </div>

                        <Input
                            id="image"
                            type="file"
                            accept="image/*"
                            @change="handleImage"
                            class="w-full"
                        />

                        <InputError
                            :message="form.errors.image"
                            class="mt-1"
                        />
                    </div>


                    <!-- Descripción -->
                    <div>
                        <Label
                            for="description"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Descripción
                        </Label>

                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            placeholder="Escribe una descripción del producto..."
                            class="w-full resize-y rounded-lg border border-slate-300 px-4 py-3 text-sm text-slate-700 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        ></textarea>

                        <InputError
                            :message="form.errors.description"
                            class="mt-1"
                        />
                    </div>

                </div>
            </div>


            <!-- Acciones -->
            <div class="flex flex-col-reverse gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">

                <Link
                    href="/stores"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Cancelar
                </Link>

                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ form.processing ? 'Actualizando...' : 'Actualizar Producto' }}
                </Button>

            </div>

        </form>
    </div>
</AppLayout>
    
</template>