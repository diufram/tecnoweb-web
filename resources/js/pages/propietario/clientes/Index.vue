<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Loader2, Pencil, Plus, Trash2, Users } from 'lucide-vue-next';
import { ref } from 'vue';

interface UsuarioResumen {
    id: number;
    ci_nit: string;
    nombre: string;
    email: string;
    direccion: string;
    telefono: string;
}

interface Cliente {
    id_usuario: number;
    linea_credito: string;
    nit_facturacion: string;
    saldo_actual: string;
    usuario: UsuarioResumen;
    updated_at: string;
}

defineProps<{
    clientes: Cliente[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clientes',
        href: route('propietario.clientes.index'),
    },
];

const target = ref<Cliente | null>(null);
const deleteForm = useForm({});
const openDialog = (cliente: Cliente) => {
    target.value = cliente;
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

    deleteForm.delete(route('propietario.clientes.destroy', target.value.id_usuario), {
        preserveScroll: true,
        onSuccess: () => {
            target.value = null;
        },
    });
};

const money = (value: string | number) =>
    new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(Number(value));
</script>

<template>
    <Head title="Clientes" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <section class="overflow-hidden rounded-3xl border bg-gradient-to-br from-card via-card to-primary/10 p-6 shadow-sm md:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-semibold uppercase tracking-wide text-primary">Gestion</p>
                        <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Clientes</h1>
                        <p class="max-w-2xl text-muted-foreground">Administra las cuentas cliente, sus datos de facturacion y linea de credito.</p>
                    </div>
                    <div class="flex size-16 items-center justify-center rounded-2xl border bg-background/70 shadow-sm">
                        <Users class="size-8 text-primary" />
                    </div>
                </div>
            </section>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-muted-foreground">
                    Total registrados: <span class="font-medium text-foreground">{{ clientes.length }}</span>
                </p>
                <Button as-child class="rounded-full">
                    <Link :href="route('propietario.clientes.create')">
                        <Plus class="size-4" />
                        Nuevo cliente
                    </Link>
                </Button>
            </div>

            <div v-if="clientes.length" class="overflow-hidden rounded-2xl border">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[1.3fr_1fr_8rem_8rem_8rem] items-center gap-4 rounded-t-2xl bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Cliente</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Contacto</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Linea</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Saldo</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="cliente in clientes"
                            :key="cliente.id_usuario"
                            class="grid grid-cols-[1.3fr_1fr_8rem_8rem_8rem] items-center gap-4 px-4 transition hover:bg-muted/40"
                        >
                            <TableCell class="p-3">
                                <p class="truncate font-medium">{{ cliente.usuario.nombre }}</p>
                                <p class="text-xs text-muted-foreground">
                                    CI/NIT: {{ cliente.usuario.ci_nit }} · Facturacion: {{ cliente.nit_facturacion }}
                                </p>
                            </TableCell>

                            <TableCell class="p-3 text-sm">
                                <p class="truncate">{{ cliente.usuario.email }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ cliente.usuario.telefono }}</p>
                            </TableCell>

                            <TableCell class="p-3 text-right">
                                <span class="inline-flex items-center rounded-full border bg-background px-3 py-1 text-sm font-semibold tabular-nums">
                                    {{ money(cliente.linea_credito) }}
                                </span>
                            </TableCell>

                            <TableCell class="p-3 text-right">
                                <span class="inline-flex items-center rounded-full border bg-background px-3 py-1 text-sm font-semibold tabular-nums">
                                    {{ money(cliente.saldo_actual) }}
                                </span>
                            </TableCell>

                            <TableCell class="p-3">
                                <div class="flex justify-end gap-1">
                                    <Button as-child variant="ghost" size="icon" aria-label="Editar cliente">
                                        <Link :href="route('propietario.clientes.edit', cliente.id_usuario)">
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>

                                    <Button type="button" variant="ghost" size="icon" aria-label="Eliminar cliente" @click="openDialog(cliente)">
                                        <Trash2 class="size-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-else class="flex min-h-56 flex-col items-center justify-center gap-3 rounded-3xl border border-dashed bg-card/40 text-center">
                <div class="flex size-16 items-center justify-center rounded-2xl border bg-background/70">
                    <Users class="size-8 text-muted-foreground" />
                </div>
                <div>
                    <p class="font-medium">No hay clientes registrados</p>
                    <p class="text-sm text-muted-foreground">Crea el primer cliente para comenzar.</p>
                </div>
                <Button as-child variant="outline" class="rounded-full">
                    <Link :href="route('propietario.clientes.create')">Crear cliente</Link>
                </Button>
            </div>
        </div>

        <Dialog :open="target !== null" @update:open="(v) => (v ? null : closeDialog())">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Eliminar cliente</DialogTitle>
                    <DialogDescription>
                        Esta accion desactiva al cliente "{{ target?.usuario.nombre }}" y su usuario asociado. Podras reactivarlo desde la base de
                        datos.
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
