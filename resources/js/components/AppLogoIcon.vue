<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();

const appUrl = document.querySelector<HTMLMetaElement>('meta[name="app-url"]')?.content.replace(/\/$/, '') ?? '';
</script>

<template>
    <img :src="`${appUrl}/logo.png`" alt="SanaMed" :class="className" v-bind="$attrs" />
</template>
