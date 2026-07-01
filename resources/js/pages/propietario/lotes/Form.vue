<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { DateInput, Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { computed } from 'vue';
import * as z from 'zod';

interface Lote {
    id: number;
    codigo_lote: string;
    fecha_ingreso: string;
    fecha_vencimiento: string;
}

const props = defineProps<{
    mode: 'create' | 'edit';
    lote: Lote | null;
}>();

const isEditing = computed(() => props.mode === 'edit');
const title = computed(() => (isEditing.value ? 'Editar lote' : 'Crear lote'));
const today = new Date().toISOString().slice(0, 10);
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Lotes', href: route('propietario.lotes.index') },
    { title: title.value, href: '#' },
];

const schema = toTypedSchema(
    z
        .object({
            codigo_lote: z.string().trim().min(3, 'El codigo de lote debe tener al menos 3 caracteres.').max(255),
            fecha_ingreso: z.string().min(1, 'La fecha de ingreso es obligatoria.'),
            fecha_vencimiento: z.string().min(1, 'La fecha de vencimiento es obligatoria.'),
        })
        .refine((data) => data.fecha_vencimiento >= data.fecha_ingreso, {
            path: ['fecha_vencimiento'],
            message: 'La fecha de vencimiento debe ser posterior o igual a la fecha de ingreso.',
        }),
);

const initialValues = {
    codigo_lote: props.lote?.codigo_lote ?? '',
    fecha_ingreso: props.lote?.fecha_ingreso ?? today,
    fecha_vencimiento: props.lote?.fecha_vencimiento ?? today,
};

const veeForm = useVeeForm({ validationSchema: schema, initialValues });
const form = useInertiaForm(initialValues);

const submit = veeForm.handleSubmit((values) => {
    form.clearErrors();
    Object.assign(form, values);

    if (isEditing.value && props.lote) {
        form.patch(route('propietario.lotes.update', props.lote.id), {
            preserveScroll: true,
            onError: (errors) => veeForm.setErrors(errors),
        });

        return;
    }

    form.post(route('propietario.lotes.store'), {
        preserveScroll: true,
        onError: (errors) => veeForm.setErrors(errors),
    });
});
</script>

<template>
    <Head :title="title" />
    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <section class="space-y-2">
                    <p class="text-sm font-medium text-muted-foreground">Gestion de lotes</p>
                    <h1 class="text-3xl font-semibold tracking-tight">{{ title }}</h1>
                    <p class="max-w-2xl text-muted-foreground">Los lotes se crean una vez y luego se seleccionan desde inventario.</p>
                </section>
                <Button as-child variant="outline">
                    <Link :href="route('propietario.lotes.index')"><ArrowLeft class="size-4" /> Volver</Link>
                </Button>
            </div>

            <div class="max-w-2xl space-y-6">
                <section class="space-y-1">
                    <h2 class="text-xl font-semibold tracking-tight">Datos del lote</h2>
                    <p class="text-sm text-muted-foreground">Registra codigo, fecha de ingreso y vencimiento.</p>
                </section>

                <form class="grid gap-6" @submit="submit">
                    <FormField v-slot="{ componentField }" name="codigo_lote">
                        <FormItem>
                            <FormLabel>Codigo de lote</FormLabel>
                            <FormControl>
                                <Input v-bind="componentField" autocomplete="off" placeholder="Ej. LOTE-2026-001" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <div class="grid gap-4 md:grid-cols-2">
                        <FormField v-slot="{ componentField }" name="fecha_ingreso">
                            <FormItem>
                                <FormLabel>Fecha de ingreso</FormLabel>
                                <FormControl>
                                    <DateInput v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <FormField v-slot="{ componentField }" name="fecha_vencimiento">
                            <FormItem>
                                <FormLabel>Fecha de vencimiento</FormLabel>
                                <FormControl>
                                    <DateInput v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </div>

                    <div class="flex items-center gap-3">
                        <Button :disabled="form.processing">
                            <Save class="size-4" />
                            {{ isEditing ? 'Guardar cambios' : 'Crear lote' }}
                        </Button>
                        <Button as-child variant="ghost" type="button">
                            <Link :href="route('propietario.lotes.index')">Cancelar</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
