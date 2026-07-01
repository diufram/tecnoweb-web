<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import StatGrid from '@/components/shared/StatGrid.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, FileText, Handshake, ShoppingCart } from 'lucide-vue-next';
import { computed, type Component } from 'vue';

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

interface StatItem {
    title: string;
    value: number;
    description: string;
    icon: Component;
    href: string;
}

const statCards = computed<StatItem[]>(() => [
    { title: 'Solicitudes', value: 0, description: 'Pendientes de tu respuesta', icon: FileText, href: route('proveedor.solicitudes') },
    { title: 'Contraofertas', value: 0, description: 'Propuestas activas', icon: Handshake, href: route('proveedor.contraofertas') },
    { title: 'Compras', value: 0, description: 'Aprobadas y rechazadas', icon: ShoppingCart, href: route('proveedor.compras') },
    { title: 'Aprobadas', value: 0, description: 'Historial confirmado', icon: CheckCircle2, href: route('proveedor.historial') },
]);
</script>

<template>
    <Head title="Dashboard proveedor" />

    <AdminLayout actor="proveedor" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader
                title="Solicitudes y contraofertas"
                description="Revisa solicitudes de compra, responde contraofertas y consulta tu historial comercial."
                :icon="ShoppingCart"
            />

            <StatGrid :stats="statCards" />

            <Card class="border-muted/80 bg-card/95 shadow-sm">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <FileText class="size-5 text-muted-foreground" />
                        <CardTitle>Actividad reciente</CardTitle>
                    </div>
                    <CardDescription>Resumen rapido de tus metricas clave.</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-3 md:grid-cols-3">
                    <Link
                        :href="route('proveedor.solicitudes')"
                        class="flex items-center justify-between rounded-2xl border bg-background/60 p-4 transition hover:border-primary/40"
                    >
                        <div>
                            <p class="text-sm text-muted-foreground">Solicitudes</p>
                            <p class="text-2xl font-semibold">{{ stats.solicitudes }}</p>
                        </div>
                        <ArrowRight class="size-4 text-muted-foreground" />
                    </Link>
                    <Link
                        :href="route('proveedor.contraofertas')"
                        class="flex items-center justify-between rounded-2xl border bg-background/60 p-4 transition hover:border-primary/40"
                    >
                        <div>
                            <p class="text-sm text-muted-foreground">Contraofertas</p>
                            <p class="text-2xl font-semibold">{{ stats.contraOfertas }}</p>
                        </div>
                        <ArrowRight class="size-4 text-muted-foreground" />
                    </Link>
                    <Link
                        :href="route('proveedor.historial')"
                        class="flex items-center justify-between rounded-2xl border bg-background/60 p-4 transition hover:border-primary/40"
                    >
                        <div>
                            <p class="text-sm text-muted-foreground">Aprobadas</p>
                            <p class="text-2xl font-semibold">{{ stats.aprobadas }}</p>
                        </div>
                        <ArrowRight class="size-4 text-muted-foreground" />
                    </Link>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
