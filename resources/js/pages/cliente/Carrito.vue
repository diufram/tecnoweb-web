<script setup lang="ts">
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useFormatters } from '@/composables/useFormatters';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { CreditCard, Loader2, PackageSearch, ShoppingCart, Trash2 } from 'lucide-vue-next';
import { computed, reactive } from 'vue';

interface CarritoItem {
    id: number;
    cantidad: number;
    cantidad_disponible: number;
    precio_unitario: string | number;
    subtotal: string | number;
    producto: {
        id: number;
        nombre_comercial: string;
    };
    lote: {
        id: number;
        fecha_vencimiento: string;
    };
}

const props = defineProps<{
    items: CarritoItem[];
    total: string | number;
    credito: {
        linea_credito: string | number;
        saldo_actual: string | number;
        disponible: string | number;
    };
}>();

const { money, date } = useFormatters();

const quantities = reactive<Record<number, number>>(Object.fromEntries(props.items.map((item) => [item.id, item.cantidad])));

const checkoutForm = useForm({
    tipo_pago: 'CONTADO',
    cuotas: 3,
});

const totalNumber = computed(() => Number(props.total));
const creditoDisponible = computed(() => Number(props.credito.disponible));
const creditoInsuficiente = computed(() => checkoutForm.tipo_pago === 'CREDITO' && totalNumber.value > creditoDisponible.value);

const updateItem = (item: CarritoItem) => {
    router.patch(route('cliente.carrito.update', item.id), { cantidad: quantities[item.id] }, { preserveScroll: true });
};

const removeItem = (item: CarritoItem) => {
    router.delete(route('cliente.carrito.destroy', item.id), { preserveScroll: true });
};

const checkout = () => {
    checkoutForm.post(route('cliente.carrito.checkout'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Carrito" />

    <CustomerLayout>
        <PageHeader
            eyebrow="Carrito"
            title="Finaliza tu compra"
            description="Agrega varios productos, ajusta cantidades y elige si pagarás al contado o en cuotas."
            :icon="ShoppingCart"
        />

        <div v-if="items.length" class="grid gap-6 lg:grid-cols-[1fr_24rem]">
            <section class="space-y-4">
                <Card v-for="item in items" :key="item.id">
                    <CardHeader class="gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <CardTitle>{{ item.producto.nombre_comercial }}</CardTitle>
                            <CardDescription>Lote #{{ item.lote.id }} · Vence {{ date(item.lote.fecha_vencimiento) }}</CardDescription>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-sm text-muted-foreground">Subtotal</p>
                            <p class="text-2xl font-semibold">{{ money(item.subtotal) }}</p>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-end">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-muted p-4">
                                <p class="text-sm text-muted-foreground">Precio</p>
                                <p class="text-lg font-semibold">{{ money(item.precio_unitario) }}</p>
                            </div>
                            <div class="rounded-2xl bg-muted p-4">
                                <p class="text-sm text-muted-foreground">Disponible</p>
                                <p class="text-lg font-semibold">{{ item.cantidad_disponible }}</p>
                            </div>
                            <label class="grid gap-2 text-sm font-medium">
                                Cantidad
                                <Input v-model.number="quantities[item.id]" type="number" min="1" :max="item.cantidad_disponible" />
                            </label>
                        </div>
                        <div class="flex gap-2 sm:justify-end">
                            <Button type="button" variant="outline" class="rounded-full" @click="updateItem(item)">Actualizar</Button>
                            <Button type="button" variant="ghost" size="icon" aria-label="Quitar producto" @click="removeItem(item)">
                                <Trash2 class="size-4 text-destructive" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Button as-child variant="outline" class="rounded-full">
                    <Link :href="route('cliente.catalogo')">
                        <PackageSearch class="size-4" />
                        Seguir comprando
                    </Link>
                </Button>
            </section>

            <Card class="h-fit lg:sticky lg:top-24">
                <CardHeader>
                    <CardTitle>Resumen</CardTitle>
                    <CardDescription>{{ items.length }} producto(s) en el carrito</CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="space-y-5" @submit.prevent="checkout">
                        <div class="rounded-2xl bg-muted p-4">
                            <p class="text-sm text-muted-foreground">Total</p>
                            <p class="text-3xl font-semibold">{{ money(total) }}</p>
                        </div>

                        <label class="grid gap-2 text-sm font-medium">
                            Tipo de pago
                            <select
                                v-model="checkoutForm.tipo_pago"
                                class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            >
                                <option value="CONTADO">Contado</option>
                                <option value="CREDITO">Crédito en cuotas</option>
                            </select>
                            <span v-if="checkoutForm.errors.tipo_pago" class="text-sm font-normal text-destructive">{{
                                checkoutForm.errors.tipo_pago
                            }}</span>
                        </label>

                        <label v-if="checkoutForm.tipo_pago === 'CREDITO'" class="grid gap-2 text-sm font-medium">
                            Número de cuotas
                            <select
                                v-model.number="checkoutForm.cuotas"
                                class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            >
                                <option :value="1">1 cuota</option>
                                <option :value="2">2 cuotas</option>
                                <option :value="3">3 cuotas</option>
                                <option :value="6">6 cuotas</option>
                                <option :value="12">12 cuotas</option>
                            </select>
                            <span v-if="checkoutForm.errors.cuotas" class="text-sm font-normal text-destructive">{{
                                checkoutForm.errors.cuotas
                            }}</span>
                        </label>

                        <div v-if="checkoutForm.tipo_pago === 'CREDITO'" class="rounded-2xl border p-4 text-sm">
                            <p class="font-medium">Crédito disponible: {{ money(credito.disponible) }}</p>
                            <p class="text-muted-foreground">
                                Línea: {{ money(credito.linea_credito) }} · Saldo actual: {{ money(credito.saldo_actual) }}
                            </p>
                            <p v-if="creditoInsuficiente" class="mt-2 font-medium text-destructive">El total supera tu crédito disponible.</p>
                        </div>

                        <p v-if="checkoutForm.errors.carrito" class="text-sm font-medium text-destructive">{{ checkoutForm.errors.carrito }}</p>
                        <p v-if="checkoutForm.errors.pago" class="text-sm font-medium text-destructive">{{ checkoutForm.errors.pago }}</p>

                        <Button class="w-full rounded-full" type="submit" :disabled="checkoutForm.processing || creditoInsuficiente">
                            <Loader2 v-if="checkoutForm.processing" class="size-4 animate-spin" />
                            <CreditCard v-else class="size-4" />
                            {{ checkoutForm.processing ? 'Registrando...' : 'Confirmar compra' }}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>

        <EmptyState
            v-else
            :icon="ShoppingCart"
            title="Tu carrito está vacío"
            description="Agrega productos desde el catálogo para iniciar una compra."
        >
            <Button as-child variant="outline" class="rounded-full">
                <Link :href="route('cliente.catalogo')">Ver catálogo</Link>
            </Button>
        </EmptyState>
    </CustomerLayout>
</template>
