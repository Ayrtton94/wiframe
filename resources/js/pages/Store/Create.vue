<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';

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
    image_path: null as File | null,
});

const handleImage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.image_path = target.files[0];
    }
};

const submit = () => {
    form.post('/stores', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Almacen Crear" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
<form @submit.prevent="submit">
            <Card class="w-full max-w-5xl mx-auto rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6 backdrop-blur-md shadow-lg">
                <CardHeader>
                    <CardTitle>Crear Producto</CardTitle>
                </CardHeader>
                
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <Label for="code_product" class="mb-1 block font-medium text-gray-700">Código de Producto</Label>
                                <Input id="code_product" v-model="form.code_product" type="text" class="w-full" />
                                <InputError :message="form.errors.code_product" class="mt-1" />
                            </div>

                            <div>
                                <Label for="name_product" class="mb-1 block font-medium text-gray-700">Nombre del Producto</Label>
                                <Input id="name_product" v-model="form.name_product" type="text" class="w-full" />
                                <InputError :message="form.errors.name_product" class="mt-1" />
                            </div>

                            <div>
                                <Label for="fabric_type" class="mb-1 block font-medium text-gray-700">Tipo de Tela</Label>
                                <Input id="fabric_type" v-model="form.fabric_type" type="text" class="w-full" />
                                <InputError :message="form.errors.fabric_type" class="mt-1" />
                            </div>

                            <div>
                                <Label for="color" class="mb-1 block font-medium text-gray-700">Color</Label>
                                <Input id="color" v-model="form.color" type="text" class="w-full" />
                                <InputError :message="form.errors.color" class="mt-1" />
                            </div> 
                        </div>                                                  
                    </CardContent>
            </Card>
            <Card class="w-full max-w-5xl mx-auto rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6 backdrop-blur-md shadow-lg">
                <CardHeader>
                    <CardTitle>Proveedor y Stock</CardTitle>
                </CardHeader>
                <CardContent>
                            <div>
                                <Label for="proveedor" class="mb-1 block font-medium text-gray-700">Proveedor</Label>
                                <Input id="proveedor" v-model="form.proveedor" type="text" class="w-full" />
                                <InputError :message="form.errors.proveedor" class="mt-1" />
                            </div>

                            <div>
                                <Label for="kilos" class="mb-1 block font-medium text-gray-700">Kilos</Label>
                                <Input id="kilos" v-model="form.kilos" type="number" step="0.01" class="w-full" />
                                <InputError :message="form.errors.kilos" class="mt-1" />
                            </div>

                            <div>
                                <Label for="metros" class="mb-1 block font-medium text-gray-700">Metros</Label>
                                <Input id="metros" v-model="form.metros" type="number" step="0.01" class="w-full" />
                                <InputError :message="form.errors.metros" class="mt-1" />
                            </div>

                            <div>
                                <Label for="minimum_stock" class="mb-1 block font-medium text-gray-700">Stock Mínimo</Label>
                                <Input id="minimum_stock" v-model="form.minimum_stock" type="number" class="w-full" />
                                <InputError :message="form.errors.minimum_stock" class="mt-1" />
                            </div>
                    </CardContent>
            </Card>
             <Card class="w-full max-w-5xl mx-auto rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6 backdrop-blur-md shadow-lg">
                <CardHeader>
                    <CardTitle>Precios</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <Label for="price" class="mb-1 block font-medium text-gray-700">Precio</Label>
                            <Input id="price" v-model="form.price" type="number" step="0.01" class="w-full" />
                            <InputError :message="form.errors.price" class="mt-1" />
                        </div>

                        <div>
                            <Label for="public_price" class="mb-1 block font-medium text-gray-700">Precio Público</Label>
                            <Input id="public_price" v-model="form.public_price" type="number" step="0.01" class="w-full" />
                            <InputError :message="form.errors.public_price" class="mt-1" />
                        </div>

                        <div>
                            <Label for="wholesale_price" class="mb-1 block font-medium text-gray-700">Precio Mayorista</Label>
                            <Input id="wholesale_price" v-model="form.wholesale_price" type="number" step="0.01" class="w-full" />
                            <InputError :message="form.errors.wholesale_price" class="mt-1" />
                        </div>

                        <div>
                            <Label for="price_roll" class="mb-1 block font-medium text-gray-700">Precio por Rollo</Label>
                            <Input id="price_roll" v-model="form.price_roll" type="number" step="0.01" class="w-full" />
                            <InputError :message="form.errors.price_roll" class="mt-1" />
                        </div>

                        <div class="col-span-2">
                            <Label for="special_price" class="mb-1 block font-medium text-gray-700">Precio Especial</Label>
                            <Input id="special_price" v-model="form.special_price" type="number" step="0.01" class="w-full" />
                            <InputError :message="form.errors.special_price" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <Label for="location" class="mb-1 block font-medium text-gray-700">Ubicación</Label>
                        <Input id="location" v-model="form.location" type="text" class="w-full" />
                        <InputError :message="form.errors.location" class="mt-1" />
                    </div>
                    <!-- Foto -->
                    <div>
                        <Label  for="image_path" class="mb-1 block text-gray-700 dark:text-white">Foto</Label>
                        <input  id="image_path" type="file" accept="image/*" @change="handleImage"  class="w-full rounded-lg border border-gray-300 dark:border-gray-500 bg-white dark:bg-transparent text-gray-800 dark:text-white" />
                        <InputError :message="form.errors.image_path" class="mt-2" />
                    </div>


                    <div>
                        <Label for="description" class="mb-1 block font-medium text-gray-700">Descripción</Label>
                        <textarea id="description" v-model="form.description" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"></textarea>
                        <InputError :message="form.errors.description" class="mt-1" />
                    </div>
                </CardContent>
                <CardFooter class="flex justify-between items-center">
                    <Button type="submit" :disabled="form.processing" class="bg-green-500 hover:bg-green-600 disabled:opacity-50">
                        Crear Producto
                    </Button>
                    <Link href="/stores" class="inline-block rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                        Volver al Inventario
                    </Link>
                </CardFooter>
            </Card>           
</form>
        </div>
    </AppLayout>
</template>
