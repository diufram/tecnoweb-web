<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, FileText, Handshake, History, ShoppingCart } from 'lucide-vue-next';

interface CompraDetalle {
    id: number;
    cantidad: number;
    precio_unitario: string | number;
    subtotal: string | number;
    producto: {
        id: number;
        nombre_comercial: string;
    };
}

interface Compra {
    id: number;
    estado: string;
    fecha_emision: string;
    monto_total: string | number;
    observaciones: string;
    proveedor: {
        empresa: string | null;
        usuario: string | null;
    };
    detalles: CompraDetalle[];
}

const props = defineProps<{
    compras: Compra[];
    tipo: 'solicitudes' | 'contraofertas' | 'compras' | 'historial';
    titulo: string;
    descripcion: string;
}>();

const money = (value: string | number) =>
    new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(Number(value));

const date = (value: string) => new Date(value).toLocaleDateString('es-BO');

const statusClass = (estado: string) => {
    switch (estado) {
        case 'APROBADO':
            return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
        case 'RECHAZADO':
            return 'border-destructive/30 bg-destructive/10 text-destructive';
        case 'CONTRA_OFERTA':
            return 'border-blue-500/30 bg-blue-500/10 text-blue-600 dark:text-blue-400';
        default:
            return 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400';
    }
};

const sections: Array<{
    tipo: Compra['tipo'] extends never ? never : 'solicitudes' | 'contraofertas' | 'compras' | 'historial';
    title: string;
    description: string;
    icon: typeof FileText;
}> = [
    { tipo: 'solicitudes', title: 'Solicitudes', description: 'Pendientes de respuesta', icon: FileText },
    { tipo: 'contraofertas', title: 'Contraofertas', description: 'Con propuestas activas', icon: Handshake },
    { tipo: 'compras', title: 'Compras', description: 'Aprobadas y rechazadas', icon: ShoppingCart },
    { tipo: 'historial', title: 'Historial', description: 'Todo lo registrado', icon: History },
];

const breadcrumbs: BreadcrumbItem[] = [{ title: props.titulo, href: '#' }];

const detalleResumen = (compra: Compra) => compra.detalles[0];
</script>

<template>
    <Head :title="titulo" />

    <AdminLayout actor="proveedor" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <section class="overflow-hidden rounded-3xl border bg-gradient-to-br from-card via-card to-primary/10 p-6 shadow-sm md:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-semibold uppercase tracking-wide text-primary">Panel proveedor</p>
                        <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">{{ titulo }}</h1>
                        <p class="max-w-2xl text-muted-foreground">{{ descripcion }}</p>
                    </div>
                    <div class="flex size-16 items-center justify-center rounded-2xl border bg-background/70 shadow-sm">
                        <ShoppingCart class="size-8 text-primary" />
                    </div>
                </div>
            </section>

            <nav class="flex flex-wrap gap-2">
                <Button
                    v-for="section in sections"
                    :key="section.tipo"
                    as-child
                    :variant="section.tipo === tipo ? 'default' : 'outline'"
                    class="rounded-full"
                >
                    <Link :href="route(`proveedor.${section.tipo}`)">
                        <component :is="section.icon" class="size-4" />
                        {{ section.title }}
                    </Link>
                </Button>
            </nav>

            <div v-if="compras.length" class="overflow-hidden rounded-2xl border">
                <Table>
                    <TableHeader>
                        <TableRow class="grid grid-cols-[6rem_1fr_1.2fr_8rem_8rem] items-center gap-4 rounded-t-2xl bg-muted hover:bg-muted">
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Estado</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Producto</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Observaciones</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-muted-foreground">Fecha</TableHead>
                            <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground">Total</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="compra in compras"
                            :key="compra.id"
                            class="grid grid-cols-[6rem_1fr_1.2fr_8rem_8rem] items-center gap-4 px-4 hover:bg-transparent"
                        >
                            <TableCell class="p-2">
                                <span
                                    class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold"
                                    :class="statusClass(compra.estado)"
                                >
                                    {{ compra.estado }}
                                </span>
                            </TableCell>

                            <TableCell class="p-2">
                                <p class="truncate font-medium">{{ detalleResumen(compra)?.producto.nombre_comercial }}</p>
                                <p class="truncate text-xs text-muted-foreground">
                                    Cant. {{ detalleResumen(compra)?.cantidad }} · {{ money(detalleResumen(compra)?.precio_unitario ?? 0) }} c/u
                                </p>
                            </TableCell>

                            <TableCell class="p-2">
                                <p class="line-clamp-2 text-sm text-muted-foreground">{{ compra.observaciones }}</p>
                            </TableCell>

                            <TableCell class="p-2 text-sm tabular-nums">{{ date(compra.fecha_emision) }}</TableCell>

                            <TableCell class="p-2 text-right font-semibold tabular-nums">
                                {{ money(compra.monto_total) }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <Card v-else class="flex flex-col items-center justify-center gap-3 border-dashed bg-card/40 py-16 text-center">
                <div class="flex size-16 items-center justify-center rounded-2xl bg-muted">
                    <CheckCircle2 class="size-8 text-muted-foreground" />
                </div>
                <div>
                    <p class="text-base font-medium">No hay registros en esta sección</p>
                    <p class="text-sm text-muted-foreground">Cuando exista actividad aparecerá aquí.</p>
                </div>
                <Button as-child variant="outline" class="rounded-full">
                    <Link :href="route('proveedor.historial')">Ver historial completo</Link>
                </Button>
            </Card>

            <Card v-if="compras.length" class="border-muted/80 bg-card/95 shadow-sm">
                <CardHeader>
                    <CardTitle class="text-lg">Detalle rapido</CardTitle>
                    <CardDescription>Abre cada compra para revisar el detalle y responder solicitudes o contraofertas.</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-3 md:grid-cols-2">
                    <Link
                        v-for="compra in compras"
                        :key="`resumen-${compra.id}`"
                        :href="route('proveedor.show', compra.id)"
                        class="flex items-center justify-between rounded-2xl border bg-background/60 p-4 transition hover:border-primary/40 hover:bg-background"
                    >
                        <div class="space-y-1">
                            <p class="font-medium">Compra #{{ compra.id }} · {{ detalleResumen(compra)?.producto.nombre_comercial }}</p>
                            <p class="text-sm text-muted-foreground">{{ date(compra.fecha_emision) }} · {{ money(compra.monto_total) }}</p>
                        </div>
                        <ArrowRight class="size-4 text-muted-foreground" />
                    </Link>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
