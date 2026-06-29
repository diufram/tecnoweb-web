<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Accessibility, Baby, Clock3, Contrast, Monitor, Moon, Sparkles, Sun, UserRound } from 'lucide-vue-next';
import type { Component } from 'vue';

interface Props {
    class?: string;
}

const { class: containerClass = '' } = defineProps<Props>();

const { preferences, updatePreferences } = useAppearance();

type Option<T extends string> = {
    value: T;
    Icon: Component;
    label: string;
    description?: string;
};

const colorModes = [
    { value: 'light', Icon: Sun, label: 'Día', description: 'Interfaz clara' },
    { value: 'dark', Icon: Moon, label: 'Noche', description: 'Interfaz oscura' },
    { value: 'system', Icon: Monitor, label: 'Sistema', description: 'Usa la preferencia del equipo' },
    { value: 'schedule', Icon: Clock3, label: 'Según horario', description: 'Día de 06:00 a 18:59' },
] as const;

const audienceThemes = [
    { value: 'kids', Icon: Baby, label: 'Niños', description: 'Colores vivos y formas amigables' },
    { value: 'youth', Icon: Sparkles, label: 'Jóvenes', description: 'Estilo moderno y dinámico' },
    { value: 'adults', Icon: UserRound, label: 'Adultos', description: 'Diseño sobrio y legible' },
] as const;

const textScales = [
    { value: 'normal', Icon: Accessibility, label: 'Normal' },
    { value: 'large', Icon: Accessibility, label: 'Grande' },
    { value: 'extra-large', Icon: Accessibility, label: 'Muy grande' },
] as const;

const contrasts = [
    { value: 'normal', Icon: Contrast, label: 'Normal' },
    { value: 'high', Icon: Contrast, label: 'Alto contraste' },
] as const;
</script>

<template>
    <div :class="['space-y-8', containerClass]">
        <section class="space-y-3">
            <div>
                <h3 class="font-medium">Modo de color</h3>
                <p class="text-sm text-muted-foreground">Elige entre día, noche, sistema o cambio automático por horario del cliente.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <button
                    v-for="{ value, Icon, label, description } in colorModes"
                    :key="value"
                    type="button"
                    @click="updatePreferences({ colorMode: value })"
                    :class="[
                        'rounded-xl border p-4 text-left transition hover:bg-accent',
                        preferences.colorMode === value ? 'border-primary bg-accent text-accent-foreground shadow-sm' : 'border-border',
                    ]"
                >
                    <component :is="Icon" class="mb-3 size-5" />
                    <div class="font-medium">{{ label }}</div>
                    <p class="mt-1 text-xs text-muted-foreground">{{ description }}</p>
                </button>
            </div>
        </section>

        <section class="space-y-3">
            <div>
                <h3 class="font-medium">Tema del sitio</h3>
                <p class="text-sm text-muted-foreground">Aplica un estilo visual único para todo el sistema.</p>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <button
                    v-for="{ value, Icon, label, description } in audienceThemes"
                    :key="value"
                    type="button"
                    @click="updatePreferences({ audienceTheme: value })"
                    :class="[
                        'rounded-xl border p-4 text-left transition hover:bg-accent',
                        preferences.audienceTheme === value ? 'border-primary bg-accent text-accent-foreground shadow-sm' : 'border-border',
                    ]"
                >
                    <component :is="Icon" class="mb-3 size-5" />
                    <div class="font-medium">{{ label }}</div>
                    <p class="mt-1 text-xs text-muted-foreground">{{ description }}</p>
                </button>
            </div>
        </section>

        <section class="space-y-3">
            <div>
                <h3 class="font-medium">Accesibilidad</h3>
                <p class="text-sm text-muted-foreground">Ajusta el tamaño de letras y el contraste del sitio.</p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="space-y-2 rounded-xl border p-4">
                    <p class="text-sm font-medium">Tamaño de texto</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="{ value, Icon, label } in textScales"
                            :key="value"
                            type="button"
                            @click="updatePreferences({ textScale: value })"
                            :class="[
                                'inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition hover:bg-accent',
                                preferences.textScale === value ? 'border-primary bg-accent text-accent-foreground' : 'border-border',
                            ]"
                        >
                            <component :is="Icon" class="size-4" />
                            {{ label }}
                        </button>
                    </div>
                </div>

                <div class="space-y-2 rounded-xl border p-4">
                    <p class="text-sm font-medium">Contraste</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="{ value, Icon, label } in contrasts"
                            :key="value"
                            type="button"
                            @click="updatePreferences({ contrast: value })"
                            :class="[
                                'inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition hover:bg-accent',
                                preferences.contrast === value ? 'border-primary bg-accent text-accent-foreground' : 'border-border',
                            ]"
                        >
                            <component :is="Icon" class="size-4" />
                            {{ label }}
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
