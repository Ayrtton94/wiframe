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
        title: 'Proveedor Crear',
        href: 'suppliers/create',
    },
];

const form = useForm({
    ruc: '',
    company_name: '',
    category: '',
    phone: '',
    email: '',
    name: '',
    address: '',
    city: '',
    country: '',
    account: '',
    cod_swift: '',
    bank_name: '',
    bank_address: '',
    bank_city: '',
    bank_country: '',
    bank_cod_swift: '',
    bank_name2: '',
    bank_address2: '',
    bank_cod_swift2: '',
    others: '',
});

const submit = () => {
    form.post('/suppliers', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>
<template>
    <Head title="Crear Proveedor" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="space-y-8 bg-slate-50 px-4 py-6
                   dark:bg-slate-950
                   sm:px-6 lg:px-8"
        >
            <form
                @submit.prevent="submit"
                class="mx-auto w-full max-w-6xl space-y-8"
            >
                <!-- INFORMACIÓN GENERAL -->
                <Card
                    class="w-full overflow-hidden rounded-xl
                           border border-slate-200
                           bg-white shadow-sm
                           transition-shadow hover:shadow-md
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <CardHeader
                        class="border-b border-slate-200
                               bg-gradient-to-r
                               from-slate-50 to-blue-50
                               dark:border-slate-700
                               dark:from-slate-900
                               dark:to-slate-800"
                    >
                        <CardTitle
                            class="text-lg font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            Información General
                        </CardTitle>

                        <p
                            class="mt-1 text-sm
                                   text-slate-500
                                   dark:text-slate-400"
                        >
                            Información principal del proveedor.
                        </p>
                    </CardHeader>

                    <CardContent class="pt-6">
                        <div
                            class="grid grid-cols-1 gap-6
                                   md:grid-cols-2"
                        >
                            <!-- CÓDIGO -->
                            <div class="space-y-2">
                                <Label
                                    for="code_product"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Código de Proveedor u otros
                                </Label>

                                <Input
                                    id="code_product"
                                    v-model="form.ruc"
                                    type="text"
                                    placeholder="Ingrese código de proveedor"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.ruc
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- RAZÓN SOCIAL -->
                            <div class="space-y-2">
                                <Label
                                    for="name_product"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Razón Social
                                </Label>

                                <Input
                                    id="name_product"
                                    v-model="
                                        form.company_name
                                    "
                                    type="text"
                                    placeholder="Ingrese razón social"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.company_name
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- CATEGORÍA -->
                            <div class="space-y-2">
                                <Label
                                    for="fabric_type"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Categoría
                                </Label>

                                <Input
                                    id="fabric_type"
                                    v-model="form.category"
                                    type="text"
                                    placeholder="Ej. Textiles, Químicos"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.category
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- TELÉFONO -->
                            <div class="space-y-2">
                                <Label
                                    for="phone"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Teléfono Principal
                                </Label>

                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="text"
                                    placeholder="+51 999 999 999"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.phone
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- CORREO -->
                            <div
                                class="space-y-2 md:col-span-2"
                            >
                                <Label
                                    for="email"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Correo Electrónico
                                </Label>

                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="contacto@proveedor.com"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.email
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- DATOS DEL BENEFICIARIO -->
                <Card
                    class="w-full overflow-hidden rounded-xl
                           border border-slate-200
                           bg-white shadow-sm
                           transition-shadow hover:shadow-md
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <CardHeader
                        class="border-b border-slate-200
                               bg-gradient-to-r
                               from-slate-50 to-purple-50
                               dark:border-slate-700
                               dark:from-slate-900
                               dark:to-slate-800"
                    >
                        <CardTitle
                            class="text-lg font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            Datos del Beneficiario
                        </CardTitle>
                    </CardHeader>

                    <CardContent class="pt-6">
                        <div
                            class="grid grid-cols-1 gap-6
                                   md:grid-cols-2"
                        >
                            <!-- NOMBRE -->
                            <div class="space-y-2">
                                <Label
                                    for="name"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Nombre del Beneficiario
                                </Label>

                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Ingrese nombre"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.name
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- DIRECCIÓN -->
                            <div class="space-y-2">
                                <Label
                                    for="address"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Dirección
                                </Label>

                                <Input
                                    id="address"
                                    v-model="form.address"
                                    type="text"
                                    placeholder="Calle, número, piso"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.address
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- CIUDAD -->
                            <div class="space-y-2">
                                <Label
                                    for="city"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Ciudad
                                </Label>

                                <Input
                                    id="city"
                                    v-model="form.city"
                                    type="text"
                                    placeholder="Ingrese ciudad"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.city
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- PAÍS -->
                            <div class="space-y-2">
                                <Label
                                    for="country"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    País
                                </Label>

                                <Input
                                    id="country"
                                    v-model="form.country"
                                    type="text"
                                    placeholder="Ingrese país"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.country
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- CUENTA -->
                            <div class="space-y-2">
                                <Label
                                    for="account"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    N° de Cuenta
                                </Label>

                                <Input
                                    id="account"
                                    v-model="form.account"
                                    type="text"
                                    placeholder="Número de cuenta bancaria"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.account
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- SWIFT OCULTO -->
                            <div
                                class="space-y-2"
                                hidden
                            >
                                <Label
                                    for="swift_code"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Código SWIFT
                                </Label>

                                <Input
                                    id="swift_code"
                                    v-model="form.cod_swift"
                                    type="text"
                                    placeholder="Ej. ABCDPEMX"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.cod_swift
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- DATOS DEL BANCO -->
                <Card
                    class="w-full overflow-hidden rounded-xl
                           border border-slate-200
                           bg-white shadow-sm
                           transition-shadow hover:shadow-md
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <CardHeader
                        class="border-b border-slate-200
                               bg-gradient-to-r
                               from-slate-50 to-green-50
                               dark:border-slate-700
                               dark:from-slate-900
                               dark:to-slate-800"
                    >
                        <CardTitle
                            class="text-lg font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            Datos del Banco Beneficiario
                        </CardTitle>
                    </CardHeader>

                    <CardContent class="pt-6">
                        <div
                            class="grid grid-cols-1 gap-6
                                   md:grid-cols-2"
                        >
                            <!-- NOMBRE BANCO -->
                            <div class="space-y-2">
                                <Label
                                    for="bank_name"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Nombre del Banco
                                </Label>

                                <Input
                                    id="bank_name"
                                    v-model="form.bank_name"
                                    type="text"
                                    placeholder="Ej. Banco de Crédito del Perú"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.bank_name
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- DIRECCIÓN BANCO -->
                            <div class="space-y-2">
                                <Label
                                    for="bank_address"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Dirección del Banco
                                </Label>

                                <Input
                                    id="bank_address"
                                    v-model="
                                        form.bank_address
                                    "
                                    type="text"
                                    placeholder="Calle, número, piso"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.bank_address
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- CIUDAD BANCO -->
                            <div class="space-y-2">
                                <Label
                                    for="bank_city"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Ciudad
                                </Label>

                                <Input
                                    id="bank_city"
                                    v-model="
                                        form.bank_city
                                    "
                                    type="text"
                                    placeholder="Ciudad donde se ubica el banco"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.bank_city
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- PAÍS BANCO -->
                            <div
                                class="space-y-2 md:col-span-1"
                            >
                                <Label
                                    for="bank_country"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    País
                                </Label>

                                <Input
                                    id="bank_country"
                                    v-model="
                                        form.bank_country
                                    "
                                    type="text"
                                    placeholder="País donde se ubica el banco"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.bank_country
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- SWIFT -->
                            <div
                                class="space-y-2 md:col-span-2"
                            >
                                <Label
                                    for="bank_swift"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Código SWIFT del Banco
                                </Label>

                                <Input
                                    id="bank_swift"
                                    v-model="
                                        form.bank_cod_swift
                                    "
                                    type="text"
                                    placeholder="Código SWIFT internacional"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors
                                            .bank_cod_swift
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- BANCO INTERNACIONAL -->
                <Card
                    class="w-full overflow-hidden rounded-xl
                           border border-slate-200
                           bg-white shadow-sm
                           transition-shadow hover:shadow-md
                           dark:border-slate-700
                           dark:bg-slate-900"
                >
                    <CardHeader
                        class="border-b border-slate-200
                               bg-gradient-to-r
                               from-slate-50 to-amber-50
                               dark:border-slate-700
                               dark:from-slate-900
                               dark:to-slate-800"
                    >
                        <CardTitle
                            class="text-lg font-semibold
                                   text-slate-900
                                   dark:text-slate-100"
                        >
                            Banco Internacional

                            <span
                                class="text-xs font-normal
                                       text-slate-500
                                       dark:text-slate-400"
                            >
                                (Opcional)
                            </span>
                        </CardTitle>
                    </CardHeader>

                    <CardContent class="pt-6">
                        <div
                            class="grid grid-cols-1 gap-6
                                   md:grid-cols-2"
                        >
                            <!-- BANCO INTERNACIONAL -->
                            <div class="space-y-2">
                                <Label
                                    for="bank_name2"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Nombre del Banco Internacional
                                </Label>

                                <Input
                                    id="bank_name2"
                                    v-model="
                                        form.bank_name2
                                    "
                                    type="text"
                                    placeholder="Ej. JPMorgan Chase Bank"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.bank_name2
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- DIRECCIÓN -->
                            <div class="space-y-2">
                                <Label
                                    for="bank_address2"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Dirección del Banco
                                </Label>

                                <Input
                                    id="bank_address2"
                                    v-model="
                                        form.bank_address2
                                    "
                                    type="text"
                                    placeholder="Calle, número, piso"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors
                                            .bank_address2
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- SWIFT -->
                            <div class="space-y-2">
                                <Label
                                    for="bank_swift2"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Código SWIFT
                                </Label>

                                <Input
                                    id="bank_swift2"
                                    v-model="
                                        form.bank_cod_swift2
                                    "
                                    type="text"
                                    placeholder="Ej. CHASUS33"
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors
                                            .bank_cod_swift2
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>

                            <!-- INFORMACIÓN ADICIONAL -->
                            <div
                                class="space-y-2 md:col-span-2"
                            >
                                <Label
                                    for="others"
                                    class="text-sm font-semibold
                                           text-slate-700
                                           dark:text-slate-300"
                                >
                                    Información Adicional
                                </Label>

                                <Input
                                    id="others"
                                    v-model="form.others"
                                    type="text"
                                    placeholder="Otros datos relevantes..."
                                    class="w-full
                                           dark:border-slate-600
                                           dark:bg-slate-800
                                           dark:text-slate-100"
                                />

                                <InputError
                                    :message="
                                        form.errors.others
                                    "
                                    class="mt-1 text-xs"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- BOTONES -->
                <div
                    class="flex flex-col-reverse
                           gap-3 pt-6
                           sm:flex-row
                           sm:justify-end"
                >
                    <Link
                        href="/suppliers"
                        class="inline-flex items-center
                               justify-center
                               rounded-lg
                               border border-slate-300
                               bg-white px-6 py-2.5
                               font-medium
                               text-slate-900
                               transition-colors
                               hover:bg-slate-50
                               dark:border-slate-700
                               dark:bg-slate-800
                               dark:text-slate-100
                               dark:hover:bg-slate-700"
                    >
                        Cancelar
                    </Link>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center
                               justify-center
                               rounded-lg
                               bg-blue-600 px-6 py-2.5
                               font-medium text-white
                               shadow-sm
                               transition-colors
                               hover:bg-blue-700
                               disabled:cursor-not-allowed
                               disabled:opacity-50
                               dark:hover:bg-blue-500"
                    >
                        {{
                            form.processing
                                ? 'Guardando...'
                                : 'Crear Proveedor'
                        }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>