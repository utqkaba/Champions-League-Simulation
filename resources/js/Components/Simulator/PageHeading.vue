<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    centered: {
        type: Boolean,
        default: false,
    },
    marginClass: {
        type: String,
        default: "mb-6",
    },
});
</script>

<template>
    <h1
        :class="[
            marginClass,
            'text-[22px] font-light text-[#6f767d]',
            centered ? 'text-center' : '',
        ]"
    >
        {{ title }}
    </h1>
</template>
