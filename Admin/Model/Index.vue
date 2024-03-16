<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3'
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps(['data', 'columns', 'options']);

const current = ref(null);
current.value = (route().current().split('.')[0][0].toUpperCase() + route().current().split('.')[0].slice(1)).replace(/-/g, ' ');

const Delete = id => {
    if(!confirm('Are you sure to delete this user?')) return;
    router.delete(route(route().current().split('.')[0] + '.destroy', { id }))
}
</script>

<template>
    <Head :title="current" />

    <div class="flex justify-between mt-4">
        <h2>{{ current }}</h2>
        <Link v-if="props.options.new" :href="route(route().current().split('.')[0] + '.create')" class="invertColor">
            <PrimaryButton class="invertColor">
                New
            </PrimaryButton>
        </Link>
    </div>

    <table class="w-full wrap-anywhere">
        <thead>
            <th v-for="column in props.columns" :key="column" class="uppercase border py-2">
                {{ column.replace(/_/g, ' ') }}
            </th>
            <th v-if="props.options.edit" class="uppercase border py-2">Edit</th>
            <th v-if="props.options.delete" class="uppercase border py-2">Delete</th>
        </thead>
        <tbody>
            <tr v-for="item in props.data" :key="item">
                <td v-for="column in props.columns" :key="column" class="p-2 border-x text-center">
                    {{ item[column] ?? '---' }}
                </td>
                <td v-if="props.options.edit" class="p-1 border-x text-center text-nowrap">
                    <Link :href="route(route().current().split('.')[0] + '.edit', {'id': item['id']})">
                        <SecondaryButton>Edit</SecondaryButton>
                    </Link>
                </td>
                <td v-if="props.options.delete" class="p-1 border-x text-center text-nowrap">
                    <DangerButton @click.prevent="Delete(item['id'])" type="button">Delete</DangerButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
import 'ladministration-vue-hook/style.css'
import { layout } from 'ladministration-vue-hook'
export default { layout }
</script>
