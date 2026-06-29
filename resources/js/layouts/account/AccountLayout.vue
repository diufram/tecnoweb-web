<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import type { BreadcrumbItemType, SharedData, User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user as User);
</script>

<template>
    <AdminLayout v-if="user.actor === 'propietario'" actor="propietario" :breadcrumbs="props.breadcrumbs">
        <slot />
    </AdminLayout>

    <AdminLayout v-else-if="user.actor === 'proveedor'" actor="proveedor" :breadcrumbs="props.breadcrumbs">
        <slot />
    </AdminLayout>

    <CustomerLayout v-else-if="user.actor === 'cliente'">
        <slot />
    </CustomerLayout>

    <AppLayout v-else :breadcrumbs="props.breadcrumbs">
        <slot />
    </AppLayout>
</template>
