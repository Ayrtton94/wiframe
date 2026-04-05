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
        price: number;
        minimum_stock: number;
        wholesale_price: number;
        public_price: number;
        price_roll?: number;
        special_price?: number;
        location?: string;
        description?: string;
        image_path?: string | null;
        image_url?: string | null;
    };
    suppliers: Array<{
        id: number;
        company_name: string;
    }>;
}>();

const form = useForm({
    code_product: props.product.code_product,
    name_product: props.product.name_product,
    fabric_type: props.product.fabric_type,
    color: props.product.color,
    kilos: props.product.kilos,
    metros: props.product.metros,
    proveedor: props.product.proveedor,
    price: props.product.price,
    minimum_stock: props.product.minimum_stock,
    wholesale_price: props.product.wholesale_price,
    public_price: props.product.public_price,
    price_roll: props.product.price_roll || null,
    special_price: props.product.special_price || null,
    location: props.product.location || null,
    description: props.product.description || null,
    image: null as File | null,
});

const handleImage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files ? target.files[0] : null;
    form.image = file;
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
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <Link href="/stores" class="mb-4 inline-block rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                    Volver al Inventario
                </Link>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <Label for="code_product">Código de Producto</Label>
                        <Input id="code_product" v-model="form.code_product" class="w-full" />
                        <InputError :message="form.errors.code_product" />
                    </div>

                    <div>
                        <Label for="name_product">Nombre del Producto</Label>
                        <Input id="name_product" v-model="form.name_product" class="w-full" />
                        <InputError :message="form.errors.name_product" />
                    </div>

                    <div>
                        <Label for="fabric_type">Tipo de Tela</Label>
                        <Input id="fabric_type" v-model="form.fabric_type" class="w-full" />
                        <InputError :message="form.errors.fabric_type" />
                    </div>

                    <div>
                        <Label for="color">Color</Label>
                        <Input id="color" v-model="form.color" class="w-full" />
                        <InputError :message="form.errors.color" />
                    </div>

                    <div>
                        <Label for="proveedor">Proveedor</Label>
                        <select id="proveedor" v-model="form.proveedor" class="w-full rounded border px-3 py-2">
                            <option value="" disabled>Selecciona un proveedor</option>
                            <option v-for="supplier in props.suppliers" :key="supplier.id" :value="supplier.company_name">
                                {{ supplier.company_name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.proveedor" />
                    </div>

                    <div>
                        <Label for="minimum_stock">Stock Mínimo</Label>
                        <Input id="minimum_stock" v-model="form.minimum_stock" type="number" class="w-full" />
                        <InputError :message="form.errors.minimum_stock" />
                    </div>

                    <div>
                        <Label for="kilos">Kilos</Label>
                        <Input id="kilos" v-model="form.kilos" type="number" step="0.01" class="w-full" />
                        <InputError :message="form.errors.kilos" />
                    </div>

                    <div>
                        <Label for="metros">Metros</Label>
                        <Input id="metros" v-model="form.metros" type="number" step="0.01" class="w-full" />
                        <InputError :message="form.errors.metros" />
                    </div>

                    <div>
                        <Label for="price">Precio</Label>
                        <Input id="price" v-model="form.price" type="number" step="0.01" class="w-full" />
                        <InputError :message="form.errors.price" />
                    </div>

                    <div>
                        <Label for="public_price">Precio Público</Label>
                        <Input id="public_price" v-model="form.public_price" type="number" step="0.01" class="w-full" />
                        <InputError :message="form.errors.public_price" />
                    </div>

                    <div>
                        <Label for="wholesale_price">Precio Mayorista</Label>
                        <Input id="wholesale_price" v-model="form.wholesale_price" type="number" step="0.01" class="w-full" />
                        <InputError :message="form.errors.wholesale_price" />
                    </div>

                    <div>
                        <Label for="price_roll">Precio por Rollo</Label>
                        <Input id="price_roll" v-model="form.price_roll" type="number" step="0.01" class="w-full" />
                        <InputError :message="form.errors.price_roll" />
                    </div>

                    <div>
                        <Label for="special_price">Precio Especial</Label>
                        <Input id="special_price" v-model="form.special_price" type="number" step="0.01" class="w-full" />
                        <InputError :message="form.errors.special_price" />
                    </div>

                    <div>
                        <Label for="location">Ubicación</Label>
                        <Input id="location" v-model="form.location" class="w-full" />
                        <InputError :message="form.errors.location" />
                    </div>

                    <div>
                        <Label for="image">Imagen del producto</Label>
                        <div v-if="currentImageSrc" class="mb-2">
                            <img :src="currentImageSrc" alt="Imagen actual" class="h-20 w-20 rounded object-cover" />
                        </div>
                        <Input id="image" type="file" accept="image/*" @change="handleImage" class="w-full" />
                        <InputError :message="form.errors.image" />
                    </div>
                </div>

                <div>
                    <Label for="description">Descripción</Label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        class="w-full rounded border border-gray-300 px-3 py-2"
                    />
                    <InputError :message="form.errors.description" />
                </div>
                <div class="mt-4">
                    <Button type="submit" :disabled="form.processing" class="rounded bg-green-500 px-4 py-2 text-white hover:bg-green-600 disabled:opacity-50">
                        Actualizar Producto
                    </Button>
                </div>
            </form>

        </div>
    </AppLayout>
    
</template>