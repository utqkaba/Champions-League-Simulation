<script setup>
import ActionButton from "@/Components/Simulator/ActionButton.vue";
import ChampionshipPredictionsTable from "@/Components/Simulator/ChampionshipPredictionsTable.vue";
import FixtureResultsTable from "@/Components/Simulator/FixtureResultsTable.vue";
import MatchdayGrid from "@/Components/Simulator/MatchdayGrid.vue";
import PageHeading from "@/Components/Simulator/PageHeading.vue";
import StandingsTable from "@/Components/Simulator/StandingsTable.vue";
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

    <main class="mx-auto min-h-screen max-w-[1500px] px-4 py-6 sm:px-6 lg:px-8">
        <PageHeading title="Simulation" centered margin-class="mb-10" />

        <section
            class="grid gap-6 lg:grid-cols-2 xl:grid-cols-[minmax(0,1.46fr)_minmax(326px,0.98fr)_minmax(228px,0.66fr)] xl:gap-8"
        >
            <StandingsTable :standings="standings" />

            <FixtureResultsTable
                :title="`Week ${currentWeek}`"
                :fixtures="currentWeekFixtures"
                :editing-fixture-id="editingFixtureId"
                :score-form="scoreForm"
                show-set-label-for-incomplete
                @start-edit="startEditing"
                @save-edit="saveFixtureResult"
                @cancel-edit="cancelEditing"
            />

            <ChampionshipPredictionsTable :teams="championshipPredictions" />
        </section>

        <section class="mt-12 flex flex-wrap justify-center gap-4 lg:gap-12">
            <div class="flex justify-center">
                <ActionButton label="Play All Weeks" @click="playAllWeeks" />
            </div>

            <div class="flex justify-center">
                <ActionButton label="Play Next Week" @click="playNextWeek" />
            </div>

            <div class="flex justify-center">
                <ActionButton
                    label="Reset Data"
                    variant="danger"
                    @click="resetData"
                />
            </div>
        </section>

        <MatchdayGrid
            v-if="showAllWeeksResults"
            title="All Result"
            :matchdays="fixturesByMatchday"
            :editing-fixture-id="editingFixtureId"
            :score-form="scoreForm"
            @start-edit="startEditing"
            @save-edit="saveFixtureResult"
            @cancel-edit="cancelEditing"
        />
    </main>
</template>
