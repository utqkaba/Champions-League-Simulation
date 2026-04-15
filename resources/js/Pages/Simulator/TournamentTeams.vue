<script setup>
import { Head, router } from '@inertiajs/vue3';

defineProps({
    teams: {
        type: Array,
        required: true,
    },
});

function generateFixtures() {
    router.post(route('simulator.generate-fixtures'));
}
</script>

<template>
    <Head title="Tournament Teams" />

    <main class="mx-auto min-h-screen max-w-[900px] px-4 py-14">
        <div class="mx-auto max-w-[805px]">
            <h1 class="mb-5 text-[22px] font-light text-[#6f767d]">Tournament Teams</h1>

            <div class="overflow-hidden">
                <div class="bg-[#2f353b] px-3 py-4 text-[16px] font-bold text-white">Team Name</div>

                <div
                    v-for="team in teams"
                    :key="team.id"
                    class="border-b border-[#e3e6ea] px-3 py-5 text-[15px] text-[#2c333a]"
                >
                    {{ team.name }}
                </div>
            </div>

            <div class="mt-10">
                <button
                    type="button"
                    class="rounded bg-[#27a8c7] px-4 py-3 text-[14px] font-semibold text-white transition hover:bg-[#2090ab]"
                    @click="generateFixtures"
                >
                    Generate Fixtures
                </button>
            </div>
        </div>
    </main>
</template>
