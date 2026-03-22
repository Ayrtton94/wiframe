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
  product: {
    id: number;
    code_product: string;
    name_product: string;
    fabric_type: string;
    color: string;
    proveedor: string;
    price: number;
    public_price: number;
    wholesale_price: number;
    price_roll?: number | null;
    special_price?: number | null;
    location?: string | null;
    description?: string | null;
    image_url?: string | null;
  };
  stock_summary: {
    kilos_available: number;
    metros_available: number;
    kilos_reserved: number;
    metros_reserved: number;
  };
  stock_locations: Array<{
    id: number;
    warehouse_id: number;
    warehouse_name: string;
    warehouse_code: string;
    kilos_available: number;
    metros_available: number;
    kilos_reserved: number;
    metros_reserved: number;
  }>;
}>();


</script>

<template>
  <Head :title="product.name_product" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ product.name_product }}</h1>
          <p class="text-sm text-gray-500">{{ product.code_product }}</p>
        </div>

        <Link href="/stores" class="rounded bg-blue-600 px-4 py-2 text-white">
          Volver
        </Link>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded border bg-white p-4">
          <img
            v-if="product.image_url"
            :src="product.image_url"
            class="h-64 w-full rounded object-cover"
          />
          <div v-else class="flex h-64 items-center justify-center rounded bg-gray-100">
            Sin imagen
          </div>
        </div>

        <div class="rounded border bg-white p-4 space-y-2">
          <p><strong>Código:</strong> {{ product.code_product }}</p>
          <p><strong>Tipo de tela:</strong> {{ product.fabric_type }}</p>
          <p><strong>Color:</strong> {{ product.color }}</p>
          <p><strong>Proveedor:</strong> {{ product.proveedor }}</p>
          <p><strong>Ubicación:</strong> {{ product.location || 'No definida' }}</p>
          <p><strong>Descripción:</strong> {{ product.description || 'Sin descripción' }}</p>
        </div>
      </div>

      <div class="grid gap-4 md:grid-cols-5">
        <div class="rounded border bg-white p-4">Precio: {{ product.price }}</div>
        <div class="rounded border bg-white p-4">Público: {{ product.public_price }}</div>
        <div class="rounded border bg-white p-4">Mayorista: {{ product.wholesale_price }}</div>
        <div class="rounded border bg-white p-4">Rollo: {{ product.price_roll ?? 0 }}</div>
        <div class="rounded border bg-white p-4">Especial: {{ product.special_price ?? 0 }}</div>
      </div>

      <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded border bg-white p-4">Kg disponibles: {{ stock_summary.kilos_available }}</div>
        <div class="rounded border bg-white p-4">M disponibles: {{ stock_summary.metros_available }}</div>
        <div class="rounded border bg-white p-4">Kg reservados: {{ stock_summary.kilos_reserved }}</div>
        <div class="rounded border bg-white p-4">M reservados: {{ stock_summary.metros_reserved }}</div>
      </div>

      <div class="overflow-x-auto rounded border bg-white">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs uppercase">Almacén</th>
              <th class="px-4 py-3 text-left text-xs uppercase">Código</th>
              <th class="px-4 py-3 text-left text-xs uppercase">Kg disp.</th>
              <th class="px-4 py-3 text-left text-xs uppercase">M disp.</th>
              <th class="px-4 py-3 text-left text-xs uppercase">Kg res.</th>
              <th class="px-4 py-3 text-left text-xs uppercase">M res.</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in stock_locations" :key="row.id">
              <td class="px-4 py-3">{{ row.warehouse_name }}</td>
              <td class="px-4 py-3">{{ row.warehouse_code }}</td>
              <td class="px-4 py-3">{{ row.kilos_available }}</td>
              <td class="px-4 py-3">{{ row.metros_available }}</td>
              <td class="px-4 py-3">{{ row.kilos_reserved }}</td>
              <td class="px-4 py-3">{{ row.metros_reserved }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

