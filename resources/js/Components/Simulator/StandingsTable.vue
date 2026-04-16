<script setup>
defineProps({
    standings: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <article class="lg:col-span-2 xl:col-span-1">
        <div>
            <header
                class="grid grid-cols-[minmax(118px,1.08fr)_repeat(6,minmax(30px,0.34fr))] gap-x-1 bg-[#2f353b] px-3 py-4 text-[11px] font-bold text-white sm:text-[12px]"
            >
                <div>Team Name</div>
                <div class="text-center">Pts</div>
                <div class="text-center">P</div>
                <div class="text-center">W</div>
                <div class="text-center">D</div>
                <div class="text-center">L</div>
                <div class="text-center">GD</div>
            </header>

            <div class="border-b border-[#d7dbe0]">
                <div
                    v-for="team in standings"
                    :key="team.id"
                    class="grid grid-cols-[minmax(118px,1.08fr)_repeat(6,minmax(30px,0.34fr))] items-center gap-x-1 border-b border-[#e3e6ea] px-3 py-5 text-[13px] text-[#2c333a] last:border-b-0 sm:text-[15px]"
                >
                    <div class="truncate pr-2">{{ team.name }}</div>
                    <div class="text-center font-semibold">{{ team.points }}</div>
                    <div class="text-center">{{ team.played }}</div>
                    <div class="text-center">{{ team.won }}</div>
                    <div class="text-center">{{ team.drawn }}</div>
                    <div class="text-center">{{ team.lost }}</div>
                    <div class="text-center">{{ team.goal_difference }}</div>
                </div>
            </div>
        </div>
    </article>
</template>
