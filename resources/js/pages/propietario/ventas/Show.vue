<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import StatusBadge from '@/components/shared/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CreditCard, Mail, MapPin, Phone, Receipt, User } from 'lucide-vue-next';
import { computed } from 'vue';

interface VentaDetalle {
    id: number;
    cantidad: number;
    precio_unitario: string | number;
    subtotal: string | number;
    producto: { nombre_comercial: string | null };
}

interface Cuota {
    id: number;
    nro_cuota: number;
    fecha_vencimiento: string | null;
    monto: string | number;
    estado_cuota: string;
    id_transaccion_pago_facil: string | null;
}

interface PlanPago {
    id: number;
    estado_plan: string;
    tipo_pago: string;
    cuotas: Cuota[];
}

interface Venta {
    id: number;
    estado_venta: string;
    fecha: string;
    total: string | number;
    cliente: {
        linea_credito: string | number | null;
        nit_facturacion: string | null;
        saldo_actual: string | number | null;
        usuario: {
            nombre: string | null;
            email: string | null;
            ci_nit: string | null;
            direccion: string | null;
            telefono: string | null;
        };
    };
    detalles: VentaDetalle[];
    plan_pago: PlanPago | null;
}

const props = defineProps<{ venta: Venta }>();

const { money, date, statusClass } = useFormatters();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ventas', href: route('propietario.ventas.index') },
    { title: `Venta #${props.venta.id}`, href: '#' },
];

const cuotasPagadas = computed(() =>
    props.venta.plan_pago?.cuotas.filter((c) =>
        ['PAGADA', 'PAGADO'].includes(c.estado_cuota.toUpperCase())
    ) ?? []
);

const cuotasPendientes = computed(() =>
    props.venta.plan_pago?.cuotas.filter((c) =>
        !['PAGADA', 'PAGADO'].includes(c.estado_cuota.toUpperCase())
    ) ?? []
);
</script>

<template>
    <Head :title="`Venta #${venta.id}`" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">

            <!-- Page Header -->
            <PageHeader
                eyebrow="Detalle de venta"
                :title="`Venta #${venta.id}`"
                description="Visualiza los productos vendidos, la información del cliente y el estado de pago."
                :icon="Receipt"
            >
                <div class="flex justify-end">
                    <Button as-child variant="outline" class="rounded-full">
                        <Link :href="route('propietario.ventas.index')">
                            <ArrowLeft class="size-4" />
                            Volver
                        </Link>
                    </Button>
                </div>
            </PageHeader>

            <!-- Main Grid: content + sidebar -->
            <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">

                <!-- Left column -->
                <div class="space-y-6">

                    <!-- Summary card -->
                    <Card>
                        <CardHeader class="gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-1">
                                <CardTitle class="text-xl">Resumen</CardTitle>
                                <CardDescription>
                                    {{ venta.cliente.usuario.nombre ?? 'Cliente' }} · {{ date(venta.fecha) }}
                                </CardDescription>
                            </div>
                            <StatusBadge :estado="venta.estado_venta" />
                        </CardHeader>
                        <CardContent class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border bg-muted/50 p-4">
                                <p class="text-sm text-muted-foreground">Total de la venta</p>
                                <p class="mt-1 text-2xl font-semibold tabular-nums">{{ money(venta.total) }}</p>
                            </div>
                            <div class="rounded-2xl border bg-muted/50 p-4">
                                <p class="text-sm text-muted-foreground">Cantidad de productos</p>
                                <p class="mt-1 text-2xl font-semibold tabular-nums">{{ venta.detalles.length }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Detalles de productos -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Detalle de productos</CardTitle>
                            <CardDescription>Productos incluidos en esta venta.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-hidden rounded-2xl border">
                                <Table>
                                    <TableHeader>
                                        <TableRow class="grid grid-cols-[1fr_7rem_10rem_10rem] items-center gap-3 bg-muted hover:bg-muted">
                                            <TableHead class="px-4 py-3 text-muted-foreground">Producto</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Cant.</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Precio unit.</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Subtotal</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="detalle in venta.detalles"
                                            :key="detalle.id"
                                            class="grid grid-cols-[1fr_7rem_10rem_10rem] items-center gap-3 px-4 transition hover:bg-muted/40"
                                        >
                                            <TableCell class="p-3 font-medium">
                                                {{ detalle.producto.nombre_comercial ?? '—' }}
                                            </TableCell>
                                            <TableCell class="p-3 text-right tabular-nums">{{ detalle.cantidad }}</TableCell>
                                            <TableCell class="p-3 text-right tabular-nums">{{ money(detalle.precio_unitario) }}</TableCell>
                                            <TableCell class="p-3 text-right font-semibold tabular-nums">{{ money(detalle.subtotal) }}</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Plan de pago (si existe) -->
                    <Card v-if="venta.plan_pago">
                        <CardHeader class="sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <CardTitle class="text-lg flex items-center gap-2">
                                    <CreditCard class="size-4 text-primary" />
                                    Plan de pago
                                </CardTitle>
                                <CardDescription>
                                    {{ venta.plan_pago.tipo_pago }} ·
                                    {{ cuotasPagadas.length }}/{{ venta.plan_pago.cuotas.length }} cuotas pagadas
                                </CardDescription>
                            </div>
                            <span
                                class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold"
                                :class="statusClass(venta.plan_pago.estado_plan)"
                            >
                                {{ venta.plan_pago.estado_plan }}
                            </span>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-hidden rounded-2xl border">
                                <Table>
                                    <TableHeader>
                                        <TableRow class="grid grid-cols-[5rem_1fr_10rem_10rem_8rem] items-center gap-3 bg-muted hover:bg-muted">
                                            <TableHead class="px-4 py-3 text-muted-foreground">Nro.</TableHead>
                                            <TableHead class="px-4 py-3 text-muted-foreground">Vencimiento</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Monto</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Estado</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Transacción</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="cuota in venta.plan_pago.cuotas"
                                            :key="cuota.id"
                                            class="grid grid-cols-[5rem_1fr_10rem_10rem_8rem] items-center gap-3 px-4 transition hover:bg-muted/40"
                                        >
                                            <TableCell class="p-3 font-medium tabular-nums">{{ cuota.nro_cuota }}</TableCell>
                                            <TableCell class="p-3 text-sm text-muted-foreground">
                                                {{ cuota.fecha_vencimiento ? date(cuota.fecha_vencimiento) : '—' }}
                                            </TableCell>
                                            <TableCell class="p-3 text-right font-semibold tabular-nums">{{ money(cuota.monto) }}</TableCell>
                                            <TableCell class="p-3 text-right">
                                                <StatusBadge :estado="cuota.estado_cuota" />
                                            </TableCell>
                                            <TableCell class="p-3 text-right text-xs text-muted-foreground tabular-nums">
                                                {{ cuota.id_transaccion_pago_facil ?? '—' }}
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>

                            <!-- Resumen del plan -->
                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl border bg-muted/50 p-4">
                                    <p class="text-sm text-muted-foreground">Total pagado</p>
                                    <p class="mt-1 text-lg font-semibold text-emerald-500">
                                        {{ money(cuotasPagadas.reduce((s, c) => s + Number(c.monto), 0)) }}
                                    </p>
                                </div>
                                <div class="rounded-2xl border bg-muted/50 p-4">
                                    <p class="text-sm text-muted-foreground">Total pendiente</p>
                                    <p class="mt-1 text-lg font-semibold text-destructive">
                                        {{ money(cuotasPendientes.reduce((s, c) => s + Number(c.monto), 0)) }}
                                    </p>
                                </div>
                                <div class="rounded-2xl border bg-muted/50 p-4">
                                    <p class="text-sm text-muted-foreground">Cuotas restantes</p>
                                    <p class="mt-1 text-lg font-semibold">{{ cuotasPendientes.length }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- Right sidebar: client info -->
                <Card class="h-fit lg:sticky lg:top-24">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="size-4 text-primary" />
                            Cliente
                        </CardTitle>
                        <CardDescription>Información del cliente asociado a esta venta.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Nombre -->
                        <div class="rounded-2xl border bg-muted/50 p-4 space-y-1">
                            <p class="text-sm font-medium">{{ venta.cliente.usuario.nombre ?? '—' }}</p>
                            <p class="text-xs text-muted-foreground">Nombre completo</p>
                        </div>

                        <!-- Info list -->
                        <div class="space-y-3">
                            <div class="flex items-start gap-3 text-sm">
                                <Mail class="size-4 mt-0.5 shrink-0 text-muted-foreground" />
                                <div>
                                    <p class="font-medium">{{ venta.cliente.usuario.email ?? '—' }}</p>
                                    <p class="text-xs text-muted-foreground">Correo electrónico</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 text-sm">
                                <Phone class="size-4 mt-0.5 shrink-0 text-muted-foreground" />
                                <div>
                                    <p class="font-medium">{{ venta.cliente.usuario.telefono ?? '—' }}</p>
                                    <p class="text-xs text-muted-foreground">Teléfono</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 text-sm">
                                <MapPin class="size-4 mt-0.5 shrink-0 text-muted-foreground" />
                                <div>
                                    <p class="font-medium">{{ venta.cliente.usuario.direccion ?? '—' }}</p>
                                    <p class="text-xs text-muted-foreground">Dirección</p>
                                </div>
                            </div>
                        </div>

                        <!-- CI/NIT and Crédito -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border bg-muted/50 p-3">
                                <p class="text-xs text-muted-foreground">CI/NIT</p>
                                <p class="mt-0.5 text-sm font-semibold tabular-nums">{{ venta.cliente.usuario.ci_nit ?? '—' }}</p>
                            </div>
                            <div class="rounded-2xl border bg-muted/50 p-3">
                                <p class="text-xs text-muted-foreground">NIT Facturación</p>
                                <p class="mt-0.5 text-sm font-semibold tabular-nums">{{ venta.cliente.nit_facturacion ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div class="rounded-2xl border bg-muted/50 p-3">
                                <p class="text-xs text-muted-foreground">Línea de crédito</p>
                                <p class="mt-0.5 font-semibold tabular-nums">{{ money(venta.cliente.linea_credito) }}</p>
                            </div>
                            <div class="rounded-2xl border bg-muted/50 p-3">
                                <p class="text-xs text-muted-foreground">Saldo actual (deuda)</p>
                                <p
                                    class="mt-0.5 font-semibold tabular-nums"
                                    :class="Number(venta.cliente.saldo_actual) > 0 ? 'text-destructive' : 'text-emerald-500'"
                                >
                                    {{ money(venta.cliente.saldo_actual) }}
                                </p>
                            </div>
                        </div>

                        <!-- Sin plan de pago -->
                        <div v-if="!venta.plan_pago" class="rounded-2xl border bg-muted/50 p-4 text-sm text-muted-foreground">
                            Esta venta no tiene un plan de pago asociado.
                        </div>
                    </CardContent>
                </Card>

            </div>
        </div>
    </AdminLayout>
</template>
