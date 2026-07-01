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

interface Lote {
    id: number;
    codigo_lote: string;
    fecha_ingreso: string;
    fecha_vencimiento: string;
    inventarios_count: number;
}

defineProps<{ lotes: Lote[] }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Lotes', href: route('propietario.lotes.index') }];
const { date } = useFormatters();
</script>

<template>
    <Head title="Lotes" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader eyebrow="Gestion" title="Lotes" description="Crea y administra los lotes que luego se asignan al inventario." :icon="Boxes" />

            <PageToolbar :total="lotes.length">
                <Button as-child class="rounded-full">
                    <Link :href="route('propietario.lotes.create')"><Plus class="size-4" /> Nuevo lote</Link>
                </Button>
            </PageToolbar>

            <DataTable v-if="lotes.length">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[1fr_10rem_10rem_8rem_6rem] items-center gap-4 rounded-t-2xl bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Codigo</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Ingreso</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Vencimiento</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Inventario</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="lote in lotes"
                            :key="lote.id"
                            class="grid grid-cols-[1fr_10rem_10rem_8rem_6rem] items-center gap-4 px-4 transition hover:bg-muted/40"
                        >
                            <TableCell class="p-3">
                                <span class="inline-flex items-center rounded-full border bg-background px-3 py-1 text-sm font-medium">
                                    {{ lote.codigo_lote }}
                                </span>
                            </TableCell>
                            <TableCell class="p-3 text-sm tabular-nums">{{ date(lote.fecha_ingreso) }}</TableCell>
                            <TableCell class="p-3 text-sm tabular-nums">{{ date(lote.fecha_vencimiento) }}</TableCell>
                            <TableCell class="p-3 text-right">
                                <span class="inline-flex items-center rounded-full border bg-background px-3 py-1 text-sm font-semibold tabular-nums">
                                    {{ lote.inventarios_count }}
                                </span>
                            </TableCell>
                            <TableCell class="p-3">
                                <div class="flex justify-end">
                                    <Button as-child variant="ghost" size="icon" aria-label="Editar lote">
                                        <Link :href="route('propietario.lotes.edit', lote.id)">
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </DataTable>

            <EmptyState v-else :icon="Boxes" title="No hay lotes registrados" description="Crea lotes para poder asignarlos al inventario.">
                <Button as-child variant="outline" class="rounded-full">
                    <Link :href="route('propietario.lotes.create')">Crear lote</Link>
                </Button>
            </EmptyState>
        </div>
    </AdminLayout>
</template>
