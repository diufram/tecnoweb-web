<script setup lang="ts">
import type { Component } from 'vue';

interface Props {
    icon: Component;
    title: string;
    description?: string;
    iconWrapperClass?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex min-h-56 flex-col items-center justify-center gap-3 rounded-3xl border border-dashed bg-card/40 px-6 text-center">
        <div class="flex size-16 items-center justify-center rounded-2xl border bg-background/70">
            <component :is="icon" class="size-8 text-muted-foreground" />
        </div>
        <div>
            <p class="font-medium">{{ title }}</p>
            <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
        </div>
        <slot />
    </div>
</template>
