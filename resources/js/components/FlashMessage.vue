<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import { CheckCircle, XCircle, X } from 'lucide-vue-next';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref<'success' | 'error'>('success');

const flash = computed(() => page.props.flash as { success?: string; error?: string; message?: string });

const autoHide = () => {
    setTimeout(() => {
        show.value = false;
    }, 5000);
};

const close = () => {
    show.value = false;
};

watch(
    () => [flash.value.success, flash.value.error, flash.value.message],
    ([success, error, msg]) => {
        if (success) {
            message.value = success;
            type.value = 'success';
            show.value = true;
            autoHide();
        } else if (error) {
            message.value = error;
            type.value = 'error';
            show.value = true;
            autoHide();
        } else if (msg) {
            message.value = msg;
            type.value = 'success';
            show.value = true;
            autoHide();
        }
    },
    { immediate: true }
);
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="show && message"
            class="fixed top-4 right-4 z-50 flex items-center gap-3 rounded-lg border px-4 py-3 shadow-lg max-w-md"
            :class="{
                'bg-green-50 border-green-200 text-green-900': type === 'success',
                'bg-red-50 border-red-200 text-red-900': type === 'error',
            }"
        >
            <CheckCircle v-if="type === 'success'" class="h-5 w-5 text-green-600 flex-shrink-0" />
            <XCircle v-if="type === 'error'" class="h-5 w-5 text-red-600 flex-shrink-0" />
            <p class="text-sm font-medium flex-1">{{ message }}</p>
            <button
                @click="close"
                class="text-current opacity-70 hover:opacity-100 transition-opacity"
            >
                <X class="h-4 w-4" />
            </button>
        </div>
    </Transition>
</template>
