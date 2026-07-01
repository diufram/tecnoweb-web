<script setup lang="ts">
import EmptyState from '@/components/shared/EmptyState.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useFormatters } from '@/composables/useFormatters';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Loader2, PackageSearch, ShoppingBasket, ShoppingCart } from 'lucide-vue-next';
import { ref } from 'vue';

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

const quantities = ref<Record<number, number>>({});
const targetId = ref<number | null>(null);
const toastMessage = ref('');
let toastTimeout: number | undefined;
const form = useForm({
    id_inventario: null as number | null,
    cantidad: 1,
});

const { money, date } = useFormatters();

const quantityFor = (item: CatalogoItem) => quantities.value[item.id] ?? 1;

const showToast = (message: string) => {
    toastMessage.value = message;

    if (toastTimeout) {
        window.clearTimeout(toastTimeout);
    }

    toastTimeout = window.setTimeout(() => {
        toastMessage.value = '';
    }, 3000);
};

const addToCart = (item: CatalogoItem) => {
    targetId.value = item.id;
    form.clearErrors();
    form.id_inventario = item.id;
    form.cantidad = quantityFor(item);
    form.post(route('cliente.carrito.store'), {
        preserveScroll: true,
        onSuccess: () => {
            quantities.value[item.id] = 1;
            showToast(`${item.producto.nombre_comercial} agregado al carrito.`);
        },
        onFinish: () => {
            targetId.value = null;
        },
    });
};
</script>

<template>
    <Head title="Catálogo" />

    <CustomerLayout>
        <PageHeader
            eyebrow="Catálogo"
            title="Medicamentos disponibles"
            description="Agrega productos al carrito y finaliza la compra eligiendo contado o cuotas."
            :icon="PackageSearch"
        >
            <div class="flex justify-end">
                <Button as-child class="rounded-full">
                    <Link :href="route('cliente.carrito')">
                        <ShoppingCart class="size-4" />
                        Ver carrito
                    </Link>
                </Button>
            </div>
        </PageHeader>

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

                    <div class="space-y-3">
                        <label class="grid gap-2 text-sm font-medium">
                            Cantidad
                            <Input v-model.number="quantities[item.id]" type="number" min="1" :max="item.cantidad_disponible" placeholder="1" />
                        </label>
                        <p v-if="targetId === item.id && form.errors.cantidad" class="text-sm text-destructive">{{ form.errors.cantidad }}</p>
                        <Button class="w-full rounded-full" :disabled="form.processing && targetId === item.id" @click="addToCart(item)">
                            <Loader2 v-if="form.processing && targetId === item.id" class="size-4 animate-spin" />
                            <ShoppingBasket v-else class="size-4" />
                            {{ form.processing && targetId === item.id ? 'Agregando...' : 'Agregar al carrito' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </section>

        <EmptyState
            v-else
            :icon="PackageSearch"
            title="No hay productos disponibles"
            description="Cuando exista inventario con stock, aparecerá aquí."
        />

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-2 opacity-0"
        >
            <div v-if="toastMessage" class="fixed right-6 top-6 z-50 max-w-sm rounded-xl border bg-background p-4 text-sm font-medium shadow-lg">
                <p>{{ toastMessage }}</p>
                <Link :href="route('cliente.carrito')" class="mt-1 inline-block text-primary underline-offset-4 hover:underline">Ver carrito</Link>
            </div>
        </Transition>
    </CustomerLayout>
</template>
