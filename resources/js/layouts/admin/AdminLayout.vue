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
import { BarChart3, Boxes, FileText, Handshake, LayoutGrid, Palette, Package, ShoppingCart, Truck, UserRound, Users } from 'lucide-vue-next';
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
const dashboardHref = computed(() => (props.actor === 'propietario' ? '/dashboard/propietario' : '/dashboard/proveedor'));

const navItems = computed<AdminNavItem[]>(() => {
    if (props.actor === 'propietario') {
        return [
            { title: 'Dashboard', href: '/dashboard/propietario', icon: LayoutGrid },
            { title: 'Productos', href: '/propietario/productos', icon: Package },
            { title: 'Inventario', href: '/propietario/inventario', icon: Boxes },
            { title: 'Compras', href: '/propietario/compras', icon: ShoppingCart },
            { title: 'Clientes', href: '/propietario/clientes', icon: Users },
            { title: 'Proveedores', href: '/propietario/proveedores', icon: Truck },
            { title: 'Reportes', href: '#reportes', icon: BarChart3 },
            { title: 'Perfil', href: '/settings/profile', icon: UserRound },
            { title: 'Apariencia', href: '/settings/appearance', icon: Palette },
        ];
    }

    return [
        { title: 'Dashboard', href: '/dashboard/proveedor', icon: LayoutGrid },
        { title: 'Solicitudes', href: '#solicitudes', icon: FileText },
        { title: 'Contraofertas', href: '#contraofertas', icon: Handshake },
        { title: 'Compras', href: '#compras', icon: ShoppingCart },
        { title: 'Historial', href: '#historial', icon: BarChart3 },
        { title: 'Perfil', href: '/settings/profile', icon: UserRound },
        { title: 'Apariencia', href: '/settings/appearance', icon: Palette },
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
                <div class="px-2 pb-2 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                    Panel {{ actorLabel }}
                </div>
            </SidebarHeader>

            <SidebarContent>
                <SidebarGroup class="px-2 py-0">
                    <SidebarGroupLabel>Gestión</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem v-for="item in navItems" :key="item.title">
                            <SidebarMenuButton as-child :is-active="item.href === page.url">
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
