<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Package, Pencil, Plus } from 'lucide-vue-next';

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
        href: '/propietario/productos',
    },
];
</script>

<template>
    <Head title="Productos" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <section class="space-y-2">
                    <p class="text-sm font-medium text-muted-foreground">Gestion de productos</p>
                    <h1 class="text-3xl font-semibold tracking-tight">Productos</h1>
                    <p class="max-w-2xl text-muted-foreground">Lista, crea y actualiza los productos disponibles para inventario y compras.</p>
                </section>

                <Button as-child>
                    <Link :href="route('propietario.productos.create')">
                        <Plus class="size-4" />
                        Nuevo producto
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <Package class="size-5 text-muted-foreground" />
                        <CardTitle>Catalogo interno</CardTitle>
                    </div>
                    <CardDescription>{{ productos.length }} productos registrados.</CardDescription>
                </CardHeader>

                <CardContent>
                    <div v-if="productos.length" class="overflow-hidden rounded-md border">
                        <div class="grid grid-cols-[1fr_8rem_6rem] gap-4 border-b bg-muted px-4 py-3 text-sm font-medium text-muted-foreground">
                            <span>Nombre comercial</span>
                            <span class="text-right">Stock</span>
                            <span class="text-right">Acciones</span>
                        </div>

                        <div v-for="producto in productos" :key="producto.id" class="grid grid-cols-[1fr_8rem_6rem] items-center gap-4 border-b px-4 py-3 last:border-b-0">
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ producto.nombre_comercial }}</p>
                                <p class="text-xs text-muted-foreground">Actualizado: {{ new Date(producto.updated_at).toLocaleDateString() }}</p>
                            </div>

                            <div class="text-right font-semibold tabular-nums">
                                {{ producto.stock_actual }}
                            </div>

                            <div class="flex justify-end">
                                <Button as-child variant="ghost" size="icon" aria-label="Editar producto">
                                    <Link :href="route('propietario.productos.edit', producto.id)">
                                        <Pencil class="size-4" />
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex min-h-56 flex-col items-center justify-center gap-3 rounded-md border border-dashed text-center">
                        <Package class="size-10 text-muted-foreground" />
                        <div>
                            <p class="font-medium">No hay productos registrados</p>
                            <p class="text-sm text-muted-foreground">Crea el primer producto para comenzar.</p>
                        </div>
                        <Button as-child variant="outline">
                            <Link :href="route('propietario.productos.create')">Crear producto</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
