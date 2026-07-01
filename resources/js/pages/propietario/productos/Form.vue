<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { toTypedSchema } from '@vee-validate/zod';
import { Head, Link, useForm as useInertiaForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { computed } from 'vue';
import * as z from 'zod';

interface Producto {
    id: number;
    nombre_comercial: string;
    stock_actual: number;
}

const props = defineProps<{
    mode: 'create' | 'edit';
    producto: Producto | null;
}>();

const isEditing = computed(() => props.mode === 'edit');
const title = computed(() => (isEditing.value ? 'Editar producto' : 'Crear producto'));

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Productos',
        href: route('propietario.productos.index'),
    },
    {
        title: title.value,
        href: '#',
    },
];

const productoSchema = toTypedSchema(
    z.object({
        nombre_comercial: z
            .string({ required_error: 'El nombre comercial es obligatorio.' })
            .trim()
            .min(3, 'El nombre comercial debe tener al menos 3 caracteres.')
            .max(255, 'El nombre comercial no debe superar 255 caracteres.'),
        stock_actual: z.coerce
            .number({ required_error: 'El stock actual es obligatorio.', invalid_type_error: 'El stock actual es obligatorio.' })
            .int('El stock actual debe ser un numero entero.')
            .min(0, 'El stock actual no puede ser negativo.')
            .max(1000000, 'El stock actual no puede superar 1.000.000 unidades.'),
    }),
);

const veeForm = useVeeForm({
    validationSchema: productoSchema,
    initialValues: {
        nombre_comercial: props.producto?.nombre_comercial ?? '',
        stock_actual: props.producto?.stock_actual ?? 0,
    },
});

const form = useInertiaForm({
    nombre_comercial: props.producto?.nombre_comercial ?? '',
    stock_actual: props.producto?.stock_actual ?? 0,
});

const submit = veeForm.handleSubmit((values) => {
    form.clearErrors();
    form.nombre_comercial = values.nombre_comercial;
    form.stock_actual = values.stock_actual;

    if (isEditing.value && props.producto) {
        form.patch(route('propietario.productos.update', props.producto.id), {
            preserveScroll: true,
            onError: (errors) => veeForm.setErrors(errors),
        });

        return;
    }

    form.post(route('propietario.productos.store'), {
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
                    <p class="text-sm font-medium text-muted-foreground">Gestion de productos</p>
                    <h1 class="text-3xl font-semibold tracking-tight">{{ title }}</h1>
                    <p class="max-w-2xl text-muted-foreground">Completa los datos del producto. Las validaciones se aplican antes de enviar y tambien en el servidor.</p>
                </section>

                <Button as-child variant="outline">
                    <Link :href="route('propietario.productos.index')">
                        <ArrowLeft class="size-4" />
                        Volver
                    </Link>
                </Button>
            </div>

            <div class="max-w-2xl space-y-6">
                <section class="space-y-1">
                    <h2 class="text-xl font-semibold tracking-tight">Datos del producto</h2>
                    <p class="text-sm text-muted-foreground">Estos datos se usaran en inventario y compras.</p>
                </section>

                <form class="space-y-6" @submit="submit">
                    <FormField v-slot="{ componentField }" name="nombre_comercial">
                        <FormItem>
                            <FormLabel>Nombre comercial</FormLabel>
                            <FormControl>
                                <Input v-bind="componentField" autocomplete="off" maxlength="255" placeholder="Ej. Paracetamol 500mg" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="stock_actual">
                        <FormItem>
                            <FormLabel>Stock actual</FormLabel>
                            <FormControl>
                                <Input v-bind="componentField" inputmode="numeric" min="0" max="1000000" placeholder="0" type="number" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <div class="flex items-center gap-3">
                        <Button :disabled="form.processing">
                            <Save class="size-4" />
                            {{ isEditing ? 'Guardar cambios' : 'Crear producto' }}
                        </Button>

                        <Button as-child variant="ghost" type="button">
                            <Link :href="route('propietario.productos.index')">Cancelar</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
