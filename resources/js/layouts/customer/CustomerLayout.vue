<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { SharedData, User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { CreditCard, Home, PackageSearch, Palette, ReceiptText, UserRound } from 'lucide-vue-next';

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const navItems = [
    { title: 'Inicio', href: route('dashboard.cliente'), icon: Home },
    { title: 'Catálogo', href: '#catalogo', icon: PackageSearch },
    { title: 'Mis compras', href: '#compras', icon: ReceiptText },
    { title: 'Pagos', href: '#pagos', icon: CreditCard },
    { title: 'Perfil', href: route('profile.edit'), icon: UserRound },
    { title: 'Apariencia', href: route('appearance'), icon: Palette },
];
</script>

<template>
    <div class="min-h-svh bg-background">
        <header class="sticky top-0 z-30 border-b bg-background/85 backdrop-blur">
            <div class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link :href="route('dashboard.cliente')" class="flex items-center gap-3 font-semibold">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                        <AppLogoIcon class="size-6 fill-current" />
                    </span>
                    <span>SanaMed</span>
                </Link>

                <nav class="hidden items-center gap-1 md:flex">
                    <Button v-for="item in navItems" :key="item.title" as-child variant="ghost" :class="item.href === page.url ? 'bg-muted' : ''">
                        <Link :href="item.href">
                            <component :is="item.icon" class="size-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="h-11 gap-3 px-2">
                            <UserInfo :user="user" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-64" align="end">
                        <UserMenuContent :user="user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
