<script setup>
import { Head, router } from "@inertiajs/vue3";
import { reactive, ref } from "vue";

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
    fixturesByMatchday: {
        type: Array,
        required: true,
    },
    showAllWeeksResults: {
        type: Boolean,
        required: true,
    },
});

const editingFixtureId = ref(null);
const scoreForm = reactive({
    home_goals: 0,
    away_goals: 0,
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

function startEditing(fixture) {
    editingFixtureId.value = fixture.id;
    scoreForm.home_goals = fixture.home_goals ?? 0;
    scoreForm.away_goals = fixture.away_goals ?? 0;
}

function cancelEditing() {
    editingFixtureId.value = null;
    scoreForm.home_goals = 0;
    scoreForm.away_goals = 0;
}

function saveFixtureResult(fixtureId) {
    router.patch(
        route("simulator.update-fixture-result", fixtureId),
        {
            home_goals: Number(scoreForm.home_goals),
            away_goals: Number(scoreForm.away_goals),
        },
        {
            preserveScroll: true,
            onSuccess: () => cancelEditing(),
        },
    );
}
</script>

<template>
    <Head title="Simulation" />

    <main class="mx-auto min-h-screen max-w-[1500px] px-4 py-4 sm:px-6 lg:px-8">
        <h1 class="mb-6 text-center text-[22px] font-light text-[#6f767d]">
            Simulation
        </h1>

        <section
            class="grid gap-6 lg:grid-cols-2 xl:grid-cols-[minmax(0,1.46fr)_minmax(326px,0.98fr)_minmax(228px,0.66fr)] xl:gap-8"
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
                        class="grid grid-cols-[minmax(0,1fr)_58px_minmax(0,1fr)_54px] items-center gap-x-1 border-b border-[#e3e6ea] px-3 py-5 text-[15px] text-[#2c333a] last:border-b-0"
                    >
                        <div class="pr-2 text-center break-words leading-5">
                            {{ fixture.home_team.name }}
                        </div>
                        <div class="text-center">
                            <template v-if="editingFixtureId === fixture.id">
                                <div
                                    class="flex items-center justify-center gap-1"
                                >
                                    <input
                                        v-model.number="scoreForm.home_goals"
                                        type="number"
                                        min="0"
                                        class="w-8 rounded border border-[#cfd5db] px-1 py-1 text-center text-[12px]"
                                    />
                                    <span>-</span>
                                    <input
                                        v-model.number="scoreForm.away_goals"
                                        type="number"
                                        min="0"
                                        class="w-8 rounded border border-[#cfd5db] px-1 py-1 text-center text-[12px]"
                                    />
                                </div>
                            </template>
                            <template v-else>
                                <span v-if="fixture.is_completed">
                                    {{ fixture.home_goals }}-{{
                                        fixture.away_goals
                                    }}
                                </span>
                                <span v-else>-</span>
                            </template>
                        </div>
                        <div class="pl-2 text-center break-words leading-5">
                            {{ fixture.away_team.name }}
                        </div>
                        <div class="flex justify-end">
                            <template v-if="editingFixtureId === fixture.id">
                                <div class="flex gap-2 text-[12px]">
                                    <button
                                        type="button"
                                        class="text-[#2aa7c7]"
                                        @click="saveFixtureResult(fixture.id)"
                                    >
                                        Save
                                    </button>
                                    <button
                                        type="button"
                                        class="text-[#6f767d]"
                                        @click="cancelEditing"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </template>
                            <button
                                v-else
                                type="button"
                                class="text-[12px] font-semibold text-[#2aa7c7]"
                                @click="startEditing(fixture)"
                            >
                                {{ fixture.is_completed ? "Edit" : "Set" }}
                            </button>
                        </div>
                    </div>
                </div>
            </article>

            <article class="min-w-0">
                <header
                    class="grid grid-cols-[minmax(0,1fr)_34px] bg-[#2f353b] px-3 py-4 text-[12px] font-bold text-white"
                >
                    <div class="pr-2">Championship Predictions</div>
                    <div class="text-right">%</div>
                </header>

                <div class="border-b border-[#d7dbe0]">
                    <div
                        v-for="team in championshipPredictions"
                        :key="team.name"
                        class="grid grid-cols-[minmax(0,1fr)_34px] items-center border-b border-[#e3e6ea] px-3 py-5 text-[15px] text-[#2c333a] last:border-b-0"
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

        <section
            v-if="showAllWeeksResults"
            class="my-12 grid gap-5 md:grid-cols-2 xl:grid-cols-3"
        >
            <div
                class="md:col-span-2 xl:col-span-3 text-center text-[20px] font-light text-[#6f767d]"
            >
                All Result
            </div>

            <article
                v-for="matchday in fixturesByMatchday"
                :key="`summary-week-${matchday[0]?.matchday}`"
                class="min-w-0"
            >
                <header
                    class="bg-[#2f353b] px-3 py-4 text-[12px] font-bold text-white"
                >
                    Week {{ matchday[0]?.matchday }}
                </header>

                <div class="border-b border-[#d7dbe0]">
                    <div
                        v-for="fixture in matchday"
                        :key="fixture.id"
                        class="grid grid-cols-[minmax(0,1fr)_58px_minmax(0,1fr)_54px] items-center gap-x-1 border-b border-[#e3e6ea] px-3 py-4 text-[14px] text-[#2c333a] last:border-b-0"
                    >
                        <div class="pr-2 text-center break-words leading-5">
                            {{ fixture.home_team.name }}
                        </div>
                        <div class="text-center">
                            <template v-if="editingFixtureId === fixture.id">
                                <div
                                    class="flex items-center justify-center gap-1"
                                >
                                    <input
                                        v-model.number="scoreForm.home_goals"
                                        type="number"
                                        min="0"
                                        class="w-8 rounded border border-[#cfd5db] px-1 py-1 text-center text-[12px]"
                                    />
                                    <span>-</span>
                                    <input
                                        v-model.number="scoreForm.away_goals"
                                        type="number"
                                        min="0"
                                        class="w-8 rounded border border-[#cfd5db] px-1 py-1 text-center text-[12px]"
                                    />
                                </div>
                            </template>
                            <template v-else>
                                {{ fixture.home_goals }}-{{
                                    fixture.away_goals
                                }}
                            </template>
                        </div>
                        <div class="pl-2 text-center break-words leading-5">
                            {{ fixture.away_team.name }}
                        </div>
                        <div class="flex justify-end">
                            <template v-if="editingFixtureId === fixture.id">
                                <div class="flex gap-2 text-[12px]">
                                    <button
                                        type="button"
                                        class="text-[#2aa7c7]"
                                        @click="saveFixtureResult(fixture.id)"
                                    >
                                        Save
                                    </button>
                                    <button
                                        type="button"
                                        class="text-[#6f767d]"
                                        @click="cancelEditing"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </template>
                            <button
                                v-else
                                type="button"
                                class="text-[12px] font-semibold text-[#2aa7c7]"
                                @click="startEditing(fixture)"
                            >
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
            </article>
        </section>
    </main>
</template>
