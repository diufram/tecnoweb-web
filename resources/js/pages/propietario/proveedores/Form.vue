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

interface Proveedor {
    id_usuario: number;
    empresa: string;
    usuario: { ci_nit: string; nombre: string; email: string; direccion: string; telefono: string };
}

const props = defineProps<{ mode: 'create' | 'edit'; proveedor: Proveedor | null }>();

const isEditing = computed(() => props.mode === 'edit');
const title = computed(() => (isEditing.value ? 'Editar proveedor' : 'Crear proveedor'));
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Proveedores', href: route('propietario.proveedores.index') }, { title: title.value, href: '#' }];

const proveedorSchema = toTypedSchema(
    z
        .object({
            ci_nit: z.string().trim().min(1, 'El CI/NIT es obligatorio.').max(50),
            nombre: z.string().trim().min(3, 'El nombre debe tener al menos 3 caracteres.').max(255),
            email: z.string().trim().min(1, 'El correo electronico es obligatorio.').email('Ingresa un correo electronico valido.').max(255),
            direccion: z.string().trim().min(3, 'La direccion debe tener al menos 3 caracteres.').max(255),
            telefono: z.string().trim().min(5, 'El telefono debe tener al menos 5 caracteres.').max(50),
            password: z.string().optional().default(''),
            password_confirmation: z.string().optional().default(''),
            empresa: z.string().trim().min(3, 'La empresa debe tener al menos 3 caracteres.').max(255),
        })
        .superRefine((data, ctx) => {
            if (!isEditing.value && !data.password) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ['password'], message: 'La contrasena es obligatoria.' });
            if (data.password && data.password.length < 8) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ['password'], message: 'La contrasena debe tener al menos 8 caracteres.' });
            if (data.password && data.password !== data.password_confirmation) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ['password_confirmation'], message: 'La confirmacion de contrasena no coincide.' });
        }),
);

const initialValues = {
    ci_nit: props.proveedor?.usuario.ci_nit ?? '',
    nombre: props.proveedor?.usuario.nombre ?? '',
    email: props.proveedor?.usuario.email ?? '',
    direccion: props.proveedor?.usuario.direccion ?? '',
    telefono: props.proveedor?.usuario.telefono ?? '',
    password: '',
    password_confirmation: '',
    empresa: props.proveedor?.empresa ?? '',
};

const veeForm = useVeeForm({ validationSchema: proveedorSchema, initialValues });
const form = useInertiaForm(initialValues);

const submit = veeForm.handleSubmit((values) => {
    form.clearErrors();
    Object.assign(form, values);

    if (isEditing.value && props.proveedor) {
        form.patch(route('propietario.proveedores.update', props.proveedor.id_usuario), { preserveScroll: true, onError: (errors) => veeForm.setErrors(errors) });
        return;
    }

    form.post(route('propietario.proveedores.store'), { preserveScroll: true, onError: (errors) => veeForm.setErrors(errors) });
});
</script>

<template>
    <Head :title="title" />
    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <section class="space-y-2">
                    <p class="text-sm font-medium text-muted-foreground">Gestion de proveedores</p>
                    <h1 class="text-3xl font-semibold tracking-tight">{{ title }}</h1>
                    <p class="max-w-2xl text-muted-foreground">Registra datos de empresa y usuario proveedor.</p>
                </section>
                <Button as-child variant="outline"><Link :href="route('propietario.proveedores.index')"><ArrowLeft class="size-4" /> Volver</Link></Button>
            </div>

            <div class="max-w-4xl space-y-6">
                <section class="space-y-1">
                    <h2 class="text-xl font-semibold tracking-tight">Datos del proveedor</h2>
                    <p class="text-sm text-muted-foreground">La contrasena es obligatoria al crear y opcional al editar.</p>
                </section>

                <form class="grid gap-6" @submit="submit">
                        <div class="grid gap-4 md:grid-cols-2">
                            <FormField v-slot="{ componentField }" name="empresa"><FormItem class="md:col-span-2"><FormLabel>Empresa</FormLabel><FormControl><Input v-bind="componentField" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="ci_nit"><FormItem><FormLabel>CI/NIT</FormLabel><FormControl><Input v-bind="componentField" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="nombre"><FormItem><FormLabel>Responsable</FormLabel><FormControl><Input v-bind="componentField" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="email"><FormItem><FormLabel>Correo electronico</FormLabel><FormControl><Input v-bind="componentField" type="email" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="telefono"><FormItem><FormLabel>Telefono</FormLabel><FormControl><Input v-bind="componentField" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="direccion"><FormItem class="md:col-span-2"><FormLabel>Direccion</FormLabel><FormControl><Input v-bind="componentField" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="password"><FormItem><FormLabel>Contrasena</FormLabel><FormControl><Input v-bind="componentField" type="password" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="password_confirmation"><FormItem><FormLabel>Confirmar contrasena</FormLabel><FormControl><Input v-bind="componentField" type="password" /></FormControl><FormMessage /></FormItem></FormField>
                        </div>

                        <div class="flex items-center gap-3">
                            <Button :disabled="form.processing"><Save class="size-4" /> {{ isEditing ? 'Guardar cambios' : 'Crear proveedor' }}</Button>
                            <Button as-child variant="ghost" type="button"><Link :href="route('propietario.proveedores.index')">Cancelar</Link></Button>
                        </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
