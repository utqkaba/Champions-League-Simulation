<script setup>
import { Head, router } from '@inertiajs/vue3';

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
        <h1 class="mb-10 text-center text-[22px] font-light text-[#7c838b]">Generated Fixtures</h1>

        <section class="grid grid-cols-1 gap-x-7 gap-y-5 md:grid-cols-2 xl:grid-cols-4">
            <article
                v-for="matchday in fixturesByMatchday"
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

        <div class="mt-10">
            <button
                type="button"
                class="rounded bg-[#2aa7c7] px-4 py-2 text-[14px] font-semibold text-white transition hover:bg-[#228faa]"
                @click="startSimulation"
            >
                Start Simulation
            </button>
        </div>
    </main>
</template>
