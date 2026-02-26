<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Empleados',
        href: 'employees/edit',
    },
];

const props = defineProps<{
    employee: {
        id: number;
        dni: string;
        name: string;
        area: string;
        phone: string;
        foto: string | null;
        foto_url: string | null;
    };
}>();

const form = useForm({
    dni: props.employee.dni,
    name: props.employee.name,
    area: props.employee.area,
    phone: props.employee.phone,
    foto: null as File | null,
});

const handleImage = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files ? target.files[0] : null;
    form.foto = file;
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/employees/${props.employee.id}`, {
        preserveScroll: true,
        forceFormData: true,
    });
};

</script>
<template>
    <Head title="Editar Empleado" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-center px-4 py-10">
            <!-- Card -->
            <div
                class="w-full max-w-4xl rounded-xl border
                       border-gray-200 dark:border-white/10
                       bg-white dark:bg-white/5
                       shadow-lg backdrop-blur-md"
            >
                <!-- Card Header -->
                <div
                    class="border-b border-gray-200 dark:border-white/10
                           px-6 py-4 text-center"
                >
                    <h2
                        class="text-2xl font-semibold
                               text-gray-800 dark:text-white"
                    >
                        Editar Empleado
                    </h2>
                </div>

                <!-- Card Body -->
                <div class="p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                            <!-- DNI -->
                            <div>
                                <Label
                                    for="dni"
                                    class="mb-1 block text-gray-700 dark:text-white"
                                >
                                    DNI
                                </Label>
                                <Input
                                    id="dni"
                                    v-model="form.dni"
                                    type="text"
                                    class="w-full
                                           bg-white dark:bg-transparent
                                           text-gray-800 dark:text-white
                                           border-gray-300 dark:border-gray-500
                                           focus:border-green-500 focus:ring-green-500"
                                    :disabled="form.processing"
                                />
                                <InputError
                                    :message="form.errors.dni"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Nombre -->
                            <div>
                                <Label
                                    for="name"
                                    class="mb-1 block text-gray-700 dark:text-white"
                                >
                                    Nombre
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full
                                           bg-white dark:bg-transparent
                                           text-gray-800 dark:text-white
                                           border-gray-300 dark:border-gray-500
                                           focus:border-green-500 focus:ring-green-500"
                                    :disabled="form.processing"
                                />
                                <InputError
                                    :message="form.errors.name"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <Label
                                    for="phone"
                                    class="mb-1 block text-gray-700 dark:text-white"
                                >
                                    Teléfono
                                </Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="text"
                                    class="w-full
                                           bg-white dark:bg-transparent
                                           text-gray-800 dark:text-white
                                           border-gray-300 dark:border-gray-500
                                           focus:border-green-500 focus:ring-green-500"
                                    :disabled="form.processing"
                                />
                                <InputError
                                    :message="form.errors.phone"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Área -->
                            <div>
                                <Label
                                    for="area"
                                    class="mb-1 block text-gray-700 dark:text-white"
                                >
                                    Área
                                </Label>
                                <Input
                                    id="area"
                                    v-model="form.area"
                                    type="text"
                                    class="w-full
                                           bg-white dark:bg-transparent
                                           text-gray-800 dark:text-white
                                           border-gray-300 dark:border-gray-500
                                           focus:border-green-500 focus:ring-green-500"
                                    :disabled="form.processing"
                                />
                                <InputError
                                    :message="form.errors.area"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Foto -->
                            <div>
                                <Label
                                    for="foto"
                                    class="mb-1 block text-gray-700 dark:text-white"
                                >
                                    Foto
                                </Label>
                                <div v-if="props.employee.foto_url" class="mb-2">
                                    <img :src="props.employee.foto_url" alt="Foto actual" class="w-20 h-20 rounded-full object-cover">
                                </div>
                                <input
                                    id="foto"
                                    type="file"
                                    accept="image/*"
                                    @change="handleImage"
                                    class="w-full rounded-lg border
                                        border-gray-300 dark:border-gray-500
                                        bg-white dark:bg-transparent
                                        text-gray-800 dark:text-white"
                                />
                                <InputError
                                    :message="form.errors.foto"
                                    class="mt-2"
                                />
                            </div>

                        </div>

                        <!-- Botones -->
                        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-green-500 px-6 py-2 text-white hover:bg-green-600 disabled:opacity-50"
                            >
                                Actualizar Empleado
                            </Button>

                            <Link
                                href="/employees"
                                class="rounded-lg bg-blue-500 px-6 py-2 text-center text-white hover:bg-blue-600"
                            >
                                Volver a Empleados
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>