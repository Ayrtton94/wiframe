<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Catalog',
        href: '/catalog',
    },
];

defineProps<{
  products: {
    data: Array<{
      id: number;
      code_product: string;
      name_product: string;
      fabric_type: string;
      color: string;
      proveedor: string;
      price: number;
      wholesale_price: number;
      public_price: number;
      image_url: string | null;
    }>;
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
  };
  filters: {
    search?: string;
    fabric_type?: string;
  };
}>();


</script>

<template>
    <Head title="Catalog" />
    <AppLayout :breadcrumbs="breadcrumbs">
        
    </AppLayout>
</template>
