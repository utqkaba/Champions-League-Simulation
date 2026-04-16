<script setup>
defineProps({
    teams: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <article class="min-w-0">
        <header
            class="grid grid-cols-[minmax(0,1fr)_34px] bg-[#2f353b] px-3 py-4 text-[12px] font-bold text-white"
        >
            <div class="pr-2">Championship Predictions</div>
            <div class="text-right">%</div>
        </header>

        <div class="border-b border-[#d7dbe0]">
            <div
                v-for="team in teams"
                :key="team.name"
                class="grid grid-cols-[minmax(0,1fr)_34px] items-center border-b border-[#e3e6ea] px-3 py-5 text-[15px] text-[#2c333a] last:border-b-0"
            >
                <div class="truncate pr-2">{{ team.name }}</div>
                <div class="text-right">{{ team.percentage }}</div>
            </div>
        </div>
    </article>
</template>
