<script setup lang="ts">
import { cn } from '@/lib/utils';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import {
    CalendarCell,
    CalendarCellTrigger,
    CalendarGrid,
    CalendarGridBody,
    CalendarGridHead,
    CalendarGridRow,
    CalendarHeadCell,
    CalendarHeader,
    CalendarHeading,
    CalendarNext,
    CalendarPrev,
    CalendarRoot,
    useForwardPropsEmits,
    type CalendarRootEmits,
    type CalendarRootProps,
} from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps<CalendarRootProps & { class?: HTMLAttributes['class'] }>();
const emits = defineEmits<CalendarRootEmits>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;
    return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <CalendarRoot
        v-slot="{ grid, weekDays }"
        v-bind="{ ...forwarded, ...$attrs }"
        :class="cn('p-3', props.class)"
    >
        <CalendarHeader class="relative flex items-center justify-center pb-3">
            <CalendarPrev
                class="absolute left-1 inline-flex size-8 items-center justify-center rounded-md border border-input bg-background text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-50"
            >
                <ChevronLeft class="size-4" />
            </CalendarPrev>
            <CalendarHeading class="text-sm font-medium" />
            <CalendarNext
                class="absolute right-1 inline-flex size-8 items-center justify-center rounded-md border border-input bg-background text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-50"
            >
                <ChevronRight class="size-4" />
            </CalendarNext>
        </CalendarHeader>

        <div class="flex flex-col gap-y-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
            <CalendarGrid v-for="month in grid" :key="month.value.toString()" class="w-full border-collapse space-y-1">
                <CalendarGridHead>
                    <CalendarGridRow class="flex">
                        <CalendarHeadCell v-for="day in weekDays" :key="day" class="w-9 rounded-md text-[0.8rem] font-normal text-muted-foreground">
                            {{ day }}
                        </CalendarHeadCell>
                    </CalendarGridRow>
                </CalendarGridHead>
                <CalendarGridBody>
                    <CalendarGridRow v-for="(weekDates, index) in month.rows" :key="`week-${index}`" class="mt-2 flex w-full">
                        <CalendarCell v-for="weekDate in weekDates" :key="weekDate.toString()" :date="weekDate" class="relative size-9 p-0 text-center text-sm">
                            <CalendarCellTrigger
                                v-slot="{ dayValue }"
                                :day="weekDate"
                                :month="month.value"
                                class="inline-flex size-9 items-center justify-center rounded-md text-sm font-normal transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 data-[disabled]:pointer-events-none data-[outside-view]:text-muted-foreground data-[outside-view]:opacity-50 data-[selected]:bg-primary data-[selected]:text-primary-foreground data-[today]:bg-accent data-[today]:text-accent-foreground data-[selected]:hover:bg-primary data-[selected]:hover:text-primary-foreground"
                            >
                                {{ dayValue }}
                            </CalendarCellTrigger>
                        </CalendarCell>
                    </CalendarGridRow>
                </CalendarGridBody>
            </CalendarGrid>
        </div>
    </CalendarRoot>
</template>
