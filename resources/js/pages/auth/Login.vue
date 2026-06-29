<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
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
    <div class="flex min-h-svh items-center justify-center bg-muted px-4 py-10">
        <Head title="Log in" />

        <div class="w-full max-w-md space-y-6">
            <Link :href="route('home')" class="mx-auto flex w-fit items-center gap-2 font-medium">
                <div class="flex size-10 items-center justify-center rounded-lg bg-background shadow-sm">
                    <AppLogoIcon class="size-7 fill-current text-foreground" />
                </div>
                <span class="sr-only">Home</span>
            </Link>

            <Card class="border-border/70 shadow-lg">
                <CardHeader class="space-y-2 text-center">
                    <CardTitle class="text-2xl">Iniciar sesión</CardTitle>
                    <CardDescription>Ingresa tus credenciales para acceder a tu cuenta</CardDescription>
                </CardHeader>

                <CardContent>
                    <div v-if="status" class="mb-4 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-center text-sm font-medium text-green-700">
                        {{ status }}
                    </div>

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
                </CardContent>

                <CardFooter class="justify-center border-t px-6 py-4 text-sm text-muted-foreground">
                    No tienes una cuenta?
                    <TextLink :href="route('register')" class="ml-1">Regístrate</TextLink>
                </CardFooter>
            </Card>
        </div>
    </div>
</template>
