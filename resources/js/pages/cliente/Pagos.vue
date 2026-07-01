<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CreditCard, Loader2, QrCode, WalletCards } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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

interface PagoQr {
    qrBase64: string;
    paymentNumber: string;
    transactionId: string;
    monto: string | number;
    venta_id: number;
    cuota_id: number;
    nro_cuota: number;
}

defineProps<{
    planes: PlanPago[];
}>();

const page = usePage<{ flash?: { pagoQr?: PagoQr | null }; errors?: { pago?: string } }>();
const processingVentaId = ref<number | null>(null);
const pagoQr = computed(() => page.props.flash?.pagoQr ?? null);
const pagoError = computed(() => page.props.errors?.pago ?? '');
const qrSrc = computed(() => {
    if (!pagoQr.value?.qrBase64) {
        return '';
    }

    return pagoQr.value.qrBase64.startsWith('data:') ? pagoQr.value.qrBase64 : `data:image/png;base64,${pagoQr.value.qrBase64}`;
});

const money = (value: string | number) =>
    new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(Number(value));

const date = (value: string) => new Date(value).toLocaleDateString('es-BO');
const isPaid = (estado: string) => ['PAGADA', 'PAGADO'].includes(estado.toUpperCase());

const progress = (plan: PlanPago) => {
    const total = Number(plan.venta.total);

    if (!total) {
        return 0;
    }

    return Math.min(100, Math.round((Number(plan.total_pagado) / total) * 100));
};

const statusClass = (estado: string) =>
    isPaid(estado)
        ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
        : 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400';

const generateQr = (plan: PlanPago) => {
    processingVentaId.value = plan.venta.id;
    router.post(
        route('cliente.pagos.qr', plan.venta.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processingVentaId.value = null;
            },
        },
    );
};
</script>

<template>
    <Head title="Pagos" />

    <CustomerLayout>
        <section class="overflow-hidden rounded-3xl border bg-gradient-to-br from-card via-card to-primary/10 p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-semibold uppercase tracking-wide text-primary">Pagos</p>
                    <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Planes y cuotas</h1>
                    <p class="max-w-2xl text-muted-foreground">Consulta tus cuotas pendientes, vencimientos y pagos registrados.</p>
                </div>
                <div class="flex size-16 items-center justify-center rounded-2xl border bg-background/70 shadow-sm">
                    <WalletCards class="size-8 text-primary" />
                </div>
            </div>
        </section>

        <Card v-if="pagoQr" class="overflow-hidden border-primary/40 bg-gradient-to-br from-primary/10 via-card to-card shadow-md">
            <CardHeader class="gap-4 border-b bg-background/35 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <CardTitle class="flex items-center gap-2 text-2xl"><QrCode class="size-6 text-primary" /> QR de pago generado</CardTitle>
                    <CardDescription class="mt-1">Venta #{{ pagoQr.venta_id }} · Cuota {{ pagoQr.nro_cuota }}</CardDescription>
                </div>
                <div class="rounded-2xl border bg-background/80 px-4 py-3 text-sm shadow-sm sm:text-right">
                    <p class="text-muted-foreground">Monto a pagar</p>
                    <p class="text-2xl font-semibold text-foreground">{{ money(pagoQr.monto) }}</p>
                </div>
            </CardHeader>
            <CardContent class="grid gap-6 p-6 lg:grid-cols-[18rem_1fr] lg:items-center">
                <div class="mx-auto w-full max-w-72 rounded-[2rem] bg-white p-5 shadow-xl ring-1 ring-black/5">
                    <img :src="qrSrc" alt="QR de PagoFacil" class="aspect-square w-full rounded-2xl object-contain" />
                </div>
                <div class="space-y-5">
                    <div class="space-y-2">
                        <p class="text-lg font-semibold text-foreground">Escanea y confirma tu pago</p>
                        <p class="max-w-xl text-sm leading-6 text-muted-foreground">
                            Cuando PagoFacil confirme la transacción, el callback actualizará automáticamente el estado de esta cuota y del plan.
                        </p>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="rounded-2xl border bg-background/70 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Payment number</p>
                            <p class="mt-1 break-all font-mono text-sm text-foreground">{{ pagoQr.paymentNumber }}</p>
                        </div>
                        <div class="rounded-2xl border bg-background/70 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Transacción</p>
                            <p class="mt-1 break-all font-mono text-sm text-foreground">{{ pagoQr.transactionId }}</p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div v-if="pagoError" class="rounded-2xl border border-destructive/30 bg-destructive/10 p-4 text-sm font-medium text-destructive">
            {{ pagoError }}
        </div>

        <section v-if="planes.length" class="space-y-5">
            <Card v-for="plan in planes" :key="plan.id" class="overflow-hidden border-muted/80 bg-card/95 shadow-sm">
                <CardHeader class="gap-4 border-b bg-muted/20 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-3">
                        <div>
                            <CardTitle class="text-2xl">Plan #{{ plan.id }} · Venta #{{ plan.venta.id }}</CardTitle>
                            <CardDescription class="mt-1">Compra del {{ date(plan.venta.fecha) }}</CardDescription>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border bg-background px-3 py-1 text-xs font-medium">{{ plan.tipo_pago }}</span>
                            <span class="rounded-full border px-3 py-1 text-xs font-medium" :class="statusClass(plan.estado_plan)">{{
                                plan.estado_plan
                            }}</span>
                            <span class="rounded-full border bg-background px-3 py-1 text-xs font-medium">{{ progress(plan) }}% pagado</span>
                        </div>
                    </div>
                    <div class="rounded-2xl border bg-background px-5 py-4 text-left shadow-sm sm:min-w-44 sm:text-right">
                        <p class="text-sm text-muted-foreground">Pendiente</p>
                        <p class="text-2xl font-semibold">{{ money(plan.total_pendiente) }}</p>
                    </div>
                </CardHeader>
                <CardContent class="space-y-5">
                    <div class="h-2 overflow-hidden rounded-full bg-muted">
                        <div class="h-full rounded-full bg-primary transition-all" :style="{ width: `${progress(plan)}%` }" />
                    </div>

                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-2xl border bg-muted/50 p-4">
                            <p class="text-sm text-muted-foreground">Total venta</p>
                            <p class="text-xl font-semibold">{{ money(plan.venta.total) }}</p>
                        </div>
                        <div class="rounded-2xl border bg-muted/50 p-4">
                            <p class="text-sm text-muted-foreground">Pagado</p>
                            <p class="text-xl font-semibold">{{ money(plan.total_pagado) }}</p>
                        </div>
                        <div class="rounded-2xl border bg-muted/50 p-4">
                            <p class="text-sm text-muted-foreground">Próxima cuota</p>
                            <p class="text-xl font-semibold">
                                {{ plan.proxima_cuota ? money(plan.proxima_cuota.monto) : 'Sin pendiente' }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2 rounded-2xl border bg-background/40 p-2">
                        <div
                            v-for="cuota in plan.cuotas"
                            :key="cuota.id"
                            class="grid gap-2 rounded-xl p-4 transition hover:bg-muted/40 sm:grid-cols-[7rem_1fr_8rem_8rem] sm:items-center"
                        >
                            <p class="font-medium">Cuota {{ cuota.nro_cuota }}</p>
                            <p class="text-sm text-muted-foreground">Vence {{ date(cuota.fecha_vencimiento) }}</p>
                            <p class="font-semibold sm:text-right">{{ money(cuota.monto) }}</p>
                            <p
                                class="justify-self-start rounded-full border px-3 py-1 text-xs font-semibold sm:justify-self-end"
                                :class="statusClass(cuota.estado_cuota)"
                            >
                                {{ cuota.estado_cuota }}
                            </p>
                        </div>
                    </div>

                    <Button
                        class="w-full rounded-xl sm:w-auto"
                        :disabled="!plan.proxima_cuota || processingVentaId === plan.venta.id"
                        @click="generateQr(plan)"
                    >
                        <Loader2 v-if="processingVentaId === plan.venta.id" class="size-4 animate-spin" />
                        <CreditCard v-else class="size-4" />
                        {{ plan.proxima_cuota ? 'Generar QR de próxima cuota' : 'Sin cuotas pendientes' }}
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
