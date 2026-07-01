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

interface Cliente {
    id_usuario: number;
    linea_credito: string;
    nit_facturacion: string;
    saldo_actual: string;
    usuario: {
        ci_nit: string;
        nombre: string;
        email: string;
        direccion: string;
        telefono: string;
    };
}

const props = defineProps<{
    mode: 'create' | 'edit';
    cliente: Cliente | null;
}>();

const isEditing = computed(() => props.mode === 'edit');
const title = computed(() => (isEditing.value ? 'Editar cliente' : 'Crear cliente'));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clientes', href: route('propietario.clientes.index') },
    { title: title.value, href: '#' },
];

const clienteSchema = toTypedSchema(
    z
        .object({
            ci_nit: z.string().trim().min(1, 'El CI/NIT es obligatorio.').max(50, 'El CI/NIT no debe superar 50 caracteres.'),
            nombre: z.string().trim().min(3, 'El nombre debe tener al menos 3 caracteres.').max(255, 'El nombre no debe superar 255 caracteres.'),
            email: z.string().trim().min(1, 'El correo electronico es obligatorio.').email('Ingresa un correo electronico valido.').max(255),
            direccion: z.string().trim().min(3, 'La direccion debe tener al menos 3 caracteres.').max(255),
            telefono: z.string().trim().min(5, 'El telefono debe tener al menos 5 caracteres.').max(50),
            password: z.string().optional().default(''),
            password_confirmation: z.string().optional().default(''),
            linea_credito: z.coerce.number().min(0, 'La linea de credito no puede ser negativa.').max(9999999999.99),
            nit_facturacion: z.string().trim().min(1, 'El NIT de facturacion es obligatorio.').max(50),
            saldo_actual: z.coerce.number().min(0, 'El saldo actual no puede ser negativo.').max(9999999999.99),
        })
        .superRefine((data, ctx) => {
            if (!isEditing.value && !data.password) {
                ctx.addIssue({ code: z.ZodIssueCode.custom, path: ['password'], message: 'La contrasena es obligatoria.' });
            }

            if (data.password && data.password.length < 8) {
                ctx.addIssue({ code: z.ZodIssueCode.custom, path: ['password'], message: 'La contrasena debe tener al menos 8 caracteres.' });
            }

            if (data.password && data.password !== data.password_confirmation) {
                ctx.addIssue({ code: z.ZodIssueCode.custom, path: ['password_confirmation'], message: 'La confirmacion de contrasena no coincide.' });
            }
        }),
);

const initialValues = {
    ci_nit: props.cliente?.usuario.ci_nit ?? '',
    nombre: props.cliente?.usuario.nombre ?? '',
    email: props.cliente?.usuario.email ?? '',
    direccion: props.cliente?.usuario.direccion ?? '',
    telefono: props.cliente?.usuario.telefono ?? '',
    password: '',
    password_confirmation: '',
    linea_credito: Number(props.cliente?.linea_credito ?? 0),
    nit_facturacion: props.cliente?.nit_facturacion ?? '',
    saldo_actual: Number(props.cliente?.saldo_actual ?? 0),
};

const veeForm = useVeeForm({ validationSchema: clienteSchema, initialValues });
const form = useInertiaForm(initialValues);

const submit = veeForm.handleSubmit((values) => {
    form.clearErrors();
    Object.assign(form, values);

    if (isEditing.value && props.cliente) {
        form.patch(route('propietario.clientes.update', props.cliente.id_usuario), {
            preserveScroll: true,
            onError: (errors) => veeForm.setErrors(errors),
        });

        return;
    }

    form.post(route('propietario.clientes.store'), {
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
                    <p class="text-sm font-medium text-muted-foreground">Gestion de clientes</p>
                    <h1 class="text-3xl font-semibold tracking-tight">{{ title }}</h1>
                    <p class="max-w-2xl text-muted-foreground">Registra datos de usuario, facturacion y credito del cliente.</p>
                </section>

                <Button as-child variant="outline">
                    <Link :href="route('propietario.clientes.index')"><ArrowLeft class="size-4" /> Volver</Link>
                </Button>
            </div>

            <div class="max-w-4xl space-y-6">
                <section class="space-y-1">
                    <h2 class="text-xl font-semibold tracking-tight">Datos del cliente</h2>
                    <p class="text-sm text-muted-foreground">La contrasena es obligatoria al crear y opcional al editar.</p>
                </section>

                <form class="grid gap-6" @submit="submit">
                        <div class="grid gap-4 md:grid-cols-2">
                            <FormField v-slot="{ componentField }" name="ci_nit"><FormItem><FormLabel>CI/NIT</FormLabel><FormControl><Input v-bind="componentField" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="nombre"><FormItem><FormLabel>Nombre</FormLabel><FormControl><Input v-bind="componentField" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="email"><FormItem><FormLabel>Correo electronico</FormLabel><FormControl><Input v-bind="componentField" type="email" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="telefono"><FormItem><FormLabel>Telefono</FormLabel><FormControl><Input v-bind="componentField" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="direccion"><FormItem class="md:col-span-2"><FormLabel>Direccion</FormLabel><FormControl><Input v-bind="componentField" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="password"><FormItem><FormLabel>Contrasena</FormLabel><FormControl><Input v-bind="componentField" type="password" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="password_confirmation"><FormItem><FormLabel>Confirmar contrasena</FormLabel><FormControl><Input v-bind="componentField" type="password" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="linea_credito"><FormItem><FormLabel>Linea de credito</FormLabel><FormControl><Input v-bind="componentField" min="0" step="0.01" type="number" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="saldo_actual"><FormItem><FormLabel>Saldo actual</FormLabel><FormControl><Input v-bind="componentField" min="0" step="0.01" type="number" /></FormControl><FormMessage /></FormItem></FormField>
                            <FormField v-slot="{ componentField }" name="nit_facturacion"><FormItem class="md:col-span-2"><FormLabel>NIT de facturacion</FormLabel><FormControl><Input v-bind="componentField" /></FormControl><FormMessage /></FormItem></FormField>
                        </div>

                        <div class="flex items-center gap-3">
                            <Button :disabled="form.processing"><Save class="size-4" /> {{ isEditing ? 'Guardar cambios' : 'Crear cliente' }}</Button>
                            <Button as-child variant="ghost" type="button"><Link :href="route('propietario.clientes.index')">Cancelar</Link></Button>
                        </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
