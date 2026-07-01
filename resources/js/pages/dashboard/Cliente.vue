<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CreditCard, PackageSearch, ReceiptText } from 'lucide-vue-next';

defineProps<{
    stats: {
        lineaCredito: string | number;
        saldoActual: string | number;
        creditoDisponible: string | number;
    };
}>();

const money = (value: string | number) =>
    new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(Number(value));
</script>

<template>
    <Head title="Dashboard cliente" />

    <CustomerLayout>
        <section class="overflow-hidden rounded-3xl border bg-card p-6 shadow-sm md:p-10">
            <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                <div class="space-y-4">
                    <p class="text-sm font-medium text-primary">Portal cliente</p>
                    <h1 class="text-3xl font-semibold tracking-tight md:text-5xl">Compra medicamentos y controla tus pagos</h1>
                    <p class="max-w-2xl text-muted-foreground">
                        Explora el catálogo, revisa tu línea de crédito y mantén al día tus cuotas desde un panel pensado para clientes.
                    </p>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <Button as-child><Link :href="route('cliente.catalogo')">Ver catálogo</Link></Button>
                        <Button as-child variant="outline"><Link :href="route('cliente.carrito')">Carrito</Link></Button>
                        <Button as-child variant="outline"><Link :href="route('cliente.compras')">Mis compras</Link></Button>
                        <Button as-child variant="outline"><Link :href="route('cliente.pagos')">Pagos</Link></Button>
                    </div>
                </div>

                <Card class="bg-primary text-primary-foreground">
                    <CardHeader>
                        <CardTitle>Crédito disponible</CardTitle>
                        <CardDescription class="text-primary-foreground/75">Línea asignada menos saldo actual</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-4xl font-bold">{{ money(stats.creditoDisponible) }}</div>
                    </CardContent>
                </Card>
            </div>
        </section>

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
