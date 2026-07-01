<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AccountLayout from '@/layouts/account/AccountLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Apariencia',
        href: route('appearance'),
    },
];
</script>

<template>
    <AccountLayout :breadcrumbs="breadcrumbItems">
        <Head title="Apariencia" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Apariencia" description="Personaliza el estilo, tema y accesibilidad de todo el sitio" />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AccountLayout>
</template>
