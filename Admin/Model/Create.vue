<script setup>
import { defineAsyncComponent, ref, } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const Input = defineAsyncComponent(() => import('@/Components/TextInput.vue'));
const InputLabel = defineAsyncComponent(() => import('@/Components/InputLabel.vue'));
const InputError = defineAsyncComponent(() => import('@/Components/InputError.vue'))

const props = defineProps([ 'name', 'inputs' ]);

const parentRoute = ref(route().current().split('.')[0]),
    action = ref(route().current().split('.')[1]);


const form = useForm((() => {
    const data = {}
    Object.keys(props.inputs).forEach(name => data[name] = props.inputs[name].default ?? '');
    return data;
})());

const type = input => {
    if (['char', 'float', 'ulid', 'varchar'].includes(input.type)) return 'text';
    if (['bigint', 'decimal', 'double', 'integer', 'mediumint', 'smallint', 'tinyint'].includes(input.type)) return 'number';
    if (['boolean'].includes(input.type)) return 'checkbox';
    if (['datetime', 'timestamp'].includes[input.type]) return 'datetime-local';
    if (['date'].includes[input.type]) return 'date';
    if (['time'].includes[input.type]) return 'time';
    if (['blob'].includes(input.type)) return 'file';
    if (['enum'].includes(input.type)) return 'radio';
    return false;
}
</script>

<template>
    <Head :title="`${action[0].toUpperCase() + action.slice(1)} ${props.name}`" />

    <div class="flex justify-between mt-4 capitalize">
        <h2>{{ `${action} ${props.name}` }}</h2>
        <Link :href="route(parentRoute + '.index')">
            <SecondaryButton>
                Back
            </SecondaryButton>
        </Link>
    </div>

    <!-- Form -->
    <form class="py-4" @submit.prevent="form[action === 'create' ? 'post' : 'put'](route(parentRoute + '.' + (action === 'create' ? 'store' : 'update'), { 'id': props.inputs.id?.default ?? null } ))">
        <template v-for="(input, name) in props.inputs" :key="name">
            <div v-if="type(input) && (input.type !== 'hidden')" class="mt-4">
                <InputLabel :for="name" :value="name.replace(/_/g, ' ')" class="capitalize" />
                <Input
                    :id="name"
                    :type="type(input)"
                    class="mt-1 block w-full"
                    v-model="form[name]"
                    :required="input.required"
                    autofocus
                    autocomplete="name"
                    :disabled="input.disabled ?? false"
                    :placeholder="input.required ? null : '(Optional)'"
                />
                <InputError class="mt-2" :massege="form.errors[name]" />
            </div>
        </template>
        <slot></slot>
        <PrimaryButton type="submit" class="mt-4 invertColor">Create</PrimaryButton>
    </form>
</template>

<script>
import 'ladministration-vue-hook/style.css'
import { layout } from 'ladministration-vue-hook'
export default { layout }
</script>
