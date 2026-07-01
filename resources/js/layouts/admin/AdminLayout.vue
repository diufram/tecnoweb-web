<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppLogo from '@/components/AppLogo.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BarChart3, Barcode, Boxes, FileText, Handshake, LayoutGrid, Package, Palette, ShoppingCart, Truck, UserRound, Users } from 'lucide-vue-next';
import { computed, type Component } from 'vue';

type Actor = 'propietario' | 'proveedor';

interface AdminNavItem {
    title: string;
    href: string;
    icon: Component;
}

const props = defineProps<{
    actor: Actor;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const page = usePage();

const actorLabel = computed(() => (props.actor === 'propietario' ? 'Propietario' : 'Proveedor'));
const dashboardHref = computed(() => (props.actor === 'propietario' ? route('dashboard.propietario') : route('dashboard.proveedor')));

const stripOrigin = (href: string) => href.replace(/^https?:\/\/[^/]+/, '');

const isActive = (href: string) => {
    if (href.startsWith('#')) {
        return false;
    }

    const path = stripOrigin(href);
    const current = page.url.split('?')[0] ?? '';

    return current === path || current.startsWith(`${path}/`);
};

const navItems = computed<AdminNavItem[]>(() => {
    if (props.actor === 'propietario') {
        return [
            { title: 'Dashboard', href: route('dashboard.propietario'), icon: LayoutGrid },
            { title: 'Productos', href: route('propietario.productos.index'), icon: Package },
            { title: 'Lotes', href: route('propietario.lotes.index'), icon: Barcode },
            { title: 'Inventario', href: route('propietario.inventario.index'), icon: Boxes },
            { title: 'Compras', href: route('propietario.compras.index'), icon: ShoppingCart },
            { title: 'Clientes', href: route('propietario.clientes.index'), icon: Users },
            { title: 'Proveedores', href: route('propietario.proveedores.index'), icon: Truck },
            { title: 'Reportes', href: '#reportes', icon: BarChart3 },
            { title: 'Perfil', href: route('profile.edit'), icon: UserRound },
            { title: 'Apariencia', href: route('appearance'), icon: Palette },
        ];
    }

    return [
        { title: 'Dashboard', href: route('dashboard.proveedor'), icon: LayoutGrid },
        { title: 'Solicitudes', href: route('proveedor.solicitudes'), icon: FileText },
        { title: 'Contraofertas', href: route('proveedor.contraofertas'), icon: Handshake },
        { title: 'Compras', href: route('proveedor.compras'), icon: ShoppingCart },
        { title: 'Historial', href: route('proveedor.historial'), icon: BarChart3 },
        { title: 'Perfil', href: route('profile.edit'), icon: UserRound },
        { title: 'Apariencia', href: route('appearance'), icon: Palette },
    ];
});
</script>

<template>
    <AppShell variant="sidebar">
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" as-child>
                            <Link :href="dashboardHref">
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <div class="px-2 pb-2 text-xs font-medium uppercase tracking-wide text-muted-foreground">Panel {{ actorLabel }}</div>
            </SidebarHeader>

            <SidebarContent>
                <SidebarGroup class="px-2 py-0">
                    <SidebarGroupLabel>Gestión</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem v-for="item in navItems" :key="item.title">
                            <SidebarMenuButton as-child :is-active="isActive(item.href)">
                                <Link :href="item.href">
                                    <component :is="item.icon" />
                                    <span>{{ item.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>

        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs ?? []" />
            <slot />
        </AppContent>
    </AppShell>
</template>
