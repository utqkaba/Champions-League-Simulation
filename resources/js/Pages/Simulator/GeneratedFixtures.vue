<script setup>
import { Head, router } from '@inertiajs/vue3';
import ActionButton from '@/Components/Simulator/ActionButton.vue';
import MatchdayPreviewGrid from '@/Components/Simulator/MatchdayPreviewGrid.vue';
import PageHeading from '@/Components/Simulator/PageHeading.vue';

defineProps({
    fixturesByMatchday: {
        type: Array,
        required: true,
    },
});

function startSimulation() {
    router.get(route('simulator.simulation'));
}
</script>

<template>
    <Head title="Generated Fixtures" />

    <main class="mx-auto min-h-screen max-w-[1280px] px-3 py-6">
        <PageHeading title="Generated Fixtures" centered margin-class="mb-10" />

        <MatchdayPreviewGrid :matchdays="fixturesByMatchday" />

        <div class="mt-10">
            <ActionButton
                label="Start Simulation"
                size="compact"
                @click="startSimulation"
            />
        </div>
    </main>
</template>
