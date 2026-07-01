<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import type { Component } from 'vue';

interface StatItem {
    title: string;
    value: number;
    description: string;
    icon: Component;
    href: string;
}

defineProps<{ stats: StatItem[] }>();
</script>

<template>
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article
            v-for="stat in stats"
            :key="stat.title"
            class="rounded-2xl border border-muted/80 bg-card/95 p-5 shadow-sm transition hover:border-primary/40"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-muted-foreground">{{ stat.title }}</p>
                    <p class="text-3xl font-semibold tracking-tight">{{ stat.value }}</p>
                </div>
                <div class="flex size-12 items-center justify-center rounded-2xl border bg-primary/10 text-primary">
                    <component :is="stat.icon" class="size-5" />
                </div>
            </div>
            <Link :href="stat.href" class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-primary underline-offset-4 hover:underline">
                Ir al modulo
                <ArrowRight class="size-3.5" />
            </Link>
            <p class="text-xs text-muted-foreground">{{ stat.description }}</p>
        </article>
    </section>
</template>
