<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus, ShoppingCart } from 'lucide-vue-next';

interface Compra {
    id: number;
    estado: string;
    fecha_emision: string;
    monto_total: string;
    observaciones: string;
    proveedor: { empresa: string; usuario: { nombre: string } };
    detalles: { cantidad: number; precio_unitario: string; subtotal: string; producto: { nombre_comercial: string } }[];
}

defineProps<{ compras: Compra[] }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Compras', href: route('propietario.compras.index') }];

const money = (value: string | number) =>
    new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(Number(value));

const date = (value: string) => new Date(value).toLocaleDateString('es-BO');

const statusClass = (estado: string) => {
    switch (estado) {
        case 'APROBADO':
            return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
        case 'RECHAZADO':
            return 'border-destructive/30 bg-destructive/10 text-destructive';
        case 'CONTRA_OFERTA':
            return 'border-blue-500/30 bg-blue-500/10 text-blue-600 dark:text-blue-400';
        default:
            return 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400';
    }
};
</script>

<template>
    <Head title="Compras" />
    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <section class="overflow-hidden rounded-3xl border bg-gradient-to-br from-card via-card to-primary/10 p-6 shadow-sm md:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-semibold uppercase tracking-wide text-primary">Gestion</p>
                        <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Compras</h1>
                        <p class="max-w-2xl text-muted-foreground">Registra solicitudes, contraofertas y compras aprobadas a proveedores.</p>
                    </div>
                    <div class="flex size-16 items-center justify-center rounded-2xl border bg-background/70 shadow-sm">
                        <ShoppingCart class="size-8 text-primary" />
                    </div>
                </div>
            </section>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-muted-foreground">
                    Total registradas: <span class="font-medium text-foreground">{{ compras.length }}</span>
                </p>
                <Button as-child class="rounded-full">
                    <Link :href="route('propietario.compras.create')"><Plus class="size-4" /> Nueva compra</Link>
                </Button>
            </div>

            <div v-if="compras.length" class="overflow-hidden rounded-2xl border">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[8rem_1fr_1fr_8rem_7rem_6rem] items-center gap-4 rounded-t-2xl bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Estado</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Proveedor</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Producto</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Cantidad</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Total</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="compra in compras"
                            :key="compra.id"
                            class="grid grid-cols-[8rem_1fr_1fr_8rem_7rem_6rem] items-center gap-4 px-4 transition hover:bg-muted/40"
                        >
                            <TableCell class="p-3">
                                <span
                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold"
                                    :class="statusClass(compra.estado)"
                                >
                                    {{ compra.estado }}
                                </span>
                            </TableCell>
                            <TableCell class="p-3">
                                <p class="truncate font-medium">{{ compra.proveedor.empresa }}</p>
                                <p class="text-xs text-muted-foreground">{{ date(compra.fecha_emision) }}</p>
                            </TableCell>
                            <TableCell class="p-3">
                                <p class="truncate">{{ compra.detalles[0]?.producto.nombre_comercial }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ compra.observaciones }}</p>
                            </TableCell>
                            <TableCell class="p-3 text-right tabular-nums">{{ compra.detalles[0]?.cantidad ?? 0 }}</TableCell>
                            <TableCell class="p-3 text-right font-semibold tabular-nums">{{ money(compra.monto_total) }}</TableCell>
                            <TableCell class="p-3">
                                <div class="flex justify-end">
                                    <Button as-child variant="ghost" size="icon" aria-label="Editar compra">
                                        <Link :href="route('propietario.compras.edit', compra.id)">
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div v-else class="flex min-h-56 flex-col items-center justify-center gap-3 rounded-3xl border border-dashed bg-card/40 text-center">
                <div class="flex size-16 items-center justify-center rounded-2xl border bg-background/70">
                    <ShoppingCart class="size-8 text-muted-foreground" />
                </div>
                <div>
                    <p class="font-medium">No hay compras registradas</p>
                    <p class="text-sm text-muted-foreground">Crea la primera compra para comenzar.</p>
                </div>
                <Button as-child variant="outline" class="rounded-full">
                    <Link :href="route('propietario.compras.create')">Crear compra</Link>
                </Button>
            </div>
        </div>
    </AdminLayout>
</template>
