<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { toTypedSchema } from '@vee-validate/zod';
import { Head, Link, useForm as useInertiaForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { computed } from 'vue';
import * as z from 'zod';

interface Option { id: number; nombre_comercial?: string; empresa?: string }
interface Compra { id: number; estado: string; fecha_emision: string; observaciones: string; id_proveedor: number; id_producto: number; cantidad: number; precio_unitario: string }

const props = defineProps<{ mode: 'create' | 'edit'; compra: Compra | null; proveedores: Option[]; productos: Option[]; estados: string[] }>();
const isEditing = computed(() => props.mode === 'edit');
const title = computed(() => (isEditing.value ? 'Editar compra' : 'Crear compra'));
const today = new Date().toISOString().slice(0, 10);
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Compras', href: route('propietario.compras.index') }, { title: title.value, href: '#' }];

const selectClass = 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base transition-colors focus-visible:border-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 aria-[invalid=true]:border-destructive aria-[invalid=true]:focus-visible:border-destructive md:text-sm';
const textareaClass = 'flex min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-base transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 aria-[invalid=true]:border-destructive aria-[invalid=true]:focus-visible:border-destructive md:text-sm';

const schema = toTypedSchema(z.object({
    estado: z.string().min(1, 'Selecciona un estado.'),
    fecha_emision: z.string().min(1, 'La fecha de emision es obligatoria.'),
    observaciones: z.string().trim().min(3, 'Las observaciones deben tener al menos 3 caracteres.').max(1000),
    id_proveedor: z.coerce.number().min(1, 'Selecciona un proveedor.'),
    id_producto: z.coerce.number().min(1, 'Selecciona un producto.'),
    cantidad: z.coerce.number().int('La cantidad debe ser un numero entero.').min(1, 'La cantidad debe ser mayor a cero.').max(1000000),
    precio_unitario: z.coerce.number().min(0.01, 'El precio unitario debe ser mayor a cero.').max(9999999999.99),
}));

const initialValues = {
    estado: props.compra?.estado ?? 'SOLICITUD',
    fecha_emision: props.compra?.fecha_emision ?? today,
    observaciones: props.compra?.observaciones ?? '',
    id_proveedor: props.compra?.id_proveedor ?? 0,
    id_producto: props.compra?.id_producto ?? 0,
    cantidad: props.compra?.cantidad ?? 1,
    precio_unitario: Number(props.compra?.precio_unitario ?? 0),
};

const veeForm = useVeeForm({ validationSchema: schema, initialValues });
const form = useInertiaForm(initialValues);
const submit = veeForm.handleSubmit((values) => {
    form.clearErrors();
    Object.assign(form, values);
    if (isEditing.value && props.compra) {
        form.patch(route('propietario.compras.update', props.compra.id), { preserveScroll: true, onError: (errors) => veeForm.setErrors(errors) });
        return;
    }
    form.post(route('propietario.compras.store'), { preserveScroll: true, onError: (errors) => veeForm.setErrors(errors) });
});
</script>

<template>
    <Head :title="title" />
    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <section class="space-y-2"><p class="text-sm font-medium text-muted-foreground">Gestion de compras</p><h1 class="text-3xl font-semibold tracking-tight">{{ title }}</h1><p class="max-w-2xl text-muted-foreground">El total se calcula automaticamente en el servidor.</p></section>
                <Button as-child variant="outline"><Link :href="route('propietario.compras.index')"><ArrowLeft class="size-4" /> Volver</Link></Button>
            </div>
            <div class="max-w-4xl space-y-6">
                <section class="space-y-1"><h2 class="text-xl font-semibold tracking-tight">Datos de compra</h2><p class="text-sm text-muted-foreground">Selecciona proveedor, producto y cantidad solicitada.</p></section>
                <form class="grid gap-6" @submit="submit">
                    <div class="grid gap-4 md:grid-cols-2">
                        <FormField v-slot="{ componentField }" name="estado"><FormItem><FormLabel>Estado</FormLabel><FormControl><select v-bind="componentField" :class="cn(selectClass)"><option v-for="estado in estados" :key="estado" :value="estado">{{ estado }}</option></select></FormControl><FormMessage /></FormItem></FormField>
                        <FormField v-slot="{ componentField }" name="fecha_emision"><FormItem><FormLabel>Fecha de emision</FormLabel><FormControl><Input v-bind="componentField" type="date" /></FormControl><FormMessage /></FormItem></FormField>
                        <FormField v-slot="{ componentField }" name="id_proveedor"><FormItem><FormLabel>Proveedor</FormLabel><FormControl><select v-bind="componentField" :class="cn(selectClass)"><option :value="0">Selecciona proveedor</option><option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">{{ proveedor.empresa }}</option></select></FormControl><FormMessage /></FormItem></FormField>
                        <FormField v-slot="{ componentField }" name="id_producto"><FormItem><FormLabel>Producto</FormLabel><FormControl><select v-bind="componentField" :class="cn(selectClass)"><option :value="0">Selecciona producto</option><option v-for="producto in productos" :key="producto.id" :value="producto.id">{{ producto.nombre_comercial }}</option></select></FormControl><FormMessage /></FormItem></FormField>
                        <FormField v-slot="{ componentField }" name="cantidad"><FormItem><FormLabel>Cantidad</FormLabel><FormControl><Input v-bind="componentField" min="1" type="number" /></FormControl><FormMessage /></FormItem></FormField>
                        <FormField v-slot="{ componentField }" name="precio_unitario"><FormItem><FormLabel>Precio unitario</FormLabel><FormControl><Input v-bind="componentField" min="0.01" step="0.01" type="number" /></FormControl><FormMessage /></FormItem></FormField>
                        <FormField v-slot="{ componentField }" name="observaciones"><FormItem class="md:col-span-2"><FormLabel>Observaciones</FormLabel><FormControl><textarea v-bind="componentField" :class="cn(textareaClass)" placeholder="Detalle de la solicitud" /></FormControl><FormMessage /></FormItem></FormField>
                    </div>
                    <div class="flex items-center gap-3"><Button :disabled="form.processing"><Save class="size-4" /> {{ isEditing ? 'Guardar cambios' : 'Crear compra' }}</Button><Button as-child variant="ghost" type="button"><Link :href="route('propietario.compras.index')">Cancelar</Link></Button></div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
