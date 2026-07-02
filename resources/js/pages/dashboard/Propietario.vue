<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import StatGrid from '@/components/shared/StatGrid.vue';
import StatusBadge from '@/components/shared/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Boxes, Eye, LayoutGrid, Package, Receipt, ShoppingCart, Truck, Users } from 'lucide-vue-next';
import { computed, type Component } from 'vue';

interface VentaReciente {
    id: number;
    estado_venta: string;
    fecha: string;
    total: string | number;
    cliente_nombre: string | null;
    primer_producto: string | null;
}

const props = defineProps<{
    stats: {
        productos: number;
        clientes: number;
        proveedores: number;
        compras: number;
        ventas: number;
    };
    ventas_recientes: VentaReciente[];
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
    { title: 'Productos', value: props.stats.productos, description: 'Productos registrados', icon: Package, href: route('propietario.productos.index') },
    { title: 'Inventario', value: 0, description: 'Registros de inventario', icon: Boxes, href: route('propietario.inventario.index') },
    { title: 'Compras', value: props.stats.compras, description: 'Solicitudes a proveedores', icon: ShoppingCart, href: route('propietario.compras.index') },
    { title: 'Ventas', value: props.stats.ventas, description: 'Ventas registradas', icon: Receipt, href: route('propietario.ventas.index') },
    { title: 'Clientes', value: props.stats.clientes, description: 'Cuentas cliente activas', icon: Users, href: route('propietario.clientes.index') },
    { title: 'Proveedores', value: props.stats.proveedores, description: 'Aliados registrados', icon: Truck, href: route('propietario.proveedores.index') },
]);

const { money, date } = useFormatters();
</script>

<template>
    <Head title="Dashboard propietario" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader
                eyebrow="Panel administrativo"
                title="Resumen general"
                description="Gestiona productos, inventario, compras, ventas, clientes y proveedores desde un solo lugar."
                :icon="LayoutGrid"
            />

            <StatGrid :stats="statCards" />

            <!-- Ventas recientes -->
            <Card class="border-muted/80 bg-card/95 shadow-sm">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Receipt class="size-5 text-muted-foreground" />
                        <div>
                            <CardTitle>Ventas recientes</CardTitle>
                            <CardDescription>Últimas 5 ventas registradas en el sistema.</CardDescription>
                        </div>
                    </div>
                    <Button as-child variant="outline" size="sm" class="rounded-full">
                        <Link :href="route('propietario.ventas.index')">Ver todas</Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <div v-if="ventas_recientes.length" class="overflow-hidden rounded-2xl border">
                        <Table>
                            <TableHeader>
                                <TableRow class="grid grid-cols-[8rem_1fr_1fr_8rem_5rem] items-center gap-4 bg-muted hover:bg-muted">
                                    <TableHead class="px-4 py-3 text-muted-foreground">Estado</TableHead>
                                    <TableHead class="px-4 py-3 text-muted-foreground">Cliente</TableHead>
                                    <TableHead class="px-4 py-3 text-muted-foreground">Producto</TableHead>
                                    <TableHead class="px-4 py-3 text-right text-muted-foreground">Total</TableHead>
                                    <TableHead class="px-4 py-3 text-right text-muted-foreground">Ver</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="venta in ventas_recientes"
                                    :key="venta.id"
                                    class="grid grid-cols-[8rem_1fr_1fr_8rem_5rem] items-center gap-4 px-4 transition hover:bg-muted/40"
                                >
                                    <TableCell class="p-3">
                                        <StatusBadge :estado="venta.estado_venta" />
                                    </TableCell>
                                    <TableCell class="p-3">
                                        <p class="truncate font-medium">{{ venta.cliente_nombre ?? '—' }}</p>
                                        <p class="text-xs text-muted-foreground">{{ date(venta.fecha) }}</p>
                                    </TableCell>
                                    <TableCell class="p-3">
                                        <p class="truncate text-sm">{{ venta.primer_producto ?? '—' }}</p>
                                    </TableCell>
                                    <TableCell class="p-3 text-right font-semibold tabular-nums">
                                        {{ money(venta.total) }}
                                    </TableCell>
                                    <TableCell class="p-3">
                                        <div class="flex justify-end">
                                            <Button as-child variant="ghost" size="icon" aria-label="Ver venta">
                                                <Link :href="route('propietario.ventas.show', venta.id)">
                                                    <Eye class="size-4" />
                                                </Link>
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div v-else class="rounded-2xl border bg-muted/40 py-10 text-center">
                        <Receipt class="mx-auto mb-2 size-8 text-muted-foreground/50" />
                        <p class="text-sm text-muted-foreground">No hay ventas registradas aún.</p>
                    </div>
                </CardContent>
            </Card>

        </div>
    </AdminLayout>
</template>
