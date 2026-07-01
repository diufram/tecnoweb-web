<script setup lang="ts">
interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <span class="inline-flex items-center rounded-full border bg-background px-3 py-1 text-sm font-semibold" :class="$attrs.class as string">
        <slot />
    </span>
</template>
