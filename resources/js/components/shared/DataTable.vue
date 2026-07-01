<script setup lang="ts">
interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-hidden rounded-2xl border" :class="$attrs.class as string">
        <slot />
    </div>
</template>
