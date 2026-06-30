<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex items-center gap-3">
        <AppLogoIcon class="size-8 shrink-0" />
        <span class="truncate font-semibold text-sidebar-foreground">SanaMed</span>
    </div>
</template>
