<script setup lang="ts">
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import StatGrid from '@/components/shared/StatGrid.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Boxes, LayoutGrid, Package, ShoppingCart, Truck, Users } from 'lucide-vue-next';
import { computed, type Component } from 'vue';

defineProps<{
    stats: {
        productos: number;
        clientes: number;
        proveedores: number;
        compras: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard propietario',
        href: route('dashboard.propietario'),
    },
];

interface StatItem {
    title: string;
    value: number;
    description: string;
    icon: Component;
    href: string;
}

const statCards = computed<StatItem[]>(() => [
    { title: 'Productos', value: 0, description: 'Productos registrados', icon: Package, href: route('propietario.productos.index') },
    { title: 'Inventario', value: 0, description: 'Registros de inventario', icon: Boxes, href: route('propietario.inventario.index') },
    { title: 'Compras', value: 0, description: 'Solicitudes a proveedores', icon: ShoppingCart, href: route('propietario.compras.index') },
    { title: 'Clientes', value: 0, description: 'Cuentas cliente activas', icon: Users, href: route('propietario.clientes.index') },
    { title: 'Proveedores', value: 0, description: 'Aliados registrados', icon: Truck, href: route('propietario.proveedores.index') },
]);
</script>

<template>
    <Head title="Dashboard propietario" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader
                eyebrow="Panel administrativo"
                title="Resumen general"
                description="Gestiona productos, inventario, compras, clientes y proveedores desde un solo lugar."
                :icon="LayoutGrid"
            />

            <StatGrid :stats="statCards" />

            <Card class="border-muted/80 bg-card/95 shadow-sm">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Boxes class="size-5 text-muted-foreground" />
                        <CardTitle>Operaciones pendientes</CardTitle>
                    </div>
                    <CardDescription>Este espacio queda listo para tablas de inventario, alertas de stock y compras recientes.</CardDescription>
                </CardHeader>
                <CardContent>
                    <EmptyState
                        :icon="ShoppingCart"
                        title="Sin pendientes por ahora"
                        description="Las solicitudes de compra, contraofertas y alertas apareceran aqui."
                    />
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
