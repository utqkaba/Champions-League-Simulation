<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    variant: {
        type: String,
        default: "primary",
    },
    size: {
        type: String,
        default: "default",
    },
});

defineEmits(["click"]);
</script>

<template>
    <button
        type="button"
        :class="[
            'rounded text-[14px] font-semibold text-white transition',
            size === 'compact' ? 'px-4 py-2' : 'px-9 py-3',
            variant === 'danger'
                ? 'bg-[#dc3545] hover:bg-[#bd2d3d]'
                : 'bg-[#2aa7c7] hover:bg-[#228faa]',
        ]"
        @click="$emit('click')"
    >
        {{ label }}
    </button>
</template>
