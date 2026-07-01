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
import { Loader2, Pencil, Plus, Trash2, Truck } from 'lucide-vue-next';
import { ref } from 'vue';

interface UsuarioResumen {
    id: number;
    ci_nit: string;
    nombre: string;
    email: string;
    direccion: string;
    telefono: string;
}

interface Proveedor {
    id_usuario: number;
    empresa: string;
    usuario: UsuarioResumen;
    updated_at: string;
}

defineProps<{
    proveedores: Proveedor[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Proveedores',
        href: route('propietario.proveedores.index'),
    },
];

const target = ref<Proveedor | null>(null);
const deleteForm = useForm({});
const openDialog = (proveedor: Proveedor) => {
    target.value = proveedor;
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

    deleteForm.delete(route('propietario.proveedores.destroy', target.value.id_usuario), {
        preserveScroll: true,
        onSuccess: () => {
            target.value = null;
        },
    });
};
</script>

<template>
    <Head title="Proveedores" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader eyebrow="Gestion" title="Proveedores" description="Administra empresas proveedoras y sus usuarios de acceso." :icon="Truck" />

            <PageToolbar :total="proveedores.length">
                <Button as-child class="rounded-full">
                    <Link :href="route('propietario.proveedores.create')">
                        <Plus class="size-4" />
                        Nuevo proveedor
                    </Link>
                </Button>
            </PageToolbar>

            <DataTable v-if="proveedores.length">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[1.2fr_1fr_1fr_8rem] items-center gap-4 rounded-t-2xl bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Empresa</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Responsable</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Contacto</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="proveedor in proveedores"
                            :key="proveedor.id_usuario"
                            class="grid grid-cols-[1.2fr_1fr_1fr_8rem] items-center gap-4 px-4 transition hover:bg-muted/40"
                        >
                            <TableCell class="p-3">
                                <p class="truncate font-medium">{{ proveedor.empresa }}</p>
                                <p class="text-xs text-muted-foreground">CI/NIT: {{ proveedor.usuario.ci_nit }}</p>
                            </TableCell>

                            <TableCell class="p-3 text-sm">
                                <p class="truncate">{{ proveedor.usuario.nombre }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ proveedor.usuario.direccion }}</p>
                            </TableCell>

                            <TableCell class="p-3 text-sm">
                                <p class="truncate">{{ proveedor.usuario.email }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ proveedor.usuario.telefono }}</p>
                            </TableCell>

                            <TableCell class="p-3">
                                <div class="flex justify-end gap-1">
                                    <Button as-child variant="ghost" size="icon" aria-label="Editar proveedor">
                                        <Link :href="route('propietario.proveedores.edit', proveedor.id_usuario)">
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>

                                    <Button type="button" variant="ghost" size="icon" aria-label="Eliminar proveedor" @click="openDialog(proveedor)">
                                        <Trash2 class="size-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </DataTable>

            <EmptyState v-else :icon="Truck" title="No hay proveedores registrados" description="Crea el primer proveedor para comenzar.">
                <Button as-child variant="outline" class="rounded-full">
                    <Link :href="route('propietario.proveedores.create')">Crear proveedor</Link>
                </Button>
            </EmptyState>
        </div>

        <Dialog :open="target !== null" @update:open="(v) => (v ? null : closeDialog())">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Eliminar proveedor</DialogTitle>
                    <DialogDescription>
                        Esta accion desactiva al proveedor "{{ target?.empresa }}" y su usuario asociado. Podras reactivarlo desde la base de datos.
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
