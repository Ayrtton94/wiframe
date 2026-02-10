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
        title: 'Clientes',
        href: 'customers/edit',
    },
];

const props = defineProps<{
    customer: {
        id: number;
        dni: string;
        name: string;
        phone: string | null;
        email: string;
        address: string | null;
        position: string | null;
    };
}>();

const form = useForm({
    dni: props.customer.dni,
    name: props.customer.name,
    phone: props.customer.phone || '',
    email: props.customer.email,
    position: props.customer.position || '',
    address: props.customer.address || '',
});

const submit = () => {
    form.put(`/customers/${props.customer.id}`, {
        preserveScroll: true,
    });
};

</script>
<template>
    <Head title="Editar Cliente" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-center px-4 py-8">
            <!-- Card -->
            <div
                class="w-full max-w-5xl rounded-xl border
                       border-gray-200 dark:border-white/10
                       bg-white dark:bg-white/5
                       p-6 backdrop-blur-md shadow-lg">

                <h2
                    class="mb-6 text-center text-2xl font-semibold
                           text-gray-800 dark:text-white">
                    Editar Cliente
                </h2>

                <form @submit.prevent="submit">
                    <div class="overflow-x-auto">
                        <table class="w-full border-separate border-spacing-4">
                            <tbody>

                                <tr class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <td class="text-sm font-medium text-gray-700 dark:text-white">
                                        RUC o DNI
                                        <Input
                                            v-model="form.dni"
                                            class="mt-1 w-full
                                                   bg-white dark:bg-transparent
                                                   text-gray-800 dark:text-white
                                                   border-gray-300 dark:border-gray-500
                                                   focus:border-green-500 focus:ring-green-500"
                                        />
                                        <InputError :message="form.errors.dni" />
                                    </td>

                                    <td class="text-sm font-medium text-gray-700 dark:text-white">
                                        Nombre o Razón Social
                                        <Input
                                            v-model="form.name"
                                            class="mt-1 w-full
                                                   bg-white dark:bg-transparent
                                                   text-gray-800 dark:text-white
                                                   border-gray-300 dark:border-gray-500
                                                   focus:border-green-500 focus:ring-green-500"
                                        />
                                        <InputError :message="form.errors.name" />
                                    </td>
                                </tr>

                                <tr class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <td class="text-sm font-medium text-gray-700 dark:text-white">
                                        Teléfono
                                        <Input
                                            v-model="form.phone"
                                            class="mt-1 w-full
                                                   bg-white dark:bg-transparent
                                                   text-gray-800 dark:text-white
                                                   border-gray-300 dark:border-gray-500
                                                   focus:border-green-500 focus:ring-green-500"
                                        />
                                        <InputError :message="form.errors.phone" />
                                    </td>

                                    <td class="text-sm font-medium text-gray-700 dark:text-white">
                                        Correo Electrónico
                                        <Input
                                            v-model="form.email"
                                            class="mt-1 w-full
                                                   bg-white dark:bg-transparent
                                                   text-gray-800 dark:text-white
                                                   border-gray-300 dark:border-gray-500
                                                   focus:border-green-500 focus:ring-green-500"
                                        />
                                        <InputError :message="form.errors.email" />
                                    </td>
                                </tr>

                                <tr class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <td class="text-sm font-medium text-gray-700 dark:text-white">
                                        Dirección
                                        <Input
                                            v-model="form.address"
                                            class="mt-1 w-full
                                                   bg-white dark:bg-transparent
                                                   text-gray-800 dark:text-white
                                                   border-gray-300 dark:border-gray-500
                                                   focus:border-green-500 focus:ring-green-500"
                                        />
                                        <InputError :message="form.errors.address" />
                                    </td>

                                    <td class="text-sm font-medium text-gray-700 dark:text-white">
                                        Cargo
                                        <Input
                                            v-model="form.position"
                                            class="mt-1 w-full
                                                   bg-white dark:bg-transparent
                                                   text-gray-800 dark:text-white
                                                   border-gray-300 dark:border-gray-500
                                                   focus:border-green-500 focus:ring-green-500"
                                        />
                                        <InputError :message="form.errors.position" />
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <!-- Botones -->
                    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-green-500 px-6 py-2 text-white hover:bg-green-600 disabled:opacity-50"
                        >
                            Actualizar Cliente
                        </Button>

                        <Link
                            href="/customers"
                            class="rounded-lg bg-blue-500 px-6 py-2 text-center text-white hover:bg-blue-600"
                        >
                            Volver a Clientes
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>