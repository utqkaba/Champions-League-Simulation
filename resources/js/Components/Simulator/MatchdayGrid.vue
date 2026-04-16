<script setup>
import FixtureResultsTable from "./FixtureResultsTable.vue";
import PageHeading from "./PageHeading.vue";

defineProps({
    title: {
        type: String,
        default: "",
    },
    matchdays: {
        type: Array,
        required: true,
    },
    editingFixtureId: {
        type: Number,
        default: null,
    },
    scoreForm: {
        type: Object,
        required: true,
    },
});

defineEmits(["start-edit", "save-edit", "cancel-edit"]);
</script>

<template>
    <section class="my-12 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        <PageHeading
            v-if="title"
            :title="title"
            centered
            margin-class="md:col-span-2 xl:col-span-3 mb-0"
        />

        <FixtureResultsTable
            v-for="matchday in matchdays"
            :key="`summary-week-${matchday[0]?.matchday}`"
            :title="`Week ${matchday[0]?.matchday}`"
            :fixtures="matchday"
            :editing-fixture-id="editingFixtureId"
            :score-form="scoreForm"
            compact
            @start-edit="$emit('start-edit', $event)"
            @save-edit="$emit('save-edit', $event)"
            @cancel-edit="$emit('cancel-edit')"
        />
    </section>
</template>
