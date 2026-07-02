<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import FormularioLogin from '@/components/FormularioLogin.vue';
import TextLink from '@/components/TextLink.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
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

                    <FormularioLogin :can-reset-password="canResetPassword" />
                </CardContent>

                <CardFooter class="justify-center border-t px-6 py-4 text-sm text-muted-foreground">
                    No tienes una cuenta?
                    <TextLink :href="route('register')" class="ml-1">Regístrate</TextLink>
                </CardFooter>
            </Card>
        </div>
    </div>
</template>
