<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { Head } from '@inertiajs/vue3';
import { CreditCard, WalletCards } from 'lucide-vue-next';

interface Cuota {
    id: number;
    nro_cuota: number;
    fecha_vencimiento: string;
    monto: string | number;
    estado_cuota: string;
    id_transaccion_pago_facil: string | null;
}

interface PlanPago {
    id: number;
    estado_plan: string;
    tipo_pago: string;
    total_pagado: string | number;
    total_pendiente: string | number;
    proxima_cuota: Cuota | null;
    venta: {
        id: number;
        fecha: string;
        total: string | number;
    };
    cuotas: Cuota[];
}

defineProps<{
    planes: PlanPago[];
}>();

const money = (value: string | number) =>
    new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(Number(value));

const date = (value: string) => new Date(value).toLocaleDateString('es-BO');
const isPaid = (estado: string) => ['PAGADA', 'PAGADO'].includes(estado.toUpperCase());
</script>

<template>
    <Head title="Pagos" />

    <CustomerLayout>
        <section class="rounded-3xl border bg-card p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-primary">Pagos</p>
                    <h1 class="text-3xl font-semibold tracking-tight">Planes y cuotas</h1>
                    <p class="max-w-2xl text-muted-foreground">Consulta tus cuotas pendientes, vencimientos y pagos registrados.</p>
                </div>
                <WalletCards class="size-12 text-muted-foreground" />
            </div>
        </section>

        <section v-if="planes.length" class="space-y-4">
            <Card v-for="plan in planes" :key="plan.id">
                <CardHeader class="gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <CardTitle>Plan #{{ plan.id }} · Venta #{{ plan.venta.id }}</CardTitle>
                        <CardDescription>{{ plan.tipo_pago }} · {{ plan.estado_plan }} · Compra del {{ date(plan.venta.fecha) }}</CardDescription>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-sm text-muted-foreground">Pendiente</p>
                        <p class="text-2xl font-semibold">{{ money(plan.total_pendiente) }}</p>
                    </div>
                </CardHeader>
                <CardContent class="space-y-5">
                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-2xl bg-muted p-4">
                            <p class="text-sm text-muted-foreground">Total venta</p>
                            <p class="text-xl font-semibold">{{ money(plan.venta.total) }}</p>
                        </div>
                        <div class="rounded-2xl bg-muted p-4">
                            <p class="text-sm text-muted-foreground">Pagado</p>
                            <p class="text-xl font-semibold">{{ money(plan.total_pagado) }}</p>
                        </div>
                        <div class="rounded-2xl bg-muted p-4">
                            <p class="text-sm text-muted-foreground">Próxima cuota</p>
                            <p class="text-xl font-semibold">
                                {{ plan.proxima_cuota ? money(plan.proxima_cuota.monto) : 'Sin pendiente' }}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border">
                        <div
                            v-for="cuota in plan.cuotas"
                            :key="cuota.id"
                            class="grid gap-2 border-b p-4 last:border-b-0 sm:grid-cols-[7rem_1fr_8rem_8rem] sm:items-center"
                        >
                            <p class="font-medium">Cuota {{ cuota.nro_cuota }}</p>
                            <p class="text-sm text-muted-foreground">Vence {{ date(cuota.fecha_vencimiento) }}</p>
                            <p class="font-semibold sm:text-right">{{ money(cuota.monto) }}</p>
                            <p :class="isPaid(cuota.estado_cuota) ? 'text-emerald-600' : 'text-amber-600'" class="text-sm font-medium sm:text-right">
                                {{ cuota.estado_cuota }}
                            </p>
                        </div>
                    </div>

                    <Button class="w-full sm:w-auto" disabled>
                        <CreditCard class="size-4" />
                        Pago en línea pendiente
                    </Button>
                </CardContent>
            </Card>
        </section>

        <div v-else class="flex min-h-64 flex-col items-center justify-center gap-3 rounded-3xl border border-dashed text-center">
            <CreditCard class="size-12 text-muted-foreground" />
            <div>
                <p class="font-medium">No tienes pagos registrados</p>
                <p class="text-sm text-muted-foreground">Los planes de pago aparecerán cuando tengas ventas con cuotas.</p>
            </div>
        </div>
    </CustomerLayout>
</template>
