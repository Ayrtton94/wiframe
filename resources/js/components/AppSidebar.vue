<script setup lang="ts">
import AppLogo from './AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    Boxes,
    LayoutGrid,
    Factory,
    ShoppingCart,
    ShieldCheck,
    User,
    Users,
} from 'lucide-vue-next';

const page = usePage();
const roles = (page.props.auth?.roles ?? []) as string[];
const isAdmin = roles.includes('admin');
const isAlmacen = roles.includes('almacen');
const isTienda = roles.includes('tienda');
const isVendedor = roles.includes('vendedor');

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    ...(isAdmin || isTienda || isVendedor
        ? [
              {
                  title: 'Ventas',
                  href: '/sales',
                  icon: ShoppingCart,
              },
          ]
        : []),
    ...(isAdmin || isVendedor
        ? [
              {
                  title: 'Productos',
                  href: '/stores',
                  icon: Boxes,
              },
              {
                  title: 'Clientes',
                  href: '/customers',
                  icon: Users,
              },
          ]
        : []),
    ...(isAdmin
        ? [
              {
                  title: 'Proveedores',
                  href: '/suppliers',
                  icon: Factory,
              },
              {
                  title: 'Personal',
                  href: '/employees',
                  icon: User,
              },
              {
                  title: 'Reportes',
                  href: '/reports',
                  icon: BarChart3,
              },
              {
                  title: 'Almacenes',
                  href: '/warehouses',
                  icon: Boxes,
              },
              {
                  title: 'Stock por almacén',
                  href: '/warehouse-stocks',
                  icon: Boxes,
              },
              {
                  title: 'Traslados',
                  href: '/transfers',
                  icon: Boxes,
              },
              {
                  title: 'Roles de usuarios',
                  href: '/users/roles',
                  icon: ShieldCheck,
              },
          ]
        : []),
    ...(isAlmacen
        ? [
              {
                  title: 'Stock por almacén',
                  href: '/warehouse-stocks',
                  icon: Boxes,
              },
          ]
        : []),
];

const mainNavItems: NavItem[] = [

    // 🔥 ADMIN VE TODO
    ...(isAdmin
        ? [
            { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
            { title: 'Ventas', href: '/sales', icon: ShoppingCart },
            { title: 'Productos', href: '/stores', icon: Boxes },
            { title: 'Clientes', href: '/customers', icon: Users },
            { title: 'Proveedores', href: '/suppliers', icon: Factory },
            { title: 'Personal', href: '/employees', icon: User },
            { title: 'Reportes', href: '/reports', icon: BarChart3 },
            { title: 'Almacenes', href: '/warehouses', icon: Boxes },
            { title: 'Stock por almacén', href: '/warehouse-stocks', icon: Boxes },
            { title: 'Traslados', href: '/transfers', icon: Boxes },
            { title: 'Roles de usuarios', href: '/users/roles', icon: ShieldCheck },
        ]
        : []),

    // 🏬 ALMACÉN
    ...(isAlmacen
        ? [
            { title: 'Stock por almacén', href: '/warehouse-stocks', icon: Boxes },
        ]
        : []),

    // 🏪 TIENDA
    ...(isTienda
        ? [
            { title: 'Ventas', href: '/sales', icon: ShoppingCart },
            { title: 'Stock', href: '/warehouse-stocks', icon: Boxes },
        ]
        : []),
];
const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
