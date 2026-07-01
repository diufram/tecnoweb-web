<script setup lang="ts">
import DataTable from '@/components/shared/DataTable.vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import PageToolbar from '@/components/shared/PageToolbar.vue';
import StatusBadge from '@/components/shared/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, ShoppingCart } from 'lucide-vue-next';

interface Compra {
    id: number;
    estado: string;
    fecha_emision: string;
    monto_total: string;
    total_solicitado: string | number;
    total_contraoferta: string | number | null;
    observaciones: string;
    proveedor: { empresa: string; usuario: { nombre: string } };
    detalles: { cantidad: number; precio_unitario: string; subtotal: string; producto: { nombre_comercial: string } }[];
}

defineProps<{ compras: Compra[] }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Compras', href: route('propietario.compras.index') }];

const { money, date } = useFormatters();
</script>

<template>
    <Head title="Compras" />
    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader
                eyebrow="Gestion"
                title="Compras"
                description="Registra solicitudes, contraofertas y compras aprobadas a proveedores."
                :icon="ShoppingCart"
            />

            <PageToolbar :total="compras.length">
                <Button as-child class="rounded-full">
                    <Link :href="route('propietario.compras.create')"><Plus class="size-4" /> Nueva compra</Link>
                </Button>
            </PageToolbar>

            <DataTable v-if="compras.length">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[8rem_1fr_1fr_8rem_7rem_6rem] items-center gap-4 rounded-t-2xl bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Estado</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Proveedor</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Producto</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Items</TableHead>
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
                                <StatusBadge :estado="compra.estado" />
                            </TableCell>
                            <TableCell class="p-3">
                                <p class="truncate font-medium">{{ compra.proveedor.empresa }}</p>
                                <p class="text-xs text-muted-foreground">{{ date(compra.fecha_emision) }}</p>
                            </TableCell>
                            <TableCell class="p-3">
                                <p class="truncate">{{ compra.detalles[0]?.producto.nombre_comercial }}</p>
                                <p v-if="compra.detalles.length > 1" class="text-xs font-medium text-muted-foreground">
                                    + {{ compra.detalles.length - 1 }} productos mas
                                </p>
                                <p class="truncate text-xs text-muted-foreground">{{ compra.observaciones }}</p>
                            </TableCell>
                            <TableCell class="p-3 text-right tabular-nums">{{ compra.detalles.length }}</TableCell>
                            <TableCell class="p-3 text-right font-semibold tabular-nums">{{ money(compra.monto_total) }}</TableCell>
                            <TableCell class="p-3">
                                <div class="flex justify-end gap-1">
                                    <Button as-child variant="ghost" size="icon" aria-label="Ver compra">
                                        <Link :href="route('propietario.compras.show', compra.id)">
                                            <Eye class="size-4" />
                                        </Link>
                                    </Button>
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
            </DataTable>

            <EmptyState v-else :icon="ShoppingCart" title="No hay compras registradas" description="Crea la primera compra para comenzar.">
                <Button as-child variant="outline" class="rounded-full">
                    <Link :href="route('propietario.compras.create')">Crear compra</Link>
                </Button>
            </EmptyState>
        </div>
    </AdminLayout>
</template>
