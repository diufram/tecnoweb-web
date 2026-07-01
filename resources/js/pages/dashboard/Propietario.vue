<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
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

interface StatCard {
    title: string;
    value: number;
    description: string;
    icon: Component;
    href: string;
}

const statCards = computed<StatCard[]>(() => [
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
            <section class="overflow-hidden rounded-3xl border bg-gradient-to-br from-card via-card to-primary/10 p-6 shadow-sm md:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-semibold uppercase tracking-wide text-primary">Panel administrativo</p>
                        <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Resumen general</h1>
                        <p class="max-w-2xl text-muted-foreground">
                            Gestiona productos, inventario, compras, clientes y proveedores desde un solo lugar.
                        </p>
                    </div>
                    <div class="flex size-16 items-center justify-center rounded-2xl border bg-background/70 shadow-sm">
                        <LayoutGrid class="size-8 text-primary" />
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Card
                    v-for="(stat, index) in statCards"
                    :key="stat.title"
                    class="border-muted/80 bg-card/95 shadow-sm transition hover:border-primary/40"
                >
                    <CardHeader class="gap-3 pb-2">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <CardTitle class="text-sm font-medium text-muted-foreground">{{ stat.title }}</CardTitle>
                                <p class="text-3xl font-semibold tracking-tight">
                                    {{
                                        index === 0 ? stats.productos : index === 1 ? stats.clientes : index === 2 ? stats.proveedores : stats.compras
                                    }}
                                </p>
                            </div>
                            <div class="flex size-12 items-center justify-center rounded-2xl border bg-primary/10 text-primary">
                                <component :is="stat.icon" class="size-5" />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Button as-child variant="link" class="h-auto px-0">
                            <Link :href="stat.href">Ir al modulo</Link>
                        </Button>
                        <CardDescription>{{ stat.description }}</CardDescription>
                    </CardContent>
                </Card>
            </section>

            <Card class="border-muted/80 bg-card/95 shadow-sm">
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
