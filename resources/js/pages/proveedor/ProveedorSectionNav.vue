<script setup lang="ts">
defineProps<{ current: 'solicitudes' | 'contraofertas' | 'compras' | 'historial' }>();
</script>

<template>
    <slot />
</template>
