<script setup>
import 'ladministration-vue-hook/administration.css'

import Input from '@/Components/TextInput.vue'
import logo from '@/Components/ApplicationLogo.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { Layout, ModelCreate } from 'ladministration-vue-hook'

defineOptions({
    layout: (h, page) => h(Layout, { logo },() => h(ModelCreate, { ...page.props, PrimaryButton, SecondaryButton, Input, InputLabel }, () => page))
})
</script>

<template>
    <!-- // -->
</template>
