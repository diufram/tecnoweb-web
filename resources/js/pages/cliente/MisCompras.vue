<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ReceiptText, ShoppingCart } from 'lucide-vue-next';

interface CompraDetalle {
    id: number;
    cantidad: number;
    precio_unitario: string | number;
    subtotal: string | number;
    producto: {
        nombre_comercial: string;
    };
}

interface Compra {
    id: number;
    estado_venta: string;
    fecha: string;
    total: string | number;
    detalles: CompraDetalle[];
    plan_pago: {
        estado_plan: string;
        tipo_pago: string;
        cuotas_pendientes: number;
    } | null;
}

defineProps<{
    compras: Compra[];
}>();

const money = (value: string | number) =>
    new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(Number(value));

const date = (value: string) => new Date(value).toLocaleDateString('es-BO');
</script>

<template>
    <Head title="Mis compras" />

    <CustomerLayout>
        <section class="rounded-3xl border bg-card p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-primary">Mis compras</p>
                    <h1 class="text-3xl font-semibold tracking-tight">Historial de compras</h1>
                    <p class="max-w-2xl text-muted-foreground">Revisa tus ventas registradas, sus productos y el estado del plan de pago asociado.</p>
                </div>
                <ShoppingCart class="size-12 text-muted-foreground" />
            </div>
        </section>

        <section v-if="compras.length" class="space-y-4">
            <Card v-for="compra in compras" :key="compra.id">
                <CardHeader class="gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <CardTitle>Compra #{{ compra.id }}</CardTitle>
                        <CardDescription>{{ date(compra.fecha) }} · {{ compra.estado_venta }}</CardDescription>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-sm text-muted-foreground">Total</p>
                        <p class="text-2xl font-semibold">{{ money(compra.total) }}</p>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="rounded-2xl border">
                        <div
                            v-for="detalle in compra.detalles"
                            :key="detalle.id"
                            class="grid gap-2 border-b p-4 last:border-b-0 sm:grid-cols-[1fr_6rem_8rem] sm:items-center"
                        >
                            <div>
                                <p class="font-medium">{{ detalle.producto.nombre_comercial }}</p>
                                <p class="text-sm text-muted-foreground">{{ money(detalle.precio_unitario) }} por unidad</p>
                            </div>
                            <p class="text-sm text-muted-foreground sm:text-right">Cant. {{ detalle.cantidad }}</p>
                            <p class="font-semibold sm:text-right">{{ money(detalle.subtotal) }}</p>
                        </div>
                    </div>

                    <div v-if="compra.plan_pago" class="rounded-2xl bg-muted p-4 text-sm">
                        <p class="font-medium">Plan {{ compra.plan_pago.tipo_pago }} · {{ compra.plan_pago.estado_plan }}</p>
                        <p class="text-muted-foreground">Cuotas pendientes: {{ compra.plan_pago.cuotas_pendientes }}</p>
                    </div>
                </CardContent>
            </Card>
        </section>

        <div v-else class="flex min-h-64 flex-col items-center justify-center gap-3 rounded-3xl border border-dashed text-center">
            <ReceiptText class="size-12 text-muted-foreground" />
            <div>
                <p class="font-medium">Todavía no tienes compras</p>
                <p class="text-sm text-muted-foreground">Cuando se registre una venta para tu cuenta, aparecerá aquí.</p>
            </div>
        </div>
    </CustomerLayout>
</template>
