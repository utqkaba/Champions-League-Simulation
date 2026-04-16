<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    fixtures: {
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
    showSetLabelForIncomplete: {
        type: Boolean,
        default: false,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["start-edit", "save-edit", "cancel-edit"]);
</script>

<template>
    <article class="min-w-0">
        <header class="bg-[#2f353b] px-3 py-4 text-[12px] font-bold text-white">
            {{ title }}
        </header>

        <div class="border-b border-[#d7dbe0]">
            <div
                v-for="fixture in fixtures"
                :key="fixture.id"
                :class="[
                    'grid grid-cols-[minmax(0,1fr)_58px_minmax(0,1fr)_54px] items-center gap-x-1 border-b border-[#e3e6ea] px-3 text-[#2c333a] last:border-b-0',
                    compact ? 'py-4 text-[14px]' : 'py-5 text-[15px]',
                ]"
            >
                <div class="pr-2 text-center break-words leading-5">
                    {{ fixture.home_team.name }}
                </div>

                <div class="text-center">
                    <template v-if="editingFixtureId === fixture.id">
                        <div class="flex items-center justify-center gap-1">
                            <input
                                :value="scoreForm.home_goals"
                                type="number"
                                min="0"
                                class="w-8 rounded border border-[#cfd5db] px-1 py-1 text-center text-[12px]"
                                @input="
                                    scoreForm.home_goals = Number(
                                        $event.target.value,
                                    )
                                "
                            />
                            <span>-</span>
                            <input
                                :value="scoreForm.away_goals"
                                type="number"
                                min="0"
                                class="w-8 rounded border border-[#cfd5db] px-1 py-1 text-center text-[12px]"
                                @input="
                                    scoreForm.away_goals = Number(
                                        $event.target.value,
                                    )
                                "
                            />
                        </div>
                    </template>

                    <template v-else>
                        <span v-if="fixture.is_completed">
                            {{ fixture.home_goals }}-{{ fixture.away_goals }}
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
                                @click="$emit('save-edit', fixture.id)"
                            >
                                Save
                            </button>
                            <button
                                type="button"
                                class="text-[#6f767d]"
                                @click="$emit('cancel-edit')"
                            >
                                Cancel
                            </button>
                        </div>
                    </template>

                    <button
                        v-else
                        type="button"
                        class="text-[12px] font-semibold text-[#2aa7c7]"
                        @click="$emit('start-edit', fixture)"
                    >
                        {{
                            fixture.is_completed || !showSetLabelForIncomplete
                                ? "Edit"
                                : "Set"
                        }}
                    </button>
                </div>
            </div>
        </div>
    </article>
</template>
