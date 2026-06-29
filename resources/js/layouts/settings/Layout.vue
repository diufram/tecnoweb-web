<script setup lang="ts">
import Heading from '@/components/Heading.vue';
</script>

<template>
    <div class="px-4 py-6 md:px-6">
        <Heading title="Configuración" description="Administra tu perfil y preferencias de cuenta" />

        <section class="mt-8 max-w-3xl space-y-12">
            <slot />
        </section>
    </div>
</template>
