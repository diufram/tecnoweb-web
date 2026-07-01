<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import StatusBadge from '@/components/shared/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { textareaClass } from '@/lib/form-classes';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, Loader2, ShoppingCart, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface CompraDetalle {
    id: number;
    cantidad: number;
    precio_unitario: string | number;
    subtotal: string | number;
    cantidad_contraoferta: number | null;
    precio_unitario_contraoferta: string | number | null;
    subtotal_contraoferta: string | number | null;
    diferencia_subtotal: string | number | null;
    producto: { id: number; nombre_comercial: string };
}

interface Compra {
    id: number;
    estado: string;
    fecha_emision: string;
    monto_total: string | number;
    total_solicitado: string | number;
    total_contraoferta: string | number | null;
    observaciones: string;
    proveedor: { empresa: string | null; usuario: { nombre: string | null; email: string | null } };
    detalles: CompraDetalle[];
}

const props = defineProps<{
    compra: Compra;
    puede_resolver_contraoferta: boolean;
}>();

const { money, date } = useFormatters();
const form = useForm<{ estado: 'APROBADO' | 'RECHAZADO'; observaciones: string }>({
    estado: 'APROBADO',
    observaciones: '',
});

const diferenciaTotal = computed(() => Number(props.compra.total_contraoferta ?? 0) - Number(props.compra.total_solicitado ?? 0));

const submit = (estado: 'APROBADO' | 'RECHAZADO') => {
    form.estado = estado;
    form.post(route('propietario.compras.resolver-contraoferta', props.compra.id), { preserveScroll: true });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Compras', href: route('propietario.compras.index') },
    { title: `Compra #${props.compra.id}`, href: '#' },
];
</script>

<template>
    <Head :title="`Compra #${compra.id}`" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader
                eyebrow="Detalle de compra"
                :title="`Compra #${compra.id}`"
                description="Compara la solicitud original con la contraoferta del proveedor."
                :icon="ShoppingCart"
            >
                <div class="flex justify-end">
                    <Button as-child variant="outline" class="rounded-full">
                        <Link :href="route('propietario.compras.index')"><ArrowLeft class="size-4" /> Volver</Link>
                    </Button>
                </div>
            </PageHeader>

            <div class="grid gap-6 lg:grid-cols-[1fr_24rem]">
                <div class="space-y-6">
                    <Card>
                        <CardHeader class="gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-2">
                                <CardTitle class="text-xl">Resumen</CardTitle>
                                <CardDescription>{{ compra.proveedor.empresa ?? 'Proveedor' }} · {{ date(compra.fecha_emision) }}</CardDescription>
                            </div>
                            <StatusBadge :estado="compra.estado" />
                        </CardHeader>
                        <CardContent class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border bg-muted/50 p-4">
                                <p class="text-sm text-muted-foreground">Total solicitado</p>
                                <p class="text-lg font-semibold">{{ money(compra.total_solicitado) }}</p>
                            </div>
                            <div class="rounded-2xl border bg-muted/50 p-4">
                                <p class="text-sm text-muted-foreground">Total contraoferta</p>
                                <p class="text-lg font-semibold">
                                    {{ compra.total_contraoferta ? money(compra.total_contraoferta) : 'Sin contraoferta' }}
                                </p>
                            </div>
                            <div class="rounded-2xl border bg-muted/50 p-4">
                                <p class="text-sm text-muted-foreground">Diferencia</p>
                                <p :class="['text-lg font-semibold', diferenciaTotal > 0 ? 'text-destructive' : 'text-emerald-500']">
                                    {{ compra.total_contraoferta ? money(diferenciaTotal) : money(0) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Comparacion por producto</CardTitle>
                            <CardDescription>La columna diferencia muestra el cambio contra lo solicitado originalmente.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-hidden rounded-2xl border">
                                <Table>
                                    <TableHeader>
                                        <TableRow
                                            class="grid grid-cols-[1fr_8rem_9rem_9rem_8rem_9rem_9rem] items-center gap-3 bg-muted hover:bg-muted"
                                        >
                                            <TableHead class="px-4 py-3 text-muted-foreground">Producto</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Cant. sol.</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Precio sol.</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Subtotal sol.</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Cant. contra</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Subtotal contra</TableHead>
                                            <TableHead class="px-4 py-3 text-right text-muted-foreground">Diferencia</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="detalle in compra.detalles"
                                            :key="detalle.id"
                                            class="grid grid-cols-[1fr_8rem_9rem_9rem_8rem_9rem_9rem] items-center gap-3 px-4 transition hover:bg-muted/40"
                                        >
                                            <TableCell class="p-3 font-medium">{{ detalle.producto.nombre_comercial }}</TableCell>
                                            <TableCell class="p-3 text-right tabular-nums">{{ detalle.cantidad }}</TableCell>
                                            <TableCell class="p-3 text-right tabular-nums">{{ money(detalle.precio_unitario) }}</TableCell>
                                            <TableCell class="p-3 text-right font-semibold tabular-nums">{{ money(detalle.subtotal) }}</TableCell>
                                            <TableCell class="p-3 text-right tabular-nums">{{ detalle.cantidad_contraoferta ?? '-' }}</TableCell>
                                            <TableCell class="p-3 text-right font-semibold tabular-nums">
                                                {{ detalle.subtotal_contraoferta ? money(detalle.subtotal_contraoferta) : '-' }}
                                            </TableCell>
                                            <TableCell class="p-3 text-right tabular-nums">
                                                <span
                                                    v-if="detalle.diferencia_subtotal !== null"
                                                    :class="Number(detalle.diferencia_subtotal) > 0 ? 'text-destructive' : 'text-emerald-500'"
                                                >
                                                    {{ money(detalle.diferencia_subtotal) }}
                                                </span>
                                                <span v-else>-</span>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
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
                        <CardTitle>Decision</CardTitle>
                        <CardDescription>
                            <span v-if="puede_resolver_contraoferta">Acepta o rechaza la contraoferta del proveedor.</span>
                            <span v-else>Esta compra no tiene una contraoferta pendiente.</span>
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <template v-if="puede_resolver_contraoferta">
                            <label class="grid gap-2 text-sm font-medium">
                                Motivo si rechazas
                                <textarea v-model="form.observaciones" :class="textareaClass" placeholder="Explica el motivo del rechazo" />
                                <span v-if="form.errors.observaciones" class="text-sm font-normal text-destructive">{{
                                    form.errors.observaciones
                                }}</span>
                            </label>

                            <p v-if="form.errors.estado" class="text-sm text-destructive">{{ form.errors.estado }}</p>

                            <div class="grid gap-2">
                                <Button type="button" class="w-full rounded-xl" :disabled="form.processing" @click="submit('APROBADO')">
                                    <Loader2 v-if="form.processing && form.estado === 'APROBADO'" class="size-4 animate-spin" />
                                    <CheckCircle2 v-else class="size-4" />
                                    Aceptar contraoferta
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    class="w-full rounded-xl"
                                    :disabled="form.processing"
                                    @click="submit('RECHAZADO')"
                                >
                                    <Loader2 v-if="form.processing && form.estado === 'RECHAZADO'" class="size-4 animate-spin" />
                                    <XCircle v-else class="size-4" />
                                    Rechazar contraoferta
                                </Button>
                            </div>
                        </template>

                        <div v-else class="rounded-2xl border bg-muted/50 p-4 text-sm text-muted-foreground">
                            Estado actual: <strong>{{ compra.estado }}</strong>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
