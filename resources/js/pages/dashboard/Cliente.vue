<script setup lang="ts">
import PageHeader from '@/components/shared/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useFormatters } from '@/composables/useFormatters';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CreditCard, PackageSearch, ReceiptText, WalletCards } from 'lucide-vue-next';

defineProps<{
    stats: {
        lineaCredito: string | number;
        saldoActual: string | number;
        creditoDisponible: string | number;
    };
}>();

const { money } = useFormatters();
</script>

<template>
    <Head title="Dashboard cliente" />

    <CustomerLayout>
        <PageHeader
            eyebrow="Portal cliente"
            title="Compra medicamentos y controla tus pagos"
            description="Explora el catálogo, revisa tu línea de crédito y mantén al día tus cuotas desde un panel pensado para clientes."
            :icon="WalletCards"
        >
            <div class="grid gap-4 md:grid-cols-2">
                <Card class="bg-primary text-primary-foreground">
                    <CardHeader>
                        <CardTitle>Crédito disponible</CardTitle>
                        <CardDescription class="text-primary-foreground/75">Línea asignada menos saldo actual</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-4xl font-bold">{{ money(stats.creditoDisponible) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Acciones rapidas</CardTitle>
                        <CardDescription>Entra directo a tus apartados.</CardDescription>
                    </CardHeader>
                    <CardContent class="flex flex-wrap gap-2">
                        <Button as-child class="rounded-full"><Link :href="route('cliente.catalogo')">Ver catálogo</Link></Button>
                        <Button as-child variant="outline" class="rounded-full"><Link :href="route('cliente.carrito')">Carrito</Link></Button>
                        <Button as-child variant="outline" class="rounded-full"><Link :href="route('cliente.compras')">Mis compras</Link></Button>
                        <Button as-child variant="outline" class="rounded-full"><Link :href="route('cliente.pagos')">Pagos</Link></Button>
                    </CardContent>
                </Card>
            </div>
        </PageHeader>

        <section class="grid gap-4 md:grid-cols-3">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Línea de crédito</CardTitle>
                    <CreditCard class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ money(stats.lineaCredito) }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Saldo actual</CardTitle>
                    <ReceiptText class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ money(stats.saldoActual) }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Catálogo</CardTitle>
                    <PackageSearch class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">Disponible</div>
                    <Button as-child variant="link" class="h-auto px-0">
                        <Link :href="route('cliente.catalogo')">Explorar productos</Link>
                    </Button>
                </CardContent>
            </Card>
        </section>
    </CustomerLayout>
</template>
