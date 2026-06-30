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

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Compras', href: '/propietario/compras' }];
</script>

<template>
    <Head title="Compras" />
    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <section class="space-y-2">
                    <div class="flex items-center gap-2">
                        <ShoppingCart class="size-7 text-muted-foreground" />
                        <h1 class="text-3xl font-semibold tracking-tight">Compras</h1>
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">Gestion de compras</p>
                    <p class="max-w-2xl text-muted-foreground">Registra solicitudes, contraofertas y compras aprobadas a proveedores.</p>
                </section>
                <Button as-child><Link :href="route('propietario.compras.create')"><Plus class="size-4" /> Nuevo</Link></Button>
            </div>

            <div v-if="compras.length" class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[8rem_1fr_1fr_8rem_7rem_6rem] items-center gap-4 bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Estado</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Proveedor</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Producto</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Cantidad</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Total</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="compra in compras" :key="compra.id" class="grid grid-cols-[8rem_1fr_1fr_8rem_7rem_6rem] items-center gap-4 px-4 hover:bg-transparent">
                            <TableCell class="p-2 text-sm font-medium">{{ compra.estado }}</TableCell>
                            <TableCell class="p-2"><p class="truncate font-medium">{{ compra.proveedor.empresa }}</p><p class="text-xs text-muted-foreground">{{ new Date(compra.fecha_emision).toLocaleDateString() }}</p></TableCell>
                            <TableCell class="p-2"><p class="truncate">{{ compra.detalles[0]?.producto.nombre_comercial }}</p><p class="truncate text-xs text-muted-foreground">{{ compra.observaciones }}</p></TableCell>
                            <TableCell class="p-2 text-right tabular-nums">{{ compra.detalles[0]?.cantidad ?? 0 }}</TableCell>
                            <TableCell class="p-2 text-right font-semibold tabular-nums">{{ Number(compra.monto_total).toFixed(2) }}</TableCell>
                            <TableCell class="p-2">
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
            <div v-else class="flex min-h-56 flex-col items-center justify-center gap-3 rounded-md border border-dashed text-center">
                <ShoppingCart class="size-10 text-muted-foreground" /><div><p class="font-medium">No hay compras registradas</p><p class="text-sm text-muted-foreground">Crea la primera compra para comenzar.</p></div>
                <Button as-child variant="outline"><Link :href="route('propietario.compras.create')">Crear compra</Link></Button>
            </div>
        </div>
    </AdminLayout>
</template>
