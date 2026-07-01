<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { computed } from 'vue';
import * as z from 'zod';

interface ProductoOption {
    id: number;
    nombre_comercial: string;
}
interface Inventario {
    id: number;
    id_producto: number;
    id_lote: number;
    cantidad_disponible: number;
    costo_unitario_lote: string;
}

interface LoteOption {
    id: number;
    codigo_lote: string;
    fecha_ingreso: string;
    fecha_vencimiento: string;
}

const props = defineProps<{ mode: 'create' | 'edit'; inventario: Inventario | null; productos: ProductoOption[]; lotes: LoteOption[] }>();
const isEditing = computed(() => props.mode === 'edit');
const title = computed(() => (isEditing.value ? 'Editar inventario' : 'Crear inventario'));
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventario', href: route('propietario.inventario.index') },
    { title: title.value, href: '#' },
];

const schema = toTypedSchema(
    z.object({
        id_producto: z.coerce.number().min(1, 'Selecciona un producto.'),
        id_lote: z.coerce.number().min(1, 'Selecciona un lote.'),
        cantidad_disponible: z.coerce
            .number()
            .int('La cantidad debe ser un numero entero.')
            .min(0, 'La cantidad no puede ser negativa.')
            .max(1000000),
        costo_unitario_lote: z.coerce.number().min(0, 'El costo unitario no puede ser negativo.').max(9999999999.99),
    }),
);

const initialValues = {
    id_producto: String(props.inventario?.id_producto ?? 0),
    id_lote: String(props.inventario?.id_lote ?? 0),
    cantidad_disponible: props.inventario?.cantidad_disponible ?? 0,
    costo_unitario_lote: Number(props.inventario?.costo_unitario_lote ?? 0),
};

const veeForm = useVeeForm({ validationSchema: schema, initialValues });
const form = useInertiaForm(initialValues);
const submit = veeForm.handleSubmit((values) => {
    form.clearErrors();
    Object.assign(form, values);
    if (isEditing.value && props.inventario) {
        form.patch(route('propietario.inventario.update', props.inventario.id), {
            preserveScroll: true,
            onError: (errors) => veeForm.setErrors(errors),
        });
        return;
    }
    form.post(route('propietario.inventario.store'), { preserveScroll: true, onError: (errors) => veeForm.setErrors(errors) });
});
</script>

<template>
    <Head :title="title" />
    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <section class="space-y-2">
                    <p class="text-sm font-medium text-muted-foreground">Gestion de inventario</p>
                    <h1 class="text-3xl font-semibold tracking-tight">{{ title }}</h1>
                    <p class="max-w-2xl text-muted-foreground">El stock del producto se sincroniza con la suma de inventario.</p>
                </section>
                <Button as-child variant="outline">
                    <Link :href="route('propietario.inventario.index')"><ArrowLeft class="size-4" /> Volver</Link>
                </Button>
            </div>
            <div class="max-w-4xl space-y-6">
                <section class="space-y-1">
                    <h2 class="text-xl font-semibold tracking-tight">Datos de inventario</h2>
                    <p class="text-sm text-muted-foreground">Registra producto, lote y disponibilidad.</p>
                </section>
                <form class="grid gap-6" @submit="submit">
                    <div class="grid gap-4 md:grid-cols-2">
                        <FormField v-slot="{ componentField }" name="id_producto">
                            <FormItem>
                                <FormLabel>Producto</FormLabel>
                                <Select v-bind="componentField">
                                    <FormControl>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecciona producto" />
                                        </SelectTrigger>
                                    </FormControl>
                                    <SelectContent>
                                        <SelectItem value="0" disabled>Selecciona producto</SelectItem>
                                        <SelectItem v-for="producto in productos" :key="producto.id" :value="String(producto.id)">
                                            {{ producto.nombre_comercial }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <FormField v-slot="{ componentField }" name="id_lote">
                            <FormItem>
                                <div class="flex items-center justify-between gap-3">
                                    <FormLabel>Lote</FormLabel>
                                    <Link :href="route('propietario.lotes.create')" class="text-xs font-medium text-primary hover:underline">
                                        Crear lote
                                    </Link>
                                </div>
                                <Select v-bind="componentField">
                                    <FormControl>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecciona lote" />
                                        </SelectTrigger>
                                    </FormControl>
                                    <SelectContent>
                                        <SelectItem value="0" disabled>Selecciona lote</SelectItem>
                                        <SelectItem v-for="lote in lotes" :key="lote.id" :value="String(lote.id)">
                                            {{ lote.codigo_lote }} · Vence {{ new Date(lote.fecha_vencimiento).toLocaleDateString() }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <FormField v-slot="{ componentField }" name="cantidad_disponible">
                            <FormItem>
                                <FormLabel>Cantidad disponible</FormLabel>
                                <FormControl>
                                    <Input v-bind="componentField" min="0" type="number" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <FormField v-slot="{ componentField }" name="costo_unitario_lote">
                            <FormItem>
                                <FormLabel>Costo unitario</FormLabel>
                                <FormControl>
                                    <Input v-bind="componentField" min="0" step="0.01" type="number" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </div>
                    <div class="flex items-center gap-3">
                        <Button :disabled="form.processing">
                            <Save class="size-4" />
                            {{ isEditing ? 'Guardar cambios' : 'Crear registro' }}
                        </Button>
                        <Button as-child variant="ghost" type="button">
                            <Link :href="route('propietario.inventario.index')">Cancelar</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
