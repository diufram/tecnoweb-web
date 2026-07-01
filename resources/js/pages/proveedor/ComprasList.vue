<script setup lang="ts">
import DataTable from '@/components/shared/DataTable.vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import StatusBadge from '@/components/shared/StatusBadge.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { Link } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';

interface CompraDetalle {
    id: number;
    cantidad: number;
    precio_unitario: string | number;
    subtotal: string | number;
    producto: {
        id: number;
        nombre_comercial: string;
    };
}

interface Compra {
    id: number;
    estado: string;
    fecha_emision: string;
    monto_total: string | number;
    observaciones: string;
    proveedor: {
        empresa: string | null;
        usuario: string | null;
    };
    detalles: CompraDetalle[];
}

defineProps<{
    compras: Compra[];
    emptyTitle?: string;
    emptyDescription?: string;
}>();

const { money, date } = useFormatters();

const detalleResumen = (compra: Compra) => compra.detalles[0];
</script>

<template>
    <DataTable v-if="compras.length">
        <Table>
            <TableHeader>
                <TableRow class="grid grid-cols-[6rem_1fr_1.2fr_8rem_8rem_6rem] items-center gap-4 rounded-t-2xl bg-muted hover:bg-muted">
                    <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Estado</TableHead>
                    <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Producto</TableHead>
                    <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Observaciones</TableHead>
                    <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Fecha</TableHead>
                    <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Total</TableHead>
                    <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Detalle</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="compra in compras"
                    :key="compra.id"
                    class="grid grid-cols-[6rem_1fr_1.2fr_8rem_8rem_6rem] items-center gap-4 px-4 transition hover:bg-muted/40"
                >
                    <TableCell class="p-3">
                        <StatusBadge :estado="compra.estado" />
                    </TableCell>

                    <TableCell class="p-3">
                        <p class="truncate font-medium">{{ detalleResumen(compra)?.producto.nombre_comercial }}</p>
                        <p v-if="compra.detalles.length > 1" class="truncate text-xs font-medium text-muted-foreground">
                            + {{ compra.detalles.length - 1 }} productos mas
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{ compra.detalles.length }} item(s) · Total {{ money(compra.monto_total) }}
                        </p>
                    </TableCell>

                    <TableCell class="p-3">
                        <p class="line-clamp-2 text-sm text-muted-foreground">{{ compra.observaciones }}</p>
                    </TableCell>

                    <TableCell class="p-3 text-sm tabular-nums">{{ date(compra.fecha_emision) }}</TableCell>

                    <TableCell class="p-3 text-right font-semibold tabular-nums">
                        {{ money(compra.monto_total) }}
                    </TableCell>

                    <TableCell class="p-3 text-right">
                        <Link
                            :href="route('proveedor.show', compra.id)"
                            class="inline-flex items-center gap-1 rounded-full border bg-background px-3 py-1 text-xs font-semibold text-primary hover:border-primary/40"
                        >
                            Abrir
                        </Link>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </DataTable>

    <EmptyState
        v-else
        :icon="CheckCircle2"
        :title="emptyTitle ?? 'No hay registros en esta seccion'"
        :description="emptyDescription ?? 'Cuando exista actividad aparecera aqui.'"
    >
        <slot name="empty-action" />
    </EmptyState>
</template>
