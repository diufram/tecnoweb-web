<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus, Truck } from 'lucide-vue-next';

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
        href: '/propietario/proveedores',
    },
];
</script>

<template>
    <Head title="Proveedores" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <section class="space-y-2">
                    <p class="text-sm font-medium text-muted-foreground">Gestion de proveedores</p>
                    <h1 class="text-3xl font-semibold tracking-tight">Proveedores</h1>
                    <p class="max-w-2xl text-muted-foreground">Administra empresas proveedoras y sus usuarios de acceso.</p>
                </section>

                <Button as-child>
                    <Link :href="route('propietario.proveedores.create')">
                        <Plus class="size-4" />
                        Nuevo proveedor
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Truck class="size-5 text-muted-foreground" />
                        <CardTitle>Empresas proveedoras</CardTitle>
                    </div>
                    <CardDescription>{{ proveedores.length }} proveedores registrados.</CardDescription>
                </CardHeader>

                <CardContent>
                    <div v-if="proveedores.length" class="overflow-hidden rounded-md border">
                        <div class="grid grid-cols-[1.2fr_1fr_1fr_6rem] gap-4 border-b bg-muted px-4 py-3 text-sm font-medium text-muted-foreground">
                            <span>Empresa</span>
                            <span>Responsable</span>
                            <span>Contacto</span>
                            <span class="text-right">Acciones</span>
                        </div>

                        <div v-for="proveedor in proveedores" :key="proveedor.id_usuario" class="grid grid-cols-[1.2fr_1fr_1fr_6rem] items-center gap-4 border-b px-4 py-3 last:border-b-0">
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ proveedor.empresa }}</p>
                                <p class="text-xs text-muted-foreground">CI/NIT: {{ proveedor.usuario.ci_nit }}</p>
                            </div>

                            <div class="min-w-0 text-sm">
                                <p class="truncate">{{ proveedor.usuario.nombre }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ proveedor.usuario.direccion }}</p>
                            </div>

                            <div class="min-w-0 text-sm">
                                <p class="truncate">{{ proveedor.usuario.email }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ proveedor.usuario.telefono }}</p>
                            </div>

                            <div class="flex justify-end">
                                <Button as-child variant="ghost" size="icon" aria-label="Editar proveedor">
                                    <Link :href="route('propietario.proveedores.edit', proveedor.id_usuario)">
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                            </div>
                        </div>
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
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
