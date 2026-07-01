<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { CheckCircle2, FileText, Handshake, ShoppingCart } from 'lucide-vue-next';

defineProps<{
    stats: {
        solicitudes: number;
        aprobadas: number;
        contraOfertas: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard proveedor',
        href: route('dashboard.proveedor'),
    },
];
</script>

<template>
    <Head title="Dashboard proveedor" />

    <AdminLayout actor="proveedor" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <section class="space-y-2">
                <p class="text-sm font-medium text-muted-foreground">Panel proveedor</p>
                <h1 class="text-3xl font-semibold tracking-tight">Solicitudes y contraofertas</h1>
                <p class="max-w-2xl text-muted-foreground">Revisa solicitudes de compra, responde contraofertas y consulta tu historial comercial.</p>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Solicitudes</CardTitle>
                        <FileText class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.solicitudes }}</div>
                        <p class="text-xs text-muted-foreground">Compras asociadas a tu proveedor</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Aprobadas</CardTitle>
                        <CheckCircle2 class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.aprobadas }}</div>
                        <p class="text-xs text-muted-foreground">Compras aceptadas</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Contraofertas</CardTitle>
                        <Handshake class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.contraOfertas }}</div>
                        <p class="text-xs text-muted-foreground">Pendientes de revisión</p>
                    </CardContent>
                </Card>
            </section>

            <Card class="min-h-80">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <ShoppingCart class="size-5 text-muted-foreground" />
                        <CardTitle>Actividad reciente</CardTitle>
                    </div>
                    <CardDescription>Este panel queda preparado para listar solicitudes, estados y respuestas del proveedor.</CardDescription>
                </CardHeader>
            </Card>
        </div>
    </AdminLayout>
</template>
