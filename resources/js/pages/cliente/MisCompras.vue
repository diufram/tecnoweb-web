<script setup lang="ts">
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import StatusBadge from '@/components/shared/StatusBadge.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useFormatters } from '@/composables/useFormatters';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ShoppingCart } from 'lucide-vue-next';

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

const { money, date } = useFormatters();
</script>

<template>
    <Head title="Mis compras" />

    <CustomerLayout>
        <PageHeader
            eyebrow="Mis compras"
            title="Historial de compras"
            description="Revisa tus ventas registradas, sus productos y el estado del plan de pago asociado."
            :icon="ShoppingCart"
        />

        <section v-if="compras.length" class="space-y-4">
            <Card v-for="compra in compras" :key="compra.id">
                <CardHeader class="gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <CardTitle>Compra #{{ compra.id }}</CardTitle>
                        <CardDescription>{{ date(compra.fecha) }} · {{ compra.estado_venta }}</CardDescription>
                        <StatusBadge :estado="compra.estado_venta" />
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

        <EmptyState
            v-else
            :icon="ShoppingCart"
            title="Todavía no tienes compras"
            description="Cuando se registre una venta para tu cuenta, aparecerá aquí."
        />
    </CustomerLayout>
</template>
