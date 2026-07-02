<script setup lang="ts">
import DataTable from '@/components/shared/DataTable.vue';
import PageHeader from '@/components/shared/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { BarChart3, FileText, Filter, Printer } from 'lucide-vue-next';
import { ref } from 'vue';

interface ReporteRow {
    categoria: string;
    descripcion: string;
    total: string;
}

const props = defineProps<{
    reporteRows: ReporteRow[];
    filters: {
        tipo: string;
        desde?: string;
        hasta?: string;
        umbral?: string | number;
        dias?: string | number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reportes',
        href: route('propietario.reportes.index'),
    },
];

const selectedTipo = ref(props.filters.tipo || 'general');
const desdeVal = ref(props.filters.desde || '');
const hastaVal = ref(props.filters.hasta || '');
const umbralVal = ref(props.filters.umbral || 10);
const diasVal = ref(props.filters.dias || 30);

const isVentas = (tipo: string) => tipo === 'ventas' || tipo === 'general';
const isStockMinimo = (tipo: string) => tipo === 'stock_minimo';
const isPorVencer = (tipo: string) => tipo === 'por_vencer';

const applyFilters = () => {
    router.get(
        route('propietario.reportes.index'),
        {
            tipo: selectedTipo.value,
            desde: isVentas(selectedTipo.value) ? desdeVal.value : undefined,
            hasta: isVentas(selectedTipo.value) ? hastaVal.value : undefined,
            umbral: isStockMinimo(selectedTipo.value) ? umbralVal.value : undefined,
            dias: isPorVencer(selectedTipo.value) ? diasVal.value : undefined,
        },
        { preserveState: true }
    );
};

const handlePrint = () => {
    window.print();
};
</script>

<template>
    <Head title="Reportes" />

    <AdminLayout actor="propietario" :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6 print:p-0 print:border-none print:shadow-none">
            
            <!-- Page Header (hidden during print) -->
            <PageHeader
                class="print:hidden"
                eyebrow="Consolidado"
                title="Reportes de Sistema"
                description="Genera y visualiza reportes específicos para ventas, compras, inventario, usuarios y más."
                :icon="BarChart3"
            />

            <!-- Filters Section (hidden during print) -->
            <Card class="border-border/70 shadow-sm print:hidden">
                <CardHeader>
                    <CardTitle class="text-lg flex items-center gap-2">
                        <Filter class="size-5 text-primary" />
                        Filtros de Reporte
                    </CardTitle>
                    <CardDescription>Selecciona el tipo de reporte y especifica los parámetros requeridos.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="applyFilters" class="grid gap-6 md:grid-cols-4 items-end">
                        
                        <!-- Tipo de Reporte -->
                        <div class="grid gap-2">
                            <Label for="tipo">Tipo de Reporte</Label>
                            <Select v-model="selectedTipo">
                                <SelectTrigger id="tipo" class="w-full">
                                    <SelectValue placeholder="Selecciona un reporte" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="general">General (Todos)</SelectItem>
                                    <SelectItem value="ventas">Ventas</SelectItem>
                                    <SelectItem value="compras">Compras</SelectItem>
                                    <SelectItem value="inventario">Inventario</SelectItem>
                                    <SelectItem value="usuarios">Usuarios</SelectItem>
                                    <SelectItem value="productos">Productos</SelectItem>
                                    <SelectItem value="mas_vendidos">Productos Más Vendidos</SelectItem>
                                    <SelectItem value="stock_minimo">Alerta Stock Mínimo</SelectItem>
                                    <SelectItem value="por_vencer">Próximos a Vencer</SelectItem>
                                    <SelectItem value="valor_inventario">Valor Total Inventario</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Filtro: Desde (Ventas/General) -->
                        <div v-if="isVentas(selectedTipo)" class="grid gap-2">
                            <Label for="desde">Desde</Label>
                            <Input id="desde" type="date" v-model="desdeVal" />
                        </div>

                        <!-- Filtro: Hasta (Ventas/General) -->
                        <div v-if="isVentas(selectedTipo)" class="grid gap-2">
                            <Label for="hasta">Hasta</Label>
                            <Input id="hasta" type="date" v-model="hastaVal" />
                        </div>

                        <!-- Filtro: Umbral (Stock Mínimo) -->
                        <div v-if="isStockMinimo(selectedTipo)" class="grid gap-2">
                            <Label for="umbral">Umbral de Stock</Label>
                            <Input id="umbral" type="number" min="0" v-model="umbralVal" />
                        </div>

                        <!-- Filtro: Días (Próximos a Vencer) -->
                        <div v-if="isPorVencer(selectedTipo)" class="grid gap-2">
                            <Label for="dias">Días de Anticipación</Label>
                            <Input id="dias" type="number" min="1" v-model="diasVal" />
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex gap-2 md:col-span-1">
                            <Button type="submit" class="flex-1 rounded-full">
                                Generar
                            </Button>
                            <Button type="button" variant="outline" size="icon" class="rounded-full shrink-0" @click="handlePrint" title="Imprimir reporte">
                                <Printer class="size-4" />
                            </Button>
                        </div>

                    </form>
                </CardContent>
            </Card>

            <!-- Report Results Section -->
            <Card class="border-border/70 shadow-lg print:border-none print:shadow-none">
                <CardHeader class="flex flex-row items-center justify-between border-b pb-4">
                    <div>
                        <CardTitle class="text-xl flex items-center gap-2">
                            <FileText class="size-5 text-primary print:text-black" />
                            <span>Resultado del Reporte</span>
                        </CardTitle>
                        <CardDescription class="print:hidden">Detalles generados a partir de los filtros seleccionados.</CardDescription>
                        <!-- Print-only metadata -->
                        <div class="hidden print:block text-sm text-muted-foreground mt-1 space-y-1">
                            <p><strong>Reporte:</strong> {{ selectedTipo.toUpperCase() }}</p>
                            <p v-if="isVentas(selectedTipo) && (desdeVal || hastaVal)">
                                <strong>Periodo:</strong> {{ desdeVal || 'Inicio' }} hasta {{ hastaVal || 'Fin' }}
                            </p>
                            <p v-if="isStockMinimo(selectedTipo)"><strong>Umbral:</strong> ≤ {{ umbralVal }} unidades</p>
                            <p v-if="isPorVencer(selectedTipo)"><strong>Límite:</strong> {{ diasVal }} días</p>
                            <p><strong>Fecha de Emisión:</strong> {{ new Date().toLocaleString() }}</p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="pt-6">
                    <DataTable v-if="reporteRows && reporteRows.length">
                        <Table>
                            <TableHeader>
                                <TableRow class="grid grid-cols-[10rem_1fr_12rem] items-center gap-4 rounded-t-2xl bg-muted hover:bg-muted print:bg-gray-100 print:text-black">
                                    <TableHead class="min-h-12 px-4 py-3 text-muted-foreground font-semibold print:text-black">Categoría</TableHead>
                                    <TableHead class="min-h-12 px-4 py-3 text-muted-foreground font-semibold print:text-black">Descripción</TableHead>
                                    <TableHead class="min-h-12 px-4 py-3 text-right text-muted-foreground font-semibold print:text-black">Total / Valor</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="(row, index) in reporteRows"
                                    :key="index"
                                    class="grid grid-cols-[10rem_1fr_12rem] items-center gap-4 px-4 transition hover:bg-muted/40 print:hover:bg-transparent print:border-b print:py-2"
                                >
                                    <TableCell class="p-3 font-semibold text-primary/80 print:text-black uppercase text-xs tracking-wider">
                                        {{ row.categoria }}
                                    </TableCell>
                                    <TableCell class="p-3">
                                        <span class="text-sm font-medium text-foreground print:text-black">{{ row.descripcion }}</span>
                                    </TableCell>
                                    <TableCell class="p-3 text-right">
                                        <span class="inline-flex items-center rounded-full border bg-background px-3 py-1 text-sm font-semibold tabular-nums print:border-none print:px-0">
                                            {{ row.total }}
                                        </span>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </DataTable>

                    <div v-else class="text-center py-12 text-muted-foreground">
                        <p class="text-lg">No se encontraron datos para los filtros especificados.</p>
                        <p class="text-sm mt-1">Prueba cambiando los filtros o generando otro tipo de reporte.</p>
                    </div>
                </CardContent>
            </Card>

        </div>
    </AdminLayout>
</template>

<style>
@media print {
    body {
        background-color: white !important;
        color: black !important;
    }
    aside, header, nav, button {
        display: none !important;
    }
}
</style>
