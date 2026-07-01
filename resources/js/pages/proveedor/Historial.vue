<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import PageToolbar from '@/components/shared/PageToolbar.vue';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import ComprasList from '@/pages/proveedor/ComprasList.vue';
import ProveedorSectionNav from '@/pages/proveedor/ProveedorSectionNav.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { History } from 'lucide-vue-next';

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

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Historial', href: '#' }];
</script>

<template>
    <Head title="Historial" />

    <AdminLayout actor="proveedor" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader title="Historial" description="Todas las compras en las que participaste, ordenadas por fecha." :icon="History" />

            <ProveedorSectionNav current="historial" />

            <PageToolbar :total="compras.length" />

            <ComprasList :compras="compras" />
        </div>
    </AdminLayout>
</template>
