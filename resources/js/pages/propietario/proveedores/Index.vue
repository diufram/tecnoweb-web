<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <section class="space-y-2">
                    <div class="flex items-center gap-2">
                        <Truck class="size-7 text-muted-foreground" />
                        <h1 class="text-3xl font-semibold tracking-tight">Proveedores</h1>
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">Gestion de proveedores</p>
                    <p class="max-w-2xl text-muted-foreground">Administra empresas proveedoras y sus usuarios de acceso.</p>
                </section>

                <Button as-child>
                    <Link :href="route('propietario.proveedores.create')">
                        <Plus class="size-4" />
                        Nuevo
                    </Link>
                </Button>
            </div>

            <div v-if="proveedores.length" class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[1.2fr_1fr_1fr_8rem] items-center gap-4 bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Empresa</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Responsable</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Contacto</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="proveedor in proveedores" :key="proveedor.id_usuario" class="grid grid-cols-[1.2fr_1fr_1fr_8rem] items-center gap-4 px-4 hover:bg-transparent">
                            <TableCell class="p-2">
                                <p class="truncate font-medium">{{ proveedor.empresa }}</p>
                                <p class="text-xs text-muted-foreground">CI/NIT: {{ proveedor.usuario.ci_nit }}</p>
                            </TableCell>

                            <TableCell class="p-2 text-sm">
                                <p class="truncate">{{ proveedor.usuario.nombre }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ proveedor.usuario.direccion }}</p>
                            </TableCell>

                            <TableCell class="p-2 text-sm">
                                <p class="truncate">{{ proveedor.usuario.email }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ proveedor.usuario.telefono }}</p>
                            </TableCell>

                            <TableCell class="p-2">
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
            </div>

            <div v-else class="flex min-h-56 flex-col items-center justify-center gap-3 rounded-md border border-dashed text-center">
                <Truck class="size-10 text-muted-foreground" />
                <div>
                    <p class="font-medium">No hay proveedores registrados</p>
                    <p class="text-sm text-muted-foreground">Crea el primer proveedor para comenzar.</p>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('propietario.proveedores.create')">Crear proveedor</Link>
                </Button>
            </div>
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
