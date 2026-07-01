import { computed } from 'vue';

export type EstadoCategoria =
    | 'APROBADO'
    | 'RECHAZADO'
    | 'CONTRA_OFERTA'
    | 'SOLICITUD'
    | 'PENDIENTE'
    | 'PAGADO'
    | 'PAGADA'
    | 'COMPLETADA'
    | 'PENDIENTE_PAGO'
    | 'CANCELADO';

const STATUS_CLASSES: Record<EstadoCategoria, string> = {
    APROBADO: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    PAGADO: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    PAGADA: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    COMPLETADA: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',

    RECHAZADO: 'border-destructive/30 bg-destructive/10 text-destructive',
    CANCELADO: 'border-destructive/30 bg-destructive/10 text-destructive',

    CONTRA_OFERTA: 'border-blue-500/30 bg-blue-500/10 text-blue-600 dark:text-blue-400',

    SOLICITUD: 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400',
    PENDIENTE: 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400',
    PENDIENTE_PAGO: 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400',
};

export function useFormatters() {
    const money = (value: string | number | null | undefined) =>
        new Intl.NumberFormat('es-BO', {
            style: 'currency',
            currency: 'BOB',
        }).format(Number(value ?? 0));

    const date = (value: string | Date | null | undefined) => {
        if (!value) {
            return '';
        }

        const d = value instanceof Date ? value : new Date(value);

        return d.toLocaleDateString('es-BO');
    };

    const statusClass = (estado: string) =>
        STATUS_CLASSES[estado.toUpperCase() as EstadoCategoria] ?? 'border-muted bg-muted/50 text-muted-foreground';

    const moneyRef = computed(() => money);
    const dateRef = computed(() => date);
    const statusClassRef = computed(() => statusClass);

    return { money, date, statusClass, moneyRef, dateRef, statusClassRef };
}
