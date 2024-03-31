<script setup>
import 'ladministration-vue-hook/administration.css'

import { router, usePage } from '@inertiajs/vue3'
import { inject, ref, computed } from 'vue'
import Input from '@/Components/TextInput.vue'
import logo from '@/Components/ApplicationLogo.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Layout, ModelCreate } from 'ladministration-vue-hook'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { ChevronDownIcon, XMarkIcon } from '@heroicons/vue/24/solid'
import { RadioGroup, RadioGroupLabel, RadioGroupOption, Combobox, ComboboxOption, ComboboxOptions, ComboboxButton, ComboboxLabel, Dialog, DialogPanel, DialogTitle, } from '@headlessui/vue'


defineOptions({
    layout: (h, page) => h(Layout, { logo }, () => h(ModelCreate, { ...page.props, PrimaryButton, SecondaryButton, InputLabel, Input }, () => page) ),
    inheritAttrs: false
})
const props = defineProps({
    accesses: Object,
    roles: Array,
    current: { type: Number, default: null }
})

const form = inject('form')

form.permissions = 'exclude'
form.exclude = []
form.include = []

const query = ref(''),
    roleSelector = {
        open: ref(false),
        user: ref(null),
        role: ref(props.current),
        change: () => {
            roleSelector.open.value = false
            router.post(
                route('users.role.change', { user: roleSelector.user.value }), { role: roleSelector.role.value },
                {
                    preserveScroll: true,
                    only: ['inputs', 'errors', 'success', 'info', 'warning'],
                    onSuccess: () => form.users = usePage().props.inputs.users.default
                }
            )
            roleSelector.user.value = null
            roleSelector.role.value = props.current
        }
    }

const filteredAccesses = computed(() => {
    if(query.value === '') return props.accesses
    let filtered = {};
    for (const key in props.accesses) {
        if (Object.hasOwnProperty.call(props.accesses, key)) {
            const value = props.accesses[key];
            if(key.toLowerCase().includes(query.value.toLowerCase())) filtered[key] = value
        }
    }
    return filtered
})
</script>

<template>
    <div class="space-y-4">
        <RadioGroup v-model="form.permissions" class="mt-4 flex space-x-8 items-center" >
            <RadioGroupLabel>
                <InputLabel>Permissions</InputLabel>
            </RadioGroupLabel>
            <div class="flex space-x-4">
                <RadioGroupOption v-for="permissionType in ['include', 'exclude']" :key="permissionType" :value="permissionType" v-slot="{ checked }" class="flex items-center">
                    <input type="radio" name="permission" :checked="checked">
                    <span class="ml-2 capitalize cursor-pointer ui-not-checked:text-[var(--secondary-color)]">{{ permissionType }} Access</span>
                </RadioGroupOption>
            </div>
        </RadioGroup>
        <Combobox as="div" v-model="form[form.permissions]" class="space-y-2 w-[30rem] min-w-64" multiple>
            <ComboboxLabel>
                <InputLabel class="capitalize">{{ form.permissions }} Access</InputLabel>
            </ComboboxLabel>
            <ul v-if="form[form.permissions].length > 0" class="border capitalize divide-y text-[var(--secondary-color)]">
                <li class="py-2 px-4 bg-gray-200 cursor-not-allowed" disabled>
                    <span>Selected Access</span>
                </li>
                <li v-for="access in form[form.permissions]" :key="access" class="py-2 px-4 flex items-center justify-between hover:text-gray-900 transition-colors duration-200" :title="props.accesses[access]">
                    <span>{{ access.replace(/:/g, ': ') }}</span>
                    <XMarkIcon class="w-6 h-6 text-inherit cursor-pointer" @click="form[form.permissions] = form[form.permissions].filter(x => x !== access)" />
                </li>
            </ul>
            <ComboboxButton class="group flex border space-x-2 bg-slate-50 focus-within:bg-white transition-all duration-200 items-center px-4 py-2 rounded-md focus-within:rounded-none focus-within:border-[var(--primary-color)] w-full">
                <input placeholder="Search Accesses" class="w-full border-none focus-visible:outline-none focus:ring-0 bg-transparent p-0" v-model="query" />
                <ChevronDownIcon class="w-6 h-6 group-focus-within:-rotate-180 transition-transform duration-200" />
            </ComboboxButton>
            <ComboboxOptions as="ul" class="w-full border border-[var(--primary-color)] capitalize max-h-48 overflow-y-auto">
                <ComboboxOption as="li"
                    v-for="(value, key) in filteredAccesses"
                    :title="value"
                    :key="key"
                    :value="key"
                    class="py-2 px-4 cursor-pointer text-gray-700 hover:bg-gray-100 ui-selected:bg-gray-200"
                >
                    {{ key.replace(/:/g, ': ') }}
                </ComboboxOption>
            </ComboboxOptions>
        </Combobox>
        <div v-if="Array.isArray(form.users) && (form.users?.length > 0)" class="space-y-2">
            <span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Users</span>
            <table class="w-full wrap-anywhere">
                <thead>
                    <tr>
                        <th v-for="key in Object.keys(form.users[0])" :key="key" class="uppercase border py-2">{{ key }}</th>
                        <th class="uppercase border py-2">Change Role</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in form.users" :key="user.id">
                        <td v-for="(value, key) in user" :key="key" class="p-2 border-x text-center">{{ value ?? '---' }}</td>
                        <td class="p-2 border-x text-center">
                            <SecondaryButton type="button" @click="roleSelector.open.value = true; roleSelector.user.value = user.id">
                                Change
                            </SecondaryButton>
                        </td>
                    </tr>
                </tbody>
            </table>
            <teleport to="body">
                <Dialog :open="roleSelector.open.value" @close="roleSelector.open.value = false" as="div" class="fixed inset-0 z-50 flex justify-center place-items-center bg-black/10">
                    <DialogPanel as="div" class="bg-gray-50 shadow rounded-md px-8 py-6 min-w-80 sm:min-w-96 space-y-4">
                        <DialogTitle>Select Role</DialogTitle>
                        <div class="flex space-x-4">
                            <div v-for="role in roles" :key="role.id" class="flex items-center">
                                <input type="radio" name="roleSelector" :id="`roleSelector:${role.id}`" :value="role.id" @input="roleSelector.role.value = $event.target.value;" :checked="Number(roleSelector.role.value) === Number(role.id)"/>
                                <label :for="`roleSelector:${role.id}`"  class="ml-2 capitalize cursor-pointer ui-not-checked:text-[var(--secondary-color)]">{{ role.name }}</label>
                            </div>
                        </div>
                        <div class="w-full flex items-center justify-between">
                            <PrimaryButton class="invertColor" @click="roleSelector.change()">Change</PrimaryButton>
                            <SecondaryButton @click="roleSelector.open.value = false">Cancel</SecondaryButton>
                        </div>
                    </DialogPanel>
                </Dialog>
            </teleport>
        </div>
    </div>
</template>
