<script setup>
import { Head, router } from "@inertiajs/vue3";

defineProps({
    currentWeek: {
        type: Number,
        required: true,
    },
    currentWeekFixtures: {
        type: Array,
        required: true,
    },
    standings: {
        type: Array,
        required: true,
    },
    championshipPredictions: {
        type: Array,
        required: true,
    },
});

function playAllWeeks() {
    router.post(route("simulator.play-all-weeks"));
}

function playNextWeek() {
    router.post(route("simulator.play-next-week"));
}

function resetData() {
    router.post(route("simulator.reset-data"));
}
</script>

<template>
    <Head title="Simulation" />

    <main class="mx-auto min-h-screen max-w-[1500px] px-4 py-4 sm:px-6 lg:px-8">
        <h1 class="mb-6 text-center text-[22px] font-light text-[#6f767d]">
            Simulation
        </h1>

        <section
            class="grid gap-6 lg:grid-cols-2 xl:grid-cols-[minmax(0,1.4fr)_minmax(280px,0.8fr)_minmax(280px,0.8fr)] xl:gap-8"
        >
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
                            <div class="text-center font-semibold">
                                {{ team.points }}
                            </div>
                            <div class="text-center">{{ team.played }}</div>
                            <div class="text-center">{{ team.won }}</div>
                            <div class="text-center">{{ team.drawn }}</div>
                            <div class="text-center">{{ team.lost }}</div>
                            <div class="text-center">
                                {{ team.goal_difference }}
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="min-w-0">
                <header
                    class="bg-[#2f353b] px-3 py-4 text-[12px] font-bold text-white"
                >
                    Week {{ currentWeek }}
                </header>

                <div class="border-b border-[#d7dbe0]">
                    <div
                        v-for="fixture in currentWeekFixtures"
                        :key="fixture.id"
                        class="grid grid-cols-[minmax(0,1fr)_52px_minmax(0,1fr)] items-center border-b border-[#e3e6ea] px-3 py-5 text-[15px] text-[#2c333a] last:border-b-0"
                    >
                        <div class="truncate pr-2">
                            {{ fixture.home_team.name }}
                        </div>
                        <div class="text-center">
                            <span v-if="fixture.is_completed">
                                {{ fixture.home_goals }}-{{
                                    fixture.away_goals
                                }}
                            </span>
                            <span v-else>-</span>
                        </div>
                        <div class="truncate pl-2 text-right">
                            {{ fixture.away_team.name }}
                        </div>
                    </div>
                </div>
            </article>

            <article class="min-w-0">
                <header
                    class="grid grid-cols-[minmax(0,1fr)_40px] bg-[#2f353b] px-3 py-4 text-[12px] font-bold text-white"
                >
                    <div class="pr-2">Championship Predictions</div>
                    <div class="text-right">%</div>
                </header>

                <div class="border-b border-[#d7dbe0]">
                    <div
                        v-for="team in championshipPredictions"
                        :key="team.name"
                        class="grid grid-cols-[minmax(0,1fr)_40px] items-center border-b border-[#e3e6ea] px-3 py-5 text-[15px] text-[#2c333a] last:border-b-0"
                    >
                        <div class="truncate pr-2">{{ team.name }}</div>
                        <div class="text-right">{{ team.percentage }}</div>
                    </div>
                </div>
            </article>
        </section>

        <section class="mt-12 flex flex-wrap justify-center gap-4 lg:gap-12">
            <div class="flex justify-center">
                <button
                    type="button"
                    class="rounded bg-[#2aa7c7] px-9 py-3 text-[14px] font-semibold text-white transition hover:bg-[#228faa]"
                    @click="playAllWeeks"
                >
                    Play All Weeks
                </button>
            </div>

            <div class="flex justify-center">
                <button
                    type="button"
                    class="rounded bg-[#2aa7c7] px-9 py-3 text-[14px] font-semibold text-white transition hover:bg-[#228faa]"
                    @click="playNextWeek"
                >
                    Play Next Week
                </button>
            </div>

            <div class="flex justify-center">
                <button
                    type="button"
                    class="rounded bg-[#dc3545] px-9 py-3 text-[14px] font-semibold text-white transition hover:bg-[#bd2d3d]"
                    @click="resetData"
                >
                    Reset Data
                </button>
            </div>
        </section>
    </main>
</template>
