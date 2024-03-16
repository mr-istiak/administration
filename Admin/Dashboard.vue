<script setup>
import { Head } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Dashboard" />

        <h2>Dashboard</h2>
        <div>
            <div class="p-6 text-gray-900 dark:text-gray-100">You're logged in!</div>
        </div>
</template>

<script>
import 'ladministration-vue-hook/style.css'
import { layout } from 'ladministration-vue-hook'
export default { layout }
</script>
