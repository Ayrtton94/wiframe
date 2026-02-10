<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

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
        stock: number;
        proveedor: string;
        price: number;
        stock_minimum: number;
        wholesale_price: number;
        retail_price: number;
        price_roll: number | null;
        special_price: number | null;
        description: string | null;
    };
}>();

const form = useForm({
    code_product: props.product.code_product,
    name_product: props.product.name_product,
    fabric_type: props.product.fabric_type,
    color: props.product.color,
    stock: props.product.stock,
    proveedor: props.product.proveedor,
    price: props.product.price,
    stock_minimum: props.product.stock_minimum,
    wholesale_price: props.product.wholesale_price,
    retail_price: props.product.retail_price,
    price_roll: props.product.price_roll,
    special_price: props.product.special_price,
    description: props.product.description,
});

const submit = () => {
    form.put(`/stores/${props.product.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Almacen Editar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <Link href="/stores" class="mb-4 inline-block rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                    Volver al Inventario
                </Link>
            </div>

            <form @submit.prevent="submit">
                <table class="min-w-full divide-y divide-gray-200">
                <tbody class="bg-white divide-y divide-gray-200">

                    <!-- Fila 1 -->
                    <tr>
                        <td class="px-6 py-4 align-top text-sm font-medium text-black">
                            Código de Producto
                            <Input v-model="form.code_product" class="w-full" />
                            <InputError :message="form.errors.code_product" />
                        </td>

                        <td class="px-6 py-4 align-top text-sm font-medium text-gray-900">
                            Nombre del Producto
                            <Input v-model="form.name_product" class="w-full" />
                            <InputError :message="form.errors.name_product" />
                        </td>
                    </tr>

                    <!-- Fila 2 -->
                    <tr>
                        <td class="px-6 py-4 align-top text-sm font-medium text-black">
                            Tipo de Tela
                            <Input v-model="form.fabric_type" class="w-full" />
                            <InputError :message="form.errors.fabric_type" />
                        </td>

                        <td class="px-6 py-4 align-top text-sm font-medium text-black">
                            Color
                            <Input v-model="form.color" class="w-full" />
                            <InputError :message="form.errors.color" />
                        </td>
                    </tr>

                    <!-- Fila 3 -->
                    <tr>
                        <td class="px-6 py-4 align-top text-sm font-medium text-black">
                            Stock
                            <Input v-model="form.stock" type="number" class="w-full" />
                            <InputError :message="form.errors.stock" />
                        </td>

                        <td class="px-6 py-4 align-top text-sm font-medium text-black">
                            Proveedor
                            <Input v-model="form.proveedor" class="w-full" />
                            <InputError :message="form.errors.proveedor" />
                        </td>
                    </tr>

                    <!-- Fila 4 -->
                    <tr>
                        <td class="px-6 py-4 align-top text-sm font-medium text-black">
                            Precio
                            <Input v-model="form.price" type="number" step="0.01" class="w-full" />
                            <InputError :message="form.errors.price" />
                        </td>

                        <td class="px-6 py-4 align-top text-sm font-medium text-black">
                            Stock Mínimo
                            <Input v-model="form.stock_minimum" type="number" class="w-full" />
                            <InputError :message="form.errors.stock_minimum" />
                        </td>
                    </tr>

                    <!-- Fila PRECIOS (4 alineados) -->
                    <tr>
                        <td colspan="2" class="px-6 py-4">
                            <div class="grid grid-cols-4 gap-4">
                                <div>
                                    <label for="Precio Mayorista" class="text-black">Precio Mayorista</label>
                                    <Input v-model="form.wholesale_price" type="number" step="0.01" class="w-full" />
                                    <InputError :message="form.errors.wholesale_price" />
                                </div>

                                <div>
                                    <label for="Precio Minorista" class="text-black">Precio Minorista</label>
                                    <Input v-model="form.retail_price" type="number" step="0.01" class="w-full" />
                                    <InputError :message="form.errors.retail_price" />
                                </div>

                                <div>
                                    <label for="Precio por Rollo" class="text-black">Precio por Rollo</label>
                                    <Input v-model="form.price_roll" type="number" step="0.01" class="w-full" />
                                    <InputError :message="form.errors.price_roll" />
                                </div>

                                <div>
                                    <label for="Precio Especial" class="text-black">Precio Especial</label>
                                    <Input v-model="form.special_price" type="number" step="0.01" class="w-full" />
                                    <InputError :message="form.errors.special_price" />
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Descripción -->
                    <tr>
                        <td colspan="2" class="px-6 py-4 align-top text-sm font-medium text-black">
                            Descripción
                            <textarea
                                v-model="form.description"
                                class="w-full border border-gray-300 rounded px-3 py-2"
                            ></textarea>
                            <InputError :message="form.errors.description" />
                        </td>
                    </tr>

                </tbody>
                </table>

                <div class="mt-4">
                    <Button type="submit" :disabled="form.processing" class="rounded bg-green-500 px-4 py-2 text-white hover:bg-green-600 disabled:opacity-50">
                        Actualizar Producto
                    </Button>
                </div>
            </form>

        </div>
    </AppLayout>
</template>