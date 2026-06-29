<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Boxes, Package, ShoppingCart, Truck, Users } from 'lucide-vue-next';

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
        href: '/dashboard/propietario',
    },
];
</script>

<template>
    <Head title="Dashboard propietario" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <section class="space-y-2">
                <p class="text-sm font-medium text-muted-foreground">Panel administrativo</p>
                <h1 class="text-3xl font-semibold tracking-tight">Resumen general</h1>
                <p class="max-w-2xl text-muted-foreground">Gestiona productos, inventario, compras, clientes y proveedores desde un solo lugar.</p>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Productos</CardTitle>
                        <Package class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.productos }}</div>
                        <p class="text-xs text-muted-foreground">Productos registrados</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Clientes</CardTitle>
                        <Users class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.clientes }}</div>
                        <p class="text-xs text-muted-foreground">Cuentas cliente activas</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Proveedores</CardTitle>
                        <Truck class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.proveedores }}</div>
                        <p class="text-xs text-muted-foreground">Aliados registrados</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Compras</CardTitle>
                        <ShoppingCart class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.compras }}</div>
                        <p class="text-xs text-muted-foreground">Solicitudes de compra</p>
                    </CardContent>
                </Card>
            </section>

            <Card class="min-h-80">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Boxes class="size-5 text-muted-foreground" />
                        <CardTitle>Operaciones pendientes</CardTitle>
                    </div>
                    <CardDescription>Este espacio queda listo para tablas de inventario, alertas de stock y compras recientes.</CardDescription>
                </CardHeader>
            </Card>
        </div>
    </AdminLayout>
</template>
