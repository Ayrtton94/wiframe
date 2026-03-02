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
        image_path: string | null;
        image_url: string | null;
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
    image_path: null as File | null,
});

const handleImage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files ? target.files[0] : null;
    form.setData('image_path', file);
};  

const submit = () => {
    form.put(`/stores/${props.product.id}`, {
        preserveScroll: true,
        forceFormData: true,
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
              <div class="grid grid-cols-2 gap-6 bg-white p-6 rounded-2xl shadow">

                <div>
                    <Label>Código de Producto</Label>
                    <Input v-model="form.code_product" />
                    <InputError :message="form.errors.code_product" />
                </div>

                <div>
                    <Label>Nombre del Producto</Label>
                    <Input v-model="form.name_product" />
                    <InputError :message="form.errors.name_product" />
                </div>

                <div>
                    <Label>Tipo de Tela</Label>
                    <Input v-model="form.fabric_type" />
                    <InputError :message="form.errors.fabric_type" />
                </div>

                <div>
                    <Label>Color</Label>
                    <Input v-model="form.color" />
                    <InputError :message="form.errors.color" />
                </div>

                <div>
                    <Label>Stock</Label>
                    <Input v-model="form.stock" type="number" />
                    <InputError :message="form.errors.stock" />
                </div>

                <div>
                    <Label>Proveedor</Label>
                    <Input v-model="form.proveedor" />
                    <InputError :message="form.errors.proveedor" />
                </div>

                <div>
                    <Label>Precio</Label>
                    <Input v-model="form.price" type="number" step="0.01" />
                    <InputError :message="form.errors.price" />
                </div>

                <div>
                    <Label>Stock Mínimo</Label>
                    <Input v-model="form.stock_minimum" type="number" />
                    <InputError :message="form.errors.stock_minimum" />
                </div>

                <div class="col-span-2">
                    <Label>Descripción</Label>
                    <textarea
                        v-model="form.description"
                        class="w-full rounded-lg border p-2"
                    ></textarea>
                    <InputError :message="form.errors.description" />
                </div>

                <div class="col-span-2">
                    <Label>Foto</Label>

                    <div v-if="props.product.image_url" class="mb-3">
                        <img
                            :src="props.product.image_url"
                            class="h-24 w-24 rounded-xl object-cover shadow"
                        />
                    </div>

                    <input type="file" @change="handleImage" />
                    <InputError :message="form.errors.image_path" />
                </div>

            </div>

                <div class="mt-6">
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-green-600 hover:bg-green-700"
                >
                    Actualizar Producto
                </Button>
            </div>
            </form>

        </div>
    </AppLayout>
</template>