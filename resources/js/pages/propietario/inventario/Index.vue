<script setup lang="ts">
import DataTable from '@/components/shared/DataTable.vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import PageToolbar from '@/components/shared/PageToolbar.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Boxes, Pencil, Plus } from 'lucide-vue-next';

interface Inventario {
    id: number;
    cantidad_disponible: number;
    costo_unitario_lote: string;
    producto: { nombre_comercial: string };
    lote: { codigo_lote: string; fecha_ingreso: string; fecha_vencimiento: string };
}

defineProps<{ inventarios: Inventario[] }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Inventario', href: route('propietario.inventario.index') }];

const { money, date } = useFormatters();
</script>

<template>
    <Head title="Inventario" />
    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader eyebrow="Gestion" title="Inventario" description="Administra cantidades disponibles por producto y lote." :icon="Boxes" />

            <PageToolbar :total="inventarios.length">
                <Button as-child class="rounded-full">
                    <Link :href="route('propietario.inventario.create')"><Plus class="size-4" /> Nuevo registro</Link>
                </Button>
            </PageToolbar>

            <DataTable v-if="inventarios.length">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[1fr_9rem_9rem_9rem_8rem_6rem] items-center gap-4 rounded-t-2xl bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Producto</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Lote</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Ingreso</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Vencimiento</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Cantidad</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="item in inventarios"
                            :key="item.id"
                            class="grid grid-cols-[1fr_9rem_9rem_9rem_8rem_6rem] items-center gap-4 px-4 transition hover:bg-muted/40"
                        >
                            <TableCell class="p-3">
                                <p class="truncate font-medium">{{ item.producto.nombre_comercial }}</p>
                                <p class="text-xs text-muted-foreground">Costo: {{ money(item.costo_unitario_lote) }}</p>
                            </TableCell>
                            <TableCell class="p-3">
                                <span class="inline-flex items-center rounded-full border bg-background px-3 py-1 text-xs font-medium">
                                    {{ item.lote.codigo_lote }}
                                </span>
                            </TableCell>
                            <TableCell class="p-3 text-sm tabular-nums">{{ date(item.lote.fecha_ingreso) }}</TableCell>
                            <TableCell class="p-3 text-sm tabular-nums">{{ date(item.lote.fecha_vencimiento) }}</TableCell>
                            <TableCell class="p-3 text-right">
                                <span class="inline-flex items-center rounded-full border bg-background px-3 py-1 text-sm font-semibold tabular-nums">
                                    {{ item.cantidad_disponible }}
                                </span>
                            </TableCell>
                            <TableCell class="p-3">
                                <div class="flex justify-end">
                                    <Button as-child variant="ghost" size="icon" aria-label="Editar inventario">
                                        <Link :href="route('propietario.inventario.edit', item.id)">
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </DataTable>

            <EmptyState v-else :icon="Boxes" title="No hay inventario registrado" description="Crea el primer registro para comenzar.">
                <Button as-child variant="outline" class="rounded-full">
                    <Link :href="route('propietario.inventario.create')">Crear registro</Link>
                </Button>
            </EmptyState>
        </div>
    </AdminLayout>
</template>
