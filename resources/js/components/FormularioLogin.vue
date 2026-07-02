<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="grid gap-5">
        <div class="grid gap-2">
            <Label for="email">Correo electrónico</Label>
            <Input
                id="email"
                type="email"
                required
                autofocus
                autocomplete="email"
                v-model="form.email"
                placeholder="nombre@correo.com"
            />
            <InputError :message="form.errors.email" />
        </div>

        <div class="grid gap-2">
            <div class="flex items-center justify-between gap-4">
                <Label for="password">Contraseña</Label>
                <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm">Olvidaste tu contraseña?</TextLink>
            </div>

            <Input
                id="password"
                type="password"
                required
                autocomplete="current-password"
                v-model="form.password"
                placeholder="Tu contraseña"
            />
            <InputError :message="form.errors.password" />
        </div>

        <Label for="remember" class="flex items-center gap-3 text-sm font-normal">
            <Checkbox id="remember" v-model:checked="form.remember" />
            <span>Recordarme</span>
        </Label>

        <Button type="submit" class="w-full" :disabled="form.processing">
            <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
            Ingresar
        </Button>
    </form>
</template>
