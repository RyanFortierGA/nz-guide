<script setup>
import SeoHead from '@/Components/SeoHead.vue';
import ExploreLayout from '@/Layouts/ExploreLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    trip: Object,
    blockTypes: Object,
    categoryColors: Object,
});

const copied = ref(false);
const addingForDay = ref(null);

const blockForm = useForm({
    type: 'meal',
    title: '',
    notes: '',
    link_url: '',
    day_index: null,
    planned_time: '',
});

const unassignedPlaces = computed(() => props.trip.locations.filter((l) => !l.day_index));
const unassignedBlocks = computed(() => props.trip.blocks.filter((b) => !b.day_index));

const typeMeta = {
    meal: { emoji: '🍽', chip: 'bg-[#fff4e5] text-[#9a5b00]' },
    hangout: { emoji: '☕', chip: 'bg-[#eef6ff] text-[#1d4f91]' },
    find: { emoji: '✨', chip: 'bg-[#f3eefc] text-[#5b3d9a]' },
    note: { emoji: '✎', chip: 'bg-[#f3f3f3] text-[#444]' },
};

function itemsForDay(dayIndex) {
    const places = props.trip.locations
        .filter((l) => Number(l.day_index) === dayIndex)
        .map((l) => ({ ...l, kind: 'location', sortKey: l.planned_time || '99:99' }));

    const blocks = props.trip.blocks
        .filter((b) => Number(b.day_index) === dayIndex)
        .map((b) => ({ ...b, sortKey: b.planned_time || '99:99' }));

    return [...places, ...blocks].sort((a, b) => String(a.sortKey).localeCompare(String(b.sortKey)));
}

function assign(locationId, dayIndex, plannedTime = null) {
    router.patch(
        route('trip.assign', locationId),
        { day_index: dayIndex, planned_time: plannedTime },
        { preserveScroll: true },
    );
}

function unassign(locationId) {
    router.patch(route('trip.assign', locationId), { day_index: null, planned_time: null }, { preserveScroll: true });
}

function setPlaceTime(locationId, dayIndex, event) {
    assign(locationId, dayIndex, event.target.value || null);
}

function openAdd(dayIndex, type = 'meal') {
    addingForDay.value = dayIndex;
    blockForm.type = type;
    blockForm.title = type === 'meal' ? 'Dinner nearby' : type === 'hangout' ? 'Hang out / coffee' : '';
    blockForm.notes = '';
    blockForm.link_url = '';
    blockForm.day_index = dayIndex;
    blockForm.planned_time = type === 'meal' ? '19:00' : '';
}

function submitBlock() {
    blockForm.post(route('trip.blocks.store'), {
        preserveScroll: true,
        onSuccess: () => {
            addingForDay.value = null;
            blockForm.reset('title', 'notes', 'link_url', 'planned_time');
        },
    });
}

function updateBlock(blockId, payload) {
    router.patch(route('trip.blocks.update', blockId), payload, { preserveScroll: true });
}

function removeBlock(blockId) {
    router.delete(route('trip.blocks.destroy', blockId), { preserveScroll: true });
}

async function copyShare() {
    try {
        await navigator.clipboard.writeText(props.trip.share_url);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        window.prompt('Copy this link', props.trip.share_url);
    }
}
</script>

<template>
    <SeoHead
        :title="trip.share_title"
        :description="trip.share_blurb"
        :url="trip.share_url"
        image="/og-share.jpg"
    />

    <ExploreLayout>
        <div class="border-b border-[var(--line)] px-5 py-6 sm:px-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-[var(--muted)]">Day-by-day</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ trip.name }}</h1>
                    <p class="mt-2 text-[14.5px] text-[#444]">
                        <span v-if="trip.visitor_name">For {{ trip.visitor_name }} · </span>
                        Arrive {{ trip.arrives_label }} · Leave {{ trip.departs_label }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('trip.setup')"
                        class="rounded-full border border-[var(--ink)] px-3 py-2 text-[11px] font-semibold uppercase tracking-wide"
                    >
                        Edit dates
                    </Link>
                    <button
                        type="button"
                        class="rounded-full bg-[var(--ink)] px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-white"
                        @click="copyShare"
                    >
                        {{ copied ? 'Link copied' : 'Copy share link' }}
                    </button>
                    <Link
                        :href="route('explore')"
                        class="rounded-full border border-[var(--line)] px-3 py-2 text-[11px] font-semibold uppercase tracking-wide"
                    >
                        Explore more
                    </Link>
                </div>
            </div>
            <p class="mt-4 max-w-2xl text-[14px] font-light leading-relaxed text-[var(--muted)]">
                {{ trip.share_blurb }}
            </p>
            <p class="mt-2 max-w-2xl text-[13px] text-[var(--muted)]">
                Share the link and guests can add locations they discover. Use meal / hang-out blocks to fill the gaps between places.
            </p>
        </div>

        <div class="grid gap-6 px-5 py-6 lg:grid-cols-[280px_1fr] sm:px-8">
            <aside class="space-y-4">
                <div>
                    <h2 class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">Unscheduled places</h2>
                    <p class="mt-1 text-[13px] text-[var(--muted)]">Saved from explore.</p>

                    <div
                        v-if="!unassignedPlaces.length"
                        class="mt-3 rounded-2xl border border-dashed border-[var(--line)] p-4 text-[13px] text-[var(--muted)]"
                    >
                        No loose places — add more from explore anytime.
                    </div>

                    <div
                        v-for="place in unassignedPlaces"
                        :key="'p-' + place.id"
                        class="mt-3 rounded-2xl border border-[var(--line)] bg-white p-3"
                    >
                        <div class="flex gap-3">
                            <div
                                class="h-14 w-16 shrink-0 rounded-xl bg-cover bg-center"
                                :style="{ backgroundImage: `url('${place.image_url}')` }"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ place.name }}</p>
                                <p class="text-[11px] text-[var(--muted)]">{{ place.travel_time }}</p>
                                <select
                                    class="mt-2 w-full rounded-lg border-gray-200 text-xs"
                                    @change="assign(place.id, Number($event.target.value) || null)"
                                >
                                    <option value="">Add to day…</option>
                                    <option v-for="day in trip.days" :key="day.index" :value="day.index">
                                        Day {{ day.index }} · {{ day.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">Loose fillers & locations</h2>
                    <div
                        v-for="block in unassignedBlocks"
                        :key="'b-' + block.id"
                        class="mt-3 rounded-2xl border border-[var(--line)] bg-white p-3"
                    >
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide" :class="typeMeta[block.type]?.chip">
                            {{ block.type_label }}
                        </span>
                        <p class="mt-1 text-sm font-semibold">{{ block.title }}</p>
                        <p v-if="block.added_by_name" class="text-[11px] text-[var(--muted)]">from {{ block.added_by_name }}</p>
                        <select
                            class="mt-2 w-full rounded-lg border-gray-200 text-xs"
                            @change="updateBlock(block.id, { day_index: Number($event.target.value) || null })"
                        >
                            <option value="">Add to day…</option>
                            <option v-for="day in trip.days" :key="day.index" :value="day.index">
                                Day {{ day.index }} · {{ day.label }}
                            </option>
                        </select>
                    </div>
                </div>
            </aside>

            <div class="space-y-5">
                <article
                    v-for="day in trip.days"
                    :key="day.index"
                    class="rounded-3xl border border-[var(--line)] bg-white p-5"
                >
                    <header class="mb-4 flex flex-wrap items-end justify-between gap-2 border-b border-[var(--line)] pb-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">
                                Day {{ day.index }}
                            </p>
                            <h3 class="text-xl font-semibold tracking-tight">{{ day.label }}</h3>
                        </div>
                        <div class="text-right text-[12px] text-[var(--muted)]">
                            <p v-if="day.is_arrival">Lands {{ day.arrival_time }}</p>
                            <p v-if="day.is_departure">Heads out {{ day.departure_time }}</p>
                        </div>
                    </header>

                    <div class="mb-4 flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-full border border-[var(--line)] px-3 py-1.5 text-[11px] font-semibold hover:border-[var(--ink)]"
                            @click="openAdd(day.index, 'meal')"
                        >
                            + Meal
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-[var(--line)] px-3 py-1.5 text-[11px] font-semibold hover:border-[var(--ink)]"
                            @click="openAdd(day.index, 'hangout')"
                        >
                            + Hang out
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-[var(--line)] px-3 py-1.5 text-[11px] font-semibold hover:border-[var(--ink)]"
                            @click="openAdd(day.index, 'find')"
                        >
                            + Location
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-[var(--line)] px-3 py-1.5 text-[11px] font-semibold hover:border-[var(--ink)]"
                            @click="openAdd(day.index, 'note')"
                        >
                            + Note
                        </button>
                    </div>

                    <form
                        v-if="addingForDay === day.index"
                        class="mb-4 space-y-3 rounded-2xl bg-[#fafafa] p-4"
                        @submit.prevent="submitBlock"
                    >
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                                Type
                                <select v-model="blockForm.type" class="mt-1 block w-full rounded-lg border-gray-200 text-sm">
                                    <option v-for="(label, key) in blockTypes" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </label>
                            <label class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                                Time (optional)
                                <input v-model="blockForm.planned_time" type="time" class="mt-1 block w-full rounded-lg border-gray-200 text-sm" />
                            </label>
                        </div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                            Title
                            <input v-model="blockForm.title" class="mt-1 block w-full rounded-lg border-gray-200 text-sm" required placeholder="Brunch at Federal Delicatessen" />
                        </label>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                            Notes
                            <textarea v-model="blockForm.notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-200 text-sm" placeholder="Book ahead / walk from home / their shout" />
                        </label>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                            Link (optional)
                            <input v-model="blockForm.link_url" type="url" class="mt-1 block w-full rounded-lg border-gray-200 text-sm" placeholder="https://…" />
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" class="rounded-full bg-[var(--ink)] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white" :disabled="blockForm.processing">
                                Add to day
                            </button>
                            <button type="button" class="text-[11px] font-semibold text-[var(--muted)]" @click="addingForDay = null">Cancel</button>
                        </div>
                    </form>

                    <div v-if="!itemsForDay(day.index).length" class="text-[13px] text-[var(--muted)]">
                        Quiet day — add a meal, hang-out, or pull a place in from the side.
                    </div>

                    <ul class="space-y-3">
                        <li
                            v-for="item in itemsForDay(day.index)"
                            :key="item.kind + '-' + item.id"
                            class="flex flex-wrap items-center gap-3 rounded-2xl bg-[#fafafa] p-3"
                        >
                            <template v-if="item.kind === 'location'">
                                <div
                                    class="h-14 w-20 shrink-0 rounded-xl bg-cover bg-center"
                                    :style="{ backgroundImage: `url('${item.image_url}')` }"
                                />
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold">{{ item.name }}</p>
                                    <p class="line-clamp-1 text-[12.5px] text-[var(--muted)]">{{ item.description }}</p>
                                </div>
                                <label class="flex items-center gap-2 text-[11px] text-[var(--muted)]">
                                    Time
                                    <input
                                        type="time"
                                        class="rounded-lg border-gray-200 text-xs"
                                        :value="item.planned_time || ''"
                                        @change="setPlaceTime(item.id, day.index, $event)"
                                    />
                                </label>
                                <button
                                    type="button"
                                    class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)] hover:text-[var(--ink)]"
                                    @click="unassign(item.id)"
                                >
                                    Remove day
                                </button>
                            </template>

                            <template v-else>
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-white text-xl">
                                    {{ typeMeta[item.type]?.emoji || '·' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide" :class="typeMeta[item.type]?.chip">
                                            {{ item.type_label }}
                                        </span>
                                        <p class="font-semibold">{{ item.title }}</p>
                                    </div>
                                    <p v-if="item.notes" class="line-clamp-2 text-[12.5px] text-[var(--muted)]">{{ item.notes }}</p>
                                    <p v-if="item.source === 'guest'" class="text-[11px] text-[var(--muted)]">
                                        Added by {{ item.added_by_name || 'a guest' }}
                                    </p>
                                    <a
                                        v-if="item.link_url"
                                        :href="item.link_url"
                                        target="_blank"
                                        rel="noopener"
                                        class="text-[11px] font-medium text-[var(--ink)] underline underline-offset-2"
                                    >
                                        Open link
                                    </a>
                                </div>
                                <label class="flex items-center gap-2 text-[11px] text-[var(--muted)]">
                                    Time
                                    <input
                                        type="time"
                                        class="rounded-lg border-gray-200 text-xs"
                                        :value="item.planned_time || ''"
                                        @change="updateBlock(item.id, { planned_time: $event.target.value || null, day_index: day.index })"
                                    />
                                </label>
                                <button
                                    type="button"
                                    class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)] hover:text-[var(--ink)]"
                                    @click="updateBlock(item.id, { day_index: null, planned_time: null })"
                                >
                                    Unschedule
                                </button>
                                <button
                                    type="button"
                                    class="text-[11px] font-semibold uppercase tracking-wide text-red-600/70 hover:text-red-700"
                                    @click="removeBlock(item.id)"
                                >
                                    Delete
                                </button>
                            </template>
                        </li>
                    </ul>
                </article>
            </div>
        </div>
    </ExploreLayout>
</template>
