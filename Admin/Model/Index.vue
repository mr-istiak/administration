<script setup>
import 'ladministration-vue-hook/administration.css'

import logo from '@/Components/ApplicationLogo.vue'
import DangerButton from '@/Components/DangerButton.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Layout, ModelIndex } from 'ladministration-vue-hook'
import SecondaryButton from '@/Components/SecondaryButton.vue'

defineOptions({
    layout: (h, page) => h(Layout, { logo }, () => h(ModelIndex, { ...page.props, DangerButton, PrimaryButton, SecondaryButton }, () => page))
})
</script>

<template>
    <!-- // -->
</template>
