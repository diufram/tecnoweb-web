<script setup lang="ts">
interface Props {
    total?: number;
    label?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <p v-if="typeof total === 'number'" class="text-sm text-muted-foreground">
            {{ label ?? 'Total registrados' }}: <span class="font-medium text-foreground">{{ total }}</span>
        </p>
        <slot />
    </div>
</template>
