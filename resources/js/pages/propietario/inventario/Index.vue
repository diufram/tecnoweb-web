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
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Inventario', href: '/propietario/inventario' }];
</script>

<template>
    <Head title="Inventario" />
    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <section class="space-y-2">
                    <div class="flex items-center gap-2">
                        <Boxes class="size-7 text-muted-foreground" />
                        <h1 class="text-3xl font-semibold tracking-tight">Inventario</h1>
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">Gestion de inventario</p>
                    <p class="max-w-2xl text-muted-foreground">Administra cantidades disponibles por producto y lote.</p>
                </section>
                <Button as-child><Link :href="route('propietario.inventario.create')"><Plus class="size-4" /> Nuevo</Link></Button>
            </div>

            <div v-if="inventarios.length" class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[1fr_9rem_9rem_9rem_8rem_6rem] items-center gap-4 bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Producto</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Lote</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Ingreso</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Vencimiento</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Cantidad</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="item in inventarios" :key="item.id" class="grid grid-cols-[1fr_9rem_9rem_9rem_8rem_6rem] items-center gap-4 px-4 hover:bg-transparent">
                            <TableCell class="p-2"><p class="truncate font-medium">{{ item.producto.nombre_comercial }}</p><p class="text-xs text-muted-foreground">Costo: {{ Number(item.costo_unitario_lote).toFixed(2) }}</p></TableCell>
                            <TableCell class="p-2 truncate text-sm">{{ item.lote.codigo_lote }}</TableCell>
                            <TableCell class="p-2 text-sm">{{ new Date(item.lote.fecha_ingreso).toLocaleDateString() }}</TableCell>
                            <TableCell class="p-2 text-sm">{{ new Date(item.lote.fecha_vencimiento).toLocaleDateString() }}</TableCell>
                            <TableCell class="p-2 text-right font-semibold tabular-nums">{{ item.cantidad_disponible }}</TableCell>
                            <TableCell class="p-2">
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
            <div v-else class="flex min-h-56 flex-col items-center justify-center gap-3 rounded-md border border-dashed text-center">
                <Boxes class="size-10 text-muted-foreground" /><div><p class="font-medium">No hay inventario registrado</p><p class="text-sm text-muted-foreground">Crea el primer registro para comenzar.</p></div>
                <Button as-child variant="outline"><Link :href="route('propietario.inventario.create')">Crear registro</Link></Button>
            </div>
        </div>
    </AdminLayout>
</template>
