<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';

const props = defineProps<{
    suppliers: Array<{
        id: number;
        company_name: string;
    }>;
    warehouses: Array<{
        id: number;
        name: string;
        code: string;
    }>;
    warehouseSelectionRequired: boolean;
    defaultWarehouseId: number | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Almacen Crear',
        href: 'stores/create',
    },
];

const form = useForm({
    code_product: '',
    name_product: '',
    fabric_type: '',
    color: '',
    proveedor: '',
    kilos: '',
    metros: '',
    minimum_stock: 0,
    price: 0,
    public_price: 0,
    wholesale_price: 0,
    price_roll: 0,
    special_price: 0,
    location: '',
    description: '',
    warehouse_id: props.defaultWarehouseId ? String(props.defaultWarehouseId) : '',
    image_path: null as File | null,
    is_active: true,
});

const handleImage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.image_path = target.files[0];
    }
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

const submit = () => {
    form.post('/stores', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

```vue
<template>
    <Head title="Crear Producto" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">

            <!-- Encabezado -->
            <div class="mx-auto w-full max-w-6xl">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">
                            Crear Producto
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Registra un nuevo producto y configura su stock, precios y disponibilidad.
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

            <form
                @submit.prevent="submit"
                class="mx-auto w-full max-w-6xl space-y-6"
            >

                <!-- Información del producto -->
                <Card class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <CardHeader class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <CardTitle class="text-lg font-semibold text-slate-800">
                            Información del producto
                        </CardTitle>

                        <p class="mt-1 text-sm text-slate-500">
                            Datos principales del producto.
                        </p>
                    </CardHeader>

                    <CardContent class="p-6">

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
                                    type="text"
                                    placeholder="Ej. TEL-001"
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
                                    type="text"
                                    placeholder="Nombre del producto"
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
                                    type="text"
                                    placeholder="Ej. Algodón"
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
                                    type="text"
                                    placeholder="Ej. Azul"
                                    class="w-full"
                                />

                                <InputError
                                    :message="form.errors.color"
                                    class="mt-1"
                                />
                            </div>

                        </div>
                    </CardContent>
                </Card>


                <!-- Proveedor y Stock -->
                <Card class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <CardHeader class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <CardTitle class="text-lg font-semibold text-slate-800">
                            Proveedor y Stock
                        </CardTitle>

                        <p class="mt-1 text-sm text-slate-500">
                            Configura el proveedor y las cantidades iniciales del producto.
                        </p>
                    </CardHeader>

                    <CardContent class="space-y-6 p-6">

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
                                    v-for="supplier in suppliers"
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

                        <!-- Cantidades -->
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                            <div>
                                <Label
                                    for="kilos"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Rollos
                                </Label>

                                <Input
                                    id="kilos"
                                    v-model.number="form.kilos"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
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
                                    placeholder="0.00"
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
                                    placeholder="0"
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

                    </CardContent>
                </Card>


                <!-- Precios -->
                <Card class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <CardHeader class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <CardTitle class="text-lg font-semibold text-slate-800">
                            Precios
                        </CardTitle>

                        <p class="mt-1 text-sm text-slate-500">
                            Define los diferentes precios de venta del producto.
                        </p>
                    </CardHeader>

                    <CardContent class="p-6">

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">

                            <!-- Precio -->
                            <div>
                                <Label
                                    for="price"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Precio
                                </Label>

                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">
                                        $
                                    </span>

                                    <Input
                                        id="price"
                                        v-model.number="form.price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full pl-7"
                                        @input="clampNumberField('price', $event)"
                                        @invalid="setNegativeMessage"
                                    />
                                </div>

                                <InputError
                                    :message="form.errors.price"
                                    class="mt-1"
                                />
                            </div>

                            <!-- Precio Público -->
                            <div>
                                <Label
                                    for="public_price"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Precio Público
                                </Label>

                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">
                                        $
                                    </span>

                                    <Input
                                        id="public_price"
                                        v-model.number="form.public_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full pl-7"
                                        @input="clampNumberField('public_price', $event)"
                                        @invalid="setNegativeMessage"
                                    />
                                </div>

                                <InputError
                                    :message="form.errors.public_price"
                                    class="mt-1"
                                />
                            </div>

                            <!-- Precio Mayorista -->
                            <div>
                                <Label
                                    for="wholesale_price"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Precio Mayorista
                                </Label>

                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">
                                        $
                                    </span>

                                    <Input
                                        id="wholesale_price"
                                        v-model.number="form.wholesale_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full pl-7"
                                        @input="clampNumberField('wholesale_price', $event)"
                                        @invalid="setNegativeMessage"
                                    />
                                </div>

                                <InputError
                                    :message="form.errors.wholesale_price"
                                    class="mt-1"
                                />
                            </div>

                            <!-- Precio por rollo -->
                            <div>
                                <Label
                                    for="price_roll"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Precio por Rollo
                                </Label>

                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">
                                        $
                                    </span>

                                    <Input
                                        id="price_roll"
                                        v-model.number="form.price_roll"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full pl-7"
                                        @input="clampNumberField('price_roll', $event)"
                                        @invalid="setNegativeMessage"
                                    />
                                </div>

                                <InputError
                                    :message="form.errors.price_roll"
                                    class="mt-1"
                                />
                            </div>

                            <!-- Precio especial -->
                            <div class="sm:col-span-2 lg:col-span-4">
                                <Label
                                    for="special_price"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Precio Especial
                                </Label>

                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">
                                        $
                                    </span>

                                    <Input
                                        id="special_price"
                                        v-model.number="form.special_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full pl-7"
                                        @input="clampNumberField('special_price', $event)"
                                        @invalid="setNegativeMessage"
                                    />
                                </div>

                                <InputError
                                    :message="form.errors.special_price"
                                    class="mt-1"
                                />
                            </div>

                        </div>
                    </CardContent>
                </Card>


                <!-- Almacén y configuración -->
                <Card class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <CardHeader class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <CardTitle class="text-lg font-semibold text-slate-800">
                            Almacén y configuración
                        </CardTitle>

                        <p class="mt-1 text-sm text-slate-500">
                            Define dónde se registrará el stock inicial y el estado del producto.
                        </p>
                    </CardHeader>

                    <CardContent class="space-y-6 p-6">

                        <!-- Almacén -->
                        <div>
                            <Label
                                for="warehouse_id"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Almacén para stock inicial
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
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 transition hover:bg-slate-100"
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
                                for="image_path"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Foto del producto
                            </Label>

                            <input
                                id="image_path"
                                type="file"
                                accept="image/*"
                                @change="handleImage"
                                class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200"
                            />

                            <p class="mt-2 text-xs text-slate-400">
                                Selecciona una imagen para identificar visualmente el producto.
                            </p>

                            <InputError
                                :message="form.errors.image_path"
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

                    </CardContent>
                </Card>


                <!-- Botones -->
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
                        {{
                            form.processing
                                ? 'Creando producto...'
                                : 'Crear Producto'
                        }}
                    </Button>

                </div>

            </form>
        </div>
    </AppLayout>
</template>
```

