<script setup lang="ts">
import DataTable from '@/components/shared/DataTable.vue';
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import PageToolbar from '@/components/shared/PageToolbar.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Loader2, Package, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Producto {
    id: number;
    nombre_comercial: string;
    stock_actual: number;
    created_at: string;
    updated_at: string;
}

defineProps<{
    productos: Producto[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Productos',
        href: route('propietario.productos.index'),
    },
];

const target = ref<Producto | null>(null);
const deleteForm = useForm({});
const openDialog = (producto: Producto) => {
    target.value = producto;
};
const closeDialog = () => {
    if (deleteForm.processing) {
        return;
    }

    target.value = null;
};
const confirmDelete = () => {
    if (!target.value) {
        return;
    }

    deleteForm.delete(route('propietario.productos.destroy', target.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            target.value = null;
        },
    });
};
</script>

<template>
    <Head title="Productos" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader
                eyebrow="Gestion"
                title="Productos"
                description="Lista, crea y actualiza los productos disponibles para inventario y compras."
                :icon="Package"
            />

            <PageToolbar :total="productos.length">
                <Button as-child class="rounded-full">
                    <Link :href="route('propietario.productos.create')">
                        <Plus class="size-4" />
                        Nuevo producto
                    </Link>
                </Button>
            </PageToolbar>

            <DataTable v-if="productos.length">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[1fr_8rem_8rem] items-center gap-4 rounded-t-2xl bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Nombre comercial</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Stock</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="producto in productos"
                            :key="producto.id"
                            class="grid grid-cols-[1fr_8rem_8rem] items-center gap-4 px-4 transition hover:bg-muted/40"
                        >
                            <TableCell class="p-3">
                                <p class="truncate font-medium">{{ producto.nombre_comercial }}</p>
                                <p class="text-xs text-muted-foreground">Actualizado: {{ new Date(producto.updated_at).toLocaleDateString() }}</p>
                            </TableCell>

                            <TableCell class="p-3 text-right">
                                <span class="inline-flex items-center rounded-full border bg-background px-3 py-1 text-sm font-semibold tabular-nums">
                                    {{ producto.stock_actual }}
                                </span>
                            </TableCell>

                            <TableCell class="p-3">
                                <div class="flex justify-end gap-1">
                                    <Button as-child variant="ghost" size="icon" aria-label="Editar producto">
                                        <Link :href="route('propietario.productos.edit', producto.id)">
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>

                                    <Button type="button" variant="ghost" size="icon" aria-label="Eliminar producto" @click="openDialog(producto)">
                                        <Trash2 class="size-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </DataTable>

            <EmptyState v-else :icon="Package" title="No hay productos registrados" description="Crea el primer producto para comenzar.">
                <Button as-child variant="outline" class="rounded-full">
                    <Link :href="route('propietario.productos.create')">Crear producto</Link>
                </Button>
            </EmptyState>
        </div>

        <Dialog :open="target !== null" @update:open="(v) => (v ? null : closeDialog())">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Eliminar producto</DialogTitle>
                    <DialogDescription>
                        Esta accion desactiva el producto "{{ target?.nombre_comercial }}". Podras reactivarlo manualmente desde la base de datos.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="outline" :disabled="deleteForm.processing" @click="closeDialog">Cancelar</Button>
                    <Button type="button" variant="destructive" :disabled="deleteForm.processing" @click="confirmDelete">
                        <Loader2 v-if="deleteForm.processing" class="size-4 animate-spin" />
                        {{ deleteForm.processing ? 'Eliminando...' : 'Eliminar' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
