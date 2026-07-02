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
import { Eye, Receipt } from 'lucide-vue-next';

interface VentaRow {
    id: number;
    estado_venta: string;
    fecha: string;
    total: string | number;
    cliente: {
        usuario: { nombre: string | null; email: string | null };
    };
    detalles: { producto: { nombre_comercial: string | null } }[];
}

defineProps<{ ventas: VentaRow[] }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Ventas', href: route('propietario.ventas.index') }];

const { money, date } = useFormatters();
</script>

<template>
    <Head title="Ventas" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader
                eyebrow="Gestión"
                title="Ventas"
                description="Consulta y visualiza todas las ventas registradas en el sistema."
                :icon="Receipt"
            />

            <PageToolbar :total="ventas.length" label="Total ventas" />

            <DataTable v-if="ventas.length">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[8rem_1fr_1fr_8rem_7rem_5rem] items-center gap-4 rounded-t-2xl bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Estado</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Cliente</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Productos</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Ítems</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Total</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Ver</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="venta in ventas"
                            :key="venta.id"
                            class="grid grid-cols-[8rem_1fr_1fr_8rem_7rem_5rem] items-center gap-4 px-4 transition hover:bg-muted/40"
                        >
                            <TableCell class="p-3">
                                <StatusBadge :estado="venta.estado_venta" />
                            </TableCell>

                            <TableCell class="p-3">
                                <p class="truncate font-medium">{{ venta.cliente.usuario.nombre ?? '—' }}</p>
                                <p class="text-xs text-muted-foreground">{{ date(venta.fecha) }}</p>
                            </TableCell>

                            <TableCell class="p-3">
                                <p class="truncate text-sm">{{ venta.detalles[0]?.producto.nombre_comercial ?? '—' }}</p>
                                <p v-if="venta.detalles.length > 1" class="text-xs font-medium text-muted-foreground">
                                    + {{ venta.detalles.length - 1 }} productos más
                                </p>
                            </TableCell>

                            <TableCell class="p-3 text-right tabular-nums">{{ venta.detalles.length }}</TableCell>

                            <TableCell class="p-3 text-right font-semibold tabular-nums">{{ money(venta.total) }}</TableCell>

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
            </DataTable>

            <EmptyState
                v-else
                :icon="Receipt"
                title="No hay ventas registradas"
                description="Las ventas generadas desde el carrito del cliente aparecerán aquí."
            />
        </div>
    </AdminLayout>
</template>
