<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import PageToolbar from '@/components/shared/PageToolbar.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import ComprasList from '@/pages/proveedor/ComprasList.vue';
import ProveedorSectionNav from '@/pages/proveedor/ProveedorSectionNav.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { FileText } from 'lucide-vue-next';

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
    proveedor: {
        empresa: string | null;
        usuario: string | null;
    };
    detalles: CompraDetalle[];
}

defineProps<{
    compras: Compra[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Solicitudes', href: '#' }];
</script>

<template>
    <Head title="Solicitudes" />

    <AdminLayout actor="proveedor" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader
                title="Solicitudes"
                description="Revisa las solicitudes de compra enviadas por el propietario y responde aceptando, rechazando o proponiendo una contraoferta."
                :icon="FileText"
            />

            <ProveedorSectionNav current="solicitudes" />

            <PageToolbar :total="compras.length" />

            <ComprasList
                :compras="compras"
                empty-title="No hay solicitudes pendientes"
                empty-description="Las solicitudes del propietario aparecera aqui."
            >
                <template #empty-action>
                    <Button as-child variant="outline" class="rounded-full">
                        <Link :href="route('proveedor.historial')">Ver historial completo</Link>
                    </Button>
                </template>
            </ComprasList>
        </div>
    </AdminLayout>
</template>
