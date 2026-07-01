<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DateInput, Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { textareaClass } from '@/lib/form-classes';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Save, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface Option {
    id: number;
    nombre_comercial?: string;
    empresa?: string;
}

interface CompraDetalleForm {
    id_producto: string;
    cantidad: number;
    precio_unitario: number;
}

interface Compra {
    id: number;
    estado: string;
    fecha_emision: string;
    observaciones: string;
    id_proveedor: number;
    detalles: Array<{
        id_producto: number;
        cantidad: number;
        precio_unitario: string | number;
    }>;
}

const props = defineProps<{
    mode: 'create' | 'edit';
    compra: Compra | null;
    proveedores: Option[];
    productos: Option[];
    estados: string[];
}>();

const isEditing = computed(() => props.mode === 'edit');
const title = computed(() => (isEditing.value ? 'Editar compra' : 'Crear compra'));
const today = new Date().toISOString().slice(0, 10);
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Compras', href: route('propietario.compras.index') },
    { title: title.value, href: '#' },
];

const initialDetails = (): CompraDetalleForm[] => {
    if (props.compra?.detalles?.length) {
        return props.compra.detalles.map((detalle) => ({
            id_producto: String(detalle.id_producto),
            cantidad: detalle.cantidad,
            precio_unitario: Number(detalle.precio_unitario),
        }));
    }

    return [{ id_producto: '0', cantidad: 1, precio_unitario: 0 }];
};

const form = useForm({
    estado: props.compra?.estado ?? 'SOLICITUD',
    fecha_emision: props.compra?.fecha_emision ?? today,
    observaciones: props.compra?.observaciones ?? '',
    id_proveedor: String(props.compra?.id_proveedor ?? 0),
    detalles: initialDetails(),
});

const totalSolicitado = computed(() =>
    form.detalles.reduce((total, detalle) => total + Number(detalle.cantidad || 0) * Number(detalle.precio_unitario || 0), 0),
);

const addDetalle = () => {
    form.detalles.push({ id_producto: '0', cantidad: 1, precio_unitario: 0 });
};

const removeDetalle = (index: number) => {
    if (form.detalles.length === 1) {
        return;
    }

    form.detalles.splice(index, 1);
};

const fieldError = (index: number, field: keyof CompraDetalleForm) => form.errors[`detalles.${index}.${field}` as keyof typeof form.errors];

const submit = () => {
    form.clearErrors();

    if (isEditing.value && props.compra) {
        form.patch(route('propietario.compras.update', props.compra.id), { preserveScroll: true });
        return;
    }

    form.post(route('propietario.compras.store'), { preserveScroll: true });
};
</script>

<template>
    <Head :title="title" />
    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <section class="space-y-2">
                    <p class="text-sm font-medium text-muted-foreground">Gestion de compras</p>
                    <h1 class="text-3xl font-semibold tracking-tight">{{ title }}</h1>
                    <p class="max-w-2xl text-muted-foreground">Solicita uno o varios productos al proveedor. El total se calcula automaticamente.</p>
                </section>
                <Button as-child variant="outline">
                    <Link :href="route('propietario.compras.index')"><ArrowLeft class="size-4" /> Volver</Link>
                </Button>
            </div>

            <div class="max-w-5xl space-y-6">
                <section class="space-y-1">
                    <h2 class="text-xl font-semibold tracking-tight">Datos de compra</h2>
                    <p class="text-sm text-muted-foreground">Selecciona proveedor y agrega los productos de la solicitud.</p>
                </section>

                <form class="grid gap-6" @submit.prevent="submit">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="grid gap-2 text-sm font-medium">
                            Estado
                            <Select v-model="form.estado">
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecciona un estado" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="estado in estados" :key="estado" :value="estado">
                                        {{ estado }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <span v-if="form.errors.estado" class="text-sm font-normal text-destructive">{{ form.errors.estado }}</span>
                        </label>

                        <label class="grid gap-2 text-sm font-medium">
                            Fecha de emision
                            <DateInput v-model="form.fecha_emision" />
                            <span v-if="form.errors.fecha_emision" class="text-sm font-normal text-destructive">{{ form.errors.fecha_emision }}</span>
                        </label>

                        <label class="grid gap-2 text-sm font-medium md:col-span-2">
                            Proveedor
                            <Select v-model="form.id_proveedor">
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecciona proveedor" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="0" disabled>Selecciona proveedor</SelectItem>
                                    <SelectItem v-for="proveedor in proveedores" :key="proveedor.id" :value="String(proveedor.id)">
                                        {{ proveedor.empresa }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <span v-if="form.errors.id_proveedor" class="text-sm font-normal text-destructive">{{ form.errors.id_proveedor }}</span>
                        </label>
                    </div>

                    <section class="space-y-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-semibold">Productos solicitados</h3>
                                <p class="text-sm text-muted-foreground">Puedes agregar varios productos en la misma solicitud.</p>
                            </div>
                            <Button type="button" variant="outline" class="rounded-full" @click="addDetalle">
                                <Plus class="size-4" /> Agregar producto
                            </Button>
                        </div>

                        <div
                            v-for="(detalle, index) in form.detalles"
                            :key="index"
                            class="grid gap-4 rounded-2xl border bg-card p-4 md:grid-cols-[1fr_8rem_10rem_auto] md:items-start"
                        >
                            <label class="grid gap-2 text-sm font-medium">
                                Producto
                                <Select v-model="detalle.id_producto">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona producto" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="0" disabled>Selecciona producto</SelectItem>
                                        <SelectItem v-for="producto in productos" :key="producto.id" :value="String(producto.id)">
                                            {{ producto.nombre_comercial }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <span v-if="fieldError(index, 'id_producto')" class="text-sm font-normal text-destructive">{{
                                    fieldError(index, 'id_producto')
                                }}</span>
                            </label>

                            <label class="grid gap-2 text-sm font-medium">
                                Cantidad
                                <Input v-model.number="detalle.cantidad" min="1" type="number" />
                                <span v-if="fieldError(index, 'cantidad')" class="text-sm font-normal text-destructive">{{
                                    fieldError(index, 'cantidad')
                                }}</span>
                            </label>

                            <label class="grid gap-2 text-sm font-medium">
                                Precio unitario
                                <Input v-model.number="detalle.precio_unitario" min="0.01" step="0.01" type="number" />
                                <span v-if="fieldError(index, 'precio_unitario')" class="text-sm font-normal text-destructive">{{
                                    fieldError(index, 'precio_unitario')
                                }}</span>
                            </label>

                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="mt-7"
                                :disabled="form.detalles.length === 1"
                                @click="removeDetalle(index)"
                            >
                                <Trash2 class="size-4 text-destructive" />
                            </Button>
                        </div>

                        <p v-if="form.errors.detalles" class="text-sm font-medium text-destructive">{{ form.errors.detalles }}</p>
                    </section>

                    <label class="grid gap-2 text-sm font-medium">
                        Observaciones
                        <textarea v-model="form.observaciones" :class="textareaClass" placeholder="Detalle de la solicitud" />
                        <span v-if="form.errors.observaciones" class="text-sm font-normal text-destructive">{{ form.errors.observaciones }}</span>
                    </label>

                    <div class="flex flex-col gap-4 rounded-2xl border bg-muted/40 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total solicitado</p>
                            <p class="text-2xl font-semibold tabular-nums">
                                {{ new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(totalSolicitado) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <Button :disabled="form.processing">
                                <Save class="size-4" />
                                {{ isEditing ? 'Guardar cambios' : 'Crear compra' }}
                            </Button>
                            <Button as-child variant="ghost" type="button">
                                <Link :href="route('propietario.compras.index')">Cancelar</Link>
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
