<script setup lang="ts">
import { useFormatters } from '@/composables/useFormatters';

interface Props {
    estado: string;
    class?: string;
}

const props = defineProps<Props>();

const { statusClass } = useFormatters();
</script>

<template>
    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold" :class="[statusClass(estado), props.class]">
        {{ estado }}
    </span>
</template>
