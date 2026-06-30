<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();

const baseUrl = import.meta.env.BASE_URL;
</script>

<template>
    <img :src="`${baseUrl}logo.png`" alt="SanaMed" :class="className" v-bind="$attrs" />
</template>
