<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { Head } from '@inertiajs/vue3';
import { PackageSearch, ShoppingBasket } from 'lucide-vue-next';

interface CatalogoItem {
    id: number;
    cantidad_disponible: number;
    precio_referencial: string | number;
    producto: {
        id: number;
        nombre_comercial: string;
        stock_actual: number;
    };
    lote: {
        id: number;
        fecha_vencimiento: string;
    };
}

defineProps<{
    catalogo: CatalogoItem[];
}>();

const money = (value: string | number) =>
    new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(Number(value));

const date = (value: string) => new Date(value).toLocaleDateString('es-BO');
</script>

<template>
    <Head title="Catálogo" />

    <CustomerLayout>
        <section class="rounded-3xl border bg-card p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-primary">Catálogo</p>
                    <h1 class="text-3xl font-semibold tracking-tight">Medicamentos disponibles</h1>
                    <p class="max-w-2xl text-muted-foreground">Consulta los productos con stock disponible y precio referencial por lote.</p>
                </div>
                <PackageSearch class="size-12 text-muted-foreground" />
            </div>
        </section>

        <section v-if="catalogo.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card v-for="item in catalogo" :key="item.id" class="overflow-hidden">
                <CardHeader>
                    <CardTitle>{{ item.producto.nombre_comercial }}</CardTitle>
                    <CardDescription>Lote #{{ item.lote.id }} · Vence {{ date(item.lote.fecha_vencimiento) }}</CardDescription>
                </CardHeader>
                <CardContent class="space-y-5">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl bg-muted p-3">
                            <p class="text-muted-foreground">Disponible</p>
                            <p class="text-2xl font-semibold">{{ item.cantidad_disponible }}</p>
                        </div>
                        <div class="rounded-xl bg-muted p-3">
                            <p class="text-muted-foreground">Precio ref.</p>
                            <p class="text-2xl font-semibold">{{ money(item.precio_referencial) }}</p>
                        </div>
                    </div>

                    <Button class="w-full" disabled>
                        <ShoppingBasket class="size-4" />
                        Compra pendiente de implementar
                    </Button>
                </CardContent>
            </Card>
        </section>

        <div v-else class="flex min-h-64 flex-col items-center justify-center gap-3 rounded-3xl border border-dashed text-center">
            <PackageSearch class="size-12 text-muted-foreground" />
            <div>
                <p class="font-medium">No hay productos disponibles</p>
                <p class="text-sm text-muted-foreground">Cuando exista inventario con stock, aparecerá aquí.</p>
            </div>
        </div>
    </CustomerLayout>
</template>
