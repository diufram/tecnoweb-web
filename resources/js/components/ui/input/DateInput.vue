<script setup lang="ts">
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { DateFormatter, getLocalTimeZone, parseDate, today, type DateValue } from '@internationalized/date';
import { CalendarDays } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { HTMLAttributes } from 'vue';
import { Button } from '../button';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    modelValue?: string | number | null;
    defaultValue?: string | number | null;
    class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const open = ref(false);
const formatter = new DateFormatter('es-BO', { dateStyle: 'medium' });

const selectedDate = computed<DateValue | undefined>(() => {
    const value = props.modelValue ?? props.defaultValue;

    if (!value) {
        return undefined;
    }

    try {
        return parseDate(String(value));
    } catch {
        return undefined;
    }
});

const defaultPlaceholder = computed(() => selectedDate.value ?? today(getLocalTimeZone()));
const formattedDate = computed(() => (selectedDate.value ? formatter.format(selectedDate.value.toDate(getLocalTimeZone())) : 'Selecciona fecha'));

const updateDate = (date: DateValue | undefined) => {
    if (!date) {
        return;
    }

    emit('update:modelValue', date.toString());
    open.value = false;
};
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                v-bind="$attrs"
                type="button"
                variant="outline"
                :class="
                    cn(
                        'h-10 w-full justify-start rounded-md border border-input bg-background px-3 text-left font-normal shadow-sm hover:bg-background hover:text-foreground focus-visible:border-ring focus-visible:ring-0 dark:border-muted-foreground/35',
                        !selectedDate && 'text-muted-foreground',
                        props.class,
                    )
                "
            >
                <CalendarDays class="mr-2 size-4" />
                {{ formattedDate }}
            </Button>
        </PopoverTrigger>
        <PopoverContent align="start" class="w-auto p-0">
            <Calendar
                :model-value="selectedDate"
                :default-placeholder="defaultPlaceholder"
                :initial-focus="true"
                @update:model-value="updateDate"
            />
        </PopoverContent>
    </Popover>
</template>
