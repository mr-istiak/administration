<script setup>
import 'ladministration-vue-hook/administration.css'

import { onMounted, ref } from 'vue'
import logo from '@/Components/ApplicationLogo.vue'
import DangerButton from '@/Components/DangerButton.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Layout, ModelIndex } from 'ladministration-vue-hook'
import SecondaryButton from '@/Components/SecondaryButton.vue'

defineOptions({
    layout: (h, page) => h(Layout, { logo }, () => h(ModelIndex, { ...page.props, DangerButton, PrimaryButton, SecondaryButton }, () => page))
})

const selector = ref(null);

onMounted(() => {
    let owner = selector.value.previousElementSibling.querySelector('tr[data-id="1"]'),
        member = selector.value.previousElementSibling.querySelector('tr[data-id="2"]')
    owner.querySelector('a').remove()
    owner.querySelector('button').remove()
    member.lastElementChild.querySelector('button').remove()
    console.log();

})
</script>

<template>
    <div class="hidden" ref="selector"></div>
</template>
