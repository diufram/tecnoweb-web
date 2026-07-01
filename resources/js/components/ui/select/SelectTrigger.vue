<script setup lang="ts">
import { cn } from '@/lib/utils';
import { ChevronDown } from 'lucide-vue-next';
import { SelectTrigger as SelectTriggerPrimitive } from 'reka-ui';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <SelectTriggerPrimitive
        v-bind="$attrs"
        :class="
            cn(
                'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-base shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:border-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 aria-[invalid=true]:border-destructive aria-[invalid=true]:focus-visible:border-destructive md:text-sm dark:border-muted-foreground/35 [&>span]:line-clamp-1',
                props.class,
            )
        "
    >
        <slot />
        <ChevronDown class="size-4 opacity-50" />
    </SelectTriggerPrimitive>
</template>
