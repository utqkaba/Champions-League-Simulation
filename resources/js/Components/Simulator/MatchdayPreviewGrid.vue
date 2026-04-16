<script setup>
defineProps({
    matchdays: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <section class="grid grid-cols-1 gap-x-7 gap-y-5 md:grid-cols-2 xl:grid-cols-4">
        <article
            v-for="matchday in matchdays"
            :key="matchday[0]?.matchday"
            class="w-full"
        >
            <header class="bg-[#2f353b] px-3 py-3 text-[12px] font-bold text-white">
                Week {{ matchday[0]?.matchday }}
            </header>

            <div class="border-b border-[#d7dbe0] px-3 py-4">
                <div
                    v-for="fixture in matchday"
                    :key="fixture.id"
                    class="grid grid-cols-[1fr_36px_1fr] items-center gap-2 py-3 text-[14px] text-[#2d343c]"
                >
                    <div class="truncate">{{ fixture.home_team.name }}</div>
                    <div class="text-center text-[15px] text-[#57606a]">-</div>
                    <div class="truncate text-right">{{ fixture.away_team.name }}</div>
                </div>
            </div>
        </article>
    </section>
</template>
