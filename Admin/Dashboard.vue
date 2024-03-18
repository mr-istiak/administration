<script setup>
import 'ladministration-vue-hook/administration.css'

import { Head } from '@inertiajs/vue3'
import { Layout } from 'ladministration-vue-hook'
import logo from '@/Components/ApplicationLogo.vue'

defineOptions({
    layout: (h, page) => h(Layout, { logo }, () => page)
})
</script>

<template>
    <Head title="Dashboard" />

        <h2>Dashboard</h2>
        <div>
            <div class="p-6 text-gray-900 dark:text-gray-100">You're logged in!</div>
        </div>
</template>
