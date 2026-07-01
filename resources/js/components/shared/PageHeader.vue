<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    eyebrow?: string;
    title: string;
    description?: string;
    icon?: unknown;
}

const props = defineProps<Props>();

const hasIcon = computed(() => Boolean(props.icon));
</script>

<template>
    <section class="overflow-hidden rounded-3xl border bg-gradient-to-br from-card via-card to-primary/10 p-6 shadow-sm md:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <p v-if="eyebrow" class="text-sm font-semibold uppercase tracking-wide text-primary">
                    {{ eyebrow }}
                </p>
                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">{{ title }}</h1>
                <p v-if="description" class="max-w-2xl text-muted-foreground">{{ description }}</p>
            </div>
            <div v-if="hasIcon" class="flex size-16 items-center justify-center rounded-2xl border bg-background/70 shadow-sm">
                <component :is="icon" class="size-8 text-primary" />
            </div>
        </div>
        <slot />
    </section>
</template>
