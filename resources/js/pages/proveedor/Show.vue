<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import StatusBadge from '@/components/shared/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useFormatters } from '@/composables/useFormatters';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, FileText, Handshake, Loader2, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

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
    id_proveedor: number;
    propietario: {
        nombre: string | null;
        email: string | null;
    };
    detalle: CompraDetalle | null;
}

const props = defineProps<{
    compra: Compra;
    puede_contraofertar: boolean;
}>();

const { money, date } = useFormatters();

const form = useForm<{
    estado: 'APROBADO' | 'RECHAZADO' | 'CONTRA_OFERTA';
    observaciones: string;
    cantidad: number;
    precio_unitario: number;
}>({
    estado: 'APROBADO',
    observaciones: '',
    cantidad: props.compra.detalle?.cantidad ?? 1,
    precio_unitario: Number(props.compra.detalle?.precio_unitario ?? 0),
});

const modo = computed<'aprobar' | 'rechazar' | 'contraofertar'>(() => {
    if (form.estado === 'APROBADO') return 'aprobar';
    if (form.estado === 'RECHAZADO') return 'rechazar';
    return 'contraofertar';
});

const subtotalPropuesto = computed(() => Math.round(form.cantidad * Number(form.precio_unitario || 0) * 100) / 100);

const submit = () => {
    form.post(route('proveedor.responder', props.compra.id), {
        preserveScroll: true,
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Solicitudes', href: route('proveedor.solicitudes') },
    { title: `Compra #${props.compra.id}`, href: '#' },
];
</script>

<template>
    <Head :title="`Compra #${compra.id}`" />

    <AdminLayout actor="proveedor" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader
                eyebrow="Detalle de compra"
                :title="`Compra #${compra.id}`"
                description="Revisa la solicitud y responde segun corresponda."
                :icon="Handshake"
            >
                <div class="flex justify-end">
                    <Button as-child variant="outline" class="rounded-full">
                        <Link :href="route('proveedor.historial')"><ArrowLeft class="size-4" /> Volver al historial</Link>
                    </Button>
                </div>
            </PageHeader>

            <div class="grid gap-6 lg:grid-cols-[1fr_24rem]">
                <div class="space-y-6">
                    <Card>
                        <CardHeader class="gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-2">
                                <CardTitle class="text-xl">Solicitud</CardTitle>
                                <CardDescription>Enviada por {{ compra.propietario.nombre ?? 'Propietario' }}</CardDescription>
                            </div>
                            <StatusBadge :estado="compra.estado" />
                        </CardHeader>
                        <CardContent class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border bg-muted/50 p-4">
                                <p class="text-sm text-muted-foreground">Fecha</p>
                                <p class="text-lg font-semibold">{{ date(compra.fecha_emision) }}</p>
                            </div>
                            <div class="rounded-2xl border bg-muted/50 p-4">
                                <p class="text-sm text-muted-foreground">Total propuesto</p>
                                <p class="text-lg font-semibold">{{ money(compra.monto_total) }}</p>
                            </div>
                            <div class="rounded-2xl border bg-muted/50 p-4">
                                <p class="text-sm text-muted-foreground">Contacto</p>
                                <p class="truncate text-sm font-medium">{{ compra.propietario.email }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card v-if="compra.detalle">
                        <CardHeader>
                            <CardTitle class="text-lg">Producto solicitado</CardTitle>
                            <CardDescription>{{ compra.detalle.producto.nombre_comercial }}</CardDescription>
                        </CardHeader>
                        <CardContent class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border bg-background/50 p-4">
                                <p class="text-sm text-muted-foreground">Cantidad</p>
                                <p class="text-lg font-semibold">{{ compra.detalle.cantidad }}</p>
                            </div>
                            <div class="rounded-2xl border bg-background/50 p-4">
                                <p class="text-sm text-muted-foreground">Precio unitario</p>
                                <p class="text-lg font-semibold">{{ money(compra.detalle.precio_unitario) }}</p>
                            </div>
                            <div class="rounded-2xl border bg-background/50 p-4">
                                <p class="text-sm text-muted-foreground">Subtotal</p>
                                <p class="text-lg font-semibold">{{ money(compra.detalle.subtotal) }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Observaciones</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm leading-6 text-muted-foreground">{{ compra.observaciones }}</p>
                        </CardContent>
                    </Card>
                </div>

                <Card class="h-fit lg:sticky lg:top-24">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2"><Handshake class="size-5" /> Responder</CardTitle>
                        <CardDescription>
                            <span v-if="puede_contraofertar">Define tu respuesta a la solicitud.</span>
                            <span v-else>Esta compra ya no admite respuestas.</span>
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form v-if="puede_contraofertar" class="space-y-5" @submit.prevent="submit">
                            <div class="grid gap-2">
                                <label class="text-sm font-medium">Tipo de respuesta</label>
                                <div class="grid gap-2 sm:grid-cols-3">
                                    <Button
                                        type="button"
                                        :variant="form.estado === 'APROBADO' ? 'default' : 'outline'"
                                        class="w-full"
                                        @click="form.estado = 'APROBADO'"
                                    >
                                        <CheckCircle2 class="size-4" />
                                        Aprobar
                                    </Button>
                                    <Button
                                        type="button"
                                        :variant="form.estado === 'CONTRA_OFERTA' ? 'default' : 'outline'"
                                        class="w-full"
                                        @click="form.estado = 'CONTRA_OFERTA'"
                                    >
                                        <Handshake class="size-4" />
                                        Contraofertar
                                    </Button>
                                    <Button
                                        type="button"
                                        :variant="form.estado === 'RECHAZADO' ? 'destructive' : 'outline'"
                                        class="w-full"
                                        @click="form.estado = 'RECHAZADO'"
                                    >
                                        <XCircle class="size-4" />
                                        Rechazar
                                    </Button>
                                </div>
                                <p v-if="form.errors.estado" class="text-sm text-destructive">{{ form.errors.estado }}</p>
                            </div>

                            <div v-if="modo === 'contraofertar'" class="grid gap-4 sm:grid-cols-2">
                                <label class="grid gap-2 text-sm font-medium">
                                    Cantidad propuesta
                                    <Input v-model.number="form.cantidad" min="1" type="number" />
                                    <span v-if="form.errors.cantidad" class="text-sm font-normal text-destructive">{{ form.errors.cantidad }}</span>
                                </label>
                                <label class="grid gap-2 text-sm font-medium">
                                    Precio unitario propuesto
                                    <Input v-model.number="form.precio_unitario" min="0.01" step="0.01" type="number" />
                                    <span v-if="form.errors.precio_unitario" class="text-sm font-normal text-destructive">{{
                                        form.errors.precio_unitario
                                    }}</span>
                                </label>
                                <div class="rounded-2xl border bg-muted/50 p-4 sm:col-span-2">
                                    <p class="text-sm text-muted-foreground">Subtotal propuesto</p>
                                    <p class="text-2xl font-semibold">{{ money(subtotalPropuesto) }}</p>
                                </div>
                            </div>

                            <div v-if="modo === 'rechazar'" class="grid gap-2">
                                <label class="grid gap-2 text-sm font-medium">
                                    Motivo del rechazo
                                    <textarea
                                        v-model="form.observaciones"
                                        rows="3"
                                        class="min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                        placeholder="Explica brevemente por que rechazas la solicitud"
                                    />
                                    <span v-if="form.errors.observaciones" class="text-sm font-normal text-destructive">{{
                                        form.errors.observaciones
                                    }}</span>
                                </label>
                            </div>

                            <p v-if="form.errors.compra" class="text-sm font-medium text-destructive">{{ form.errors.compra }}</p>

                            <Button
                                type="submit"
                                class="w-full rounded-xl"
                                :variant="modo === 'rechazar' ? 'destructive' : 'default'"
                                :disabled="form.processing"
                            >
                                <Loader2 v-if="form.processing" class="size-4 animate-spin" />
                                <FileText v-else class="size-4" />
                                {{ form.processing ? 'Enviando...' : `Confirmar ${modo}` }}
                            </Button>
                        </form>

                        <div v-else class="rounded-2xl border bg-muted/50 p-4 text-sm text-muted-foreground">
                            Esta compra se resolvio en estado <strong>{{ compra.estado }}</strong> y no admite cambios.
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
