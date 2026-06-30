<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus, Users } from 'lucide-vue-next';

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
        href: '/propietario/clientes',
    },
];
</script>

<template>
    <Head title="Clientes" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <section class="space-y-2">
                    <p class="text-sm font-medium text-muted-foreground">Gestion de clientes</p>
                    <h1 class="text-3xl font-semibold tracking-tight">Clientes</h1>
                    <p class="max-w-2xl text-muted-foreground">Administra las cuentas cliente, sus datos de facturacion y linea de credito.</p>
                </section>

                <Button as-child>
                    <Link :href="route('propietario.clientes.create')">
                        <Plus class="size-4" />
                        Nuevo cliente
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Users class="size-5 text-muted-foreground" />
                        <CardTitle>Cuentas cliente</CardTitle>
                    </div>
                    <CardDescription>{{ clientes.length }} clientes registrados.</CardDescription>
                </CardHeader>

                <CardContent>
                    <div v-if="clientes.length" class="overflow-hidden rounded-md border">
                        <div class="grid grid-cols-[1.3fr_1fr_8rem_8rem_6rem] gap-4 border-b bg-muted px-4 py-3 text-sm font-medium text-muted-foreground">
                            <span>Cliente</span>
                            <span>Contacto</span>
                            <span class="text-right">Linea</span>
                            <span class="text-right">Saldo</span>
                            <span class="text-right">Acciones</span>
                        </div>

                        <div v-for="cliente in clientes" :key="cliente.id_usuario" class="grid grid-cols-[1.3fr_1fr_8rem_8rem_6rem] items-center gap-4 border-b px-4 py-3 last:border-b-0">
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ cliente.usuario.nombre }}</p>
                                <p class="text-xs text-muted-foreground">CI/NIT: {{ cliente.usuario.ci_nit }} · Facturacion: {{ cliente.nit_facturacion }}</p>
                            </div>

                            <div class="min-w-0 text-sm">
                                <p class="truncate">{{ cliente.usuario.email }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ cliente.usuario.telefono }}</p>
                            </div>

                            <div class="text-right font-semibold tabular-nums">{{ Number(cliente.linea_credito).toFixed(2) }}</div>
                            <div class="text-right font-semibold tabular-nums">{{ Number(cliente.saldo_actual).toFixed(2) }}</div>

                            <div class="flex justify-end">
                                <Button as-child variant="ghost" size="icon" aria-label="Editar cliente">
                                    <Link :href="route('propietario.clientes.edit', cliente.id_usuario)">
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex min-h-56 flex-col items-center justify-center gap-3 rounded-md border border-dashed text-center">
                        <Users class="size-10 text-muted-foreground" />
                        <div>
                            <p class="font-medium">No hay clientes registrados</p>
                            <p class="text-sm text-muted-foreground">Crea el primer cliente para comenzar.</p>
                        </div>
                        <Button as-child variant="outline">
                            <Link :href="route('propietario.clientes.create')">Crear cliente</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
