<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
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

const money = (value: string | number) =>
    new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(Number(value));

const date = (value: string) => new Date(value).toLocaleDateString('es-BO');
</script>

<template>
    <Head title="Inventario" />
    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <section class="overflow-hidden rounded-3xl border bg-gradient-to-br from-card via-card to-primary/10 p-6 shadow-sm md:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-semibold uppercase tracking-wide text-primary">Gestion</p>
                        <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Inventario</h1>
                        <p class="max-w-2xl text-muted-foreground">Administra cantidades disponibles por producto y lote.</p>
                    </div>
                    <div class="flex size-16 items-center justify-center rounded-2xl border bg-background/70 shadow-sm">
                        <Boxes class="size-8 text-primary" />
                    </div>
                </div>
            </section>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-muted-foreground">
                    Total registros: <span class="font-medium text-foreground">{{ inventarios.length }}</span>
                </p>
                <Button as-child class="rounded-full">
                    <Link :href="route('propietario.inventario.create')"><Plus class="size-4" /> Nuevo registro</Link>
                </Button>
            </div>

            <div v-if="inventarios.length" class="overflow-hidden rounded-2xl border">
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
            </div>
            <div v-else class="flex min-h-56 flex-col items-center justify-center gap-3 rounded-3xl border border-dashed bg-card/40 text-center">
                <div class="flex size-16 items-center justify-center rounded-2xl border bg-background/70">
                    <Boxes class="size-8 text-muted-foreground" />
                </div>
                <div>
                    <p class="font-medium">No hay inventario registrado</p>
                    <p class="text-sm text-muted-foreground">Crea el primer registro para comenzar.</p>
                </div>
                <Button as-child variant="outline" class="rounded-full">
                    <Link :href="route('propietario.inventario.create')">Crear registro</Link>
                </Button>
            </div>
        </div>
    </AdminLayout>
</template>
