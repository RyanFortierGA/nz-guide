<script setup>
import SeoHead from '@/Components/SeoHead.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    trip: Object,
    seo: Object,
    token: String,
    flashSuccess: String,
    categoryColors: Object,
});

const page = usePage();
const success = computed(() => props.flashSuccess || page.props.flash?.success);

const form = useForm({
    type: 'find',
    title: '',
    notes: '',
    link_url: '',
    added_by_name: '',
    day_index: '',
});

const typeMeta = {
    meal: { emoji: '🍽', chip: 'bg-[#fff4e5] text-[#9a5b00]', label: 'Meal' },
    hangout: { emoji: '☕', chip: 'bg-[#eef6ff] text-[#1d4f91]', label: 'Hang out' },
    find: { emoji: '✨', chip: 'bg-[#f3eefc] text-[#5b3d9a]', label: 'Location' },
    note: { emoji: '✎', chip: 'bg-[#f3f3f3] text-[#444]', label: 'Note' },
};

function itemsForDay(dayIndex) {
    const places = props.trip.locations
        .filter((l) => Number(l.day_index) === dayIndex)
        .map((l) => ({ ...l, kind: 'location', sortKey: l.planned_time || '99:99' }));

    const blocks = (props.trip.blocks || [])
        .filter((b) => Number(b.day_index) === dayIndex)
        .map((b) => ({ ...b, sortKey: b.planned_time || '99:99' }));

    return [...places, ...blocks].sort((a, b) => String(a.sortKey).localeCompare(String(b.sortKey)));
}

const looseIdeas = computed(() => props.trip.locations.filter((l) => !l.day_index));
const looseBlocks = computed(() => (props.trip.blocks || []).filter((b) => !b.day_index));

function submitFind() {
    form
        .transform((data) => ({
            ...data,
            day_index: data.day_index ? Number(data.day_index) : null,
        }))
        .post(route('share.suggest', props.token), {
            preserveScroll: true,
            onSuccess: () => form.reset('title', 'notes', 'link_url', 'day_index'),
        });
}
</script>

<template>
    <SeoHead
        :title="seo.title"
        :description="seo.description"
        :image="seo.image"
        :url="seo.url"
    />

    <div class="min-h-screen bg-[#f7f5f2] text-[var(--ink)]">
        <div
            class="relative overflow-hidden border-b border-[var(--line)] bg-cover bg-center"
            :style="{ backgroundImage: `linear-gradient(to top, rgba(20,20,20,.72), rgba(20,20,20,.25)), url('${seo.image}')` }"
        >
            <div class="mx-auto max-w-3xl px-5 py-14 sm:px-8 sm:py-20">
                <p class="text-[11px] font-medium uppercase tracking-[0.22em] text-white/70">
                    A visit guide from {{ trip.host_name || 'us' }}
                </p>
                <h1 class="mt-3 max-w-xl text-[clamp(28px,5vw,44px)] font-semibold leading-tight tracking-tight text-white">
                    {{ trip.share_title }}
                </h1>
                <p class="mt-4 max-w-xl text-[15px] font-light leading-relaxed text-white/85">
                    {{ trip.share_blurb }}
                </p>
                <p v-if="trip.arrives_label" class="mt-5 text-[13px] text-white/75">
                    {{ trip.arrives_label }}
                    <span v-if="trip.departs_label"> → {{ trip.departs_label }}</span>
                </p>
            </div>
        </div>

        <div class="mx-auto max-w-3xl space-y-8 px-5 py-10 sm:px-8">
            <p
                v-if="success"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[14px] text-emerald-900"
            >
                {{ success }}
            </p>

            <section class="rounded-3xl bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-xl font-semibold">Found something good?</h2>
                <p class="mt-1 text-[14px] text-[var(--muted)]">
                    Drop it on the plan — a café, a lookout, a random shop. No account needed.
                </p>

                <form class="mt-5 space-y-3" @submit.prevent="submitFind">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                            What is it?
                            <select v-model="form.type" class="mt-1 block w-full rounded-lg border-gray-200 text-sm">
                                <option value="find">A location</option>
                                <option value="meal">A meal idea</option>
                                <option value="hangout">Hang out / coffee</option>
                                <option value="note">Just a note</option>
                            </select>
                        </label>
                        <label class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                            Your name (optional)
                            <input v-model="form.added_by_name" class="mt-1 block w-full rounded-lg border-gray-200 text-sm" placeholder="Sam" />
                        </label>
                    </div>

                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                        Title
                        <input v-model="form.title" required class="mt-1 block w-full rounded-lg border-gray-200 text-sm" placeholder="Best flat white we found on K Road" />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                    </label>

                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                        Why it’s good
                        <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-200 text-sm" placeholder="Quiet courtyard, open late, walkable from home…" />
                    </label>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                            Link (Maps / Instagram / whatever)
                            <input v-model="form.link_url" type="url" class="mt-1 block w-full rounded-lg border-gray-200 text-sm" placeholder="https://…" />
                            <p v-if="form.errors.link_url" class="mt-1 text-xs text-red-600">{{ form.errors.link_url }}</p>
                        </label>
                        <label class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                            Put on a day? (optional)
                            <select v-model="form.day_index" class="mt-1 block w-full rounded-lg border-gray-200 text-sm">
                                <option value="">Keep as a loose idea</option>
                                <option v-for="day in trip.days" :key="day.index" :value="day.index">
                                    Day {{ day.index }} · {{ day.label }}
                                </option>
                            </select>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="rounded-full bg-[var(--ink)] px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-white disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        Add to the plan
                    </button>
                </form>
            </section>

            <section v-for="day in trip.days" :key="day.index" class="rounded-3xl bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">
                            Day {{ day.index }}
                        </p>
                        <h2 class="text-xl font-semibold">{{ day.label }}</h2>
                    </div>
                    <div class="text-right text-[12px] text-[var(--muted)]">
                        <p v-if="day.is_arrival">Touchdown {{ day.arrival_time }}</p>
                        <p v-if="day.is_departure">Depart {{ day.departure_time }}</p>
                    </div>
                </div>

                <p v-if="!itemsForDay(day.index).length" class="text-[14px] text-[var(--muted)]">
                    Soft day — coffee, jet lag, or whatever feels good.
                </p>

                <div class="space-y-4">
                    <article
                        v-for="item in itemsForDay(day.index)"
                        :key="item.kind + '-' + item.id"
                        class="overflow-hidden rounded-2xl border border-[var(--line)]"
                    >
                        <template v-if="item.kind === 'location'">
                            <div
                                class="h-36 bg-cover bg-center sm:h-44"
                                :style="{ backgroundImage: `url('${item.image_url}')` }"
                            />
                            <div class="p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-semibold">{{ item.name }}</h3>
                                    <span
                                        v-if="item.planned_time"
                                        class="rounded-full bg-black/5 px-2 py-0.5 text-[11px] font-medium"
                                    >
                                        ~{{ item.planned_time }}
                                    </span>
                                    <span class="text-[11px] text-[var(--muted)]">{{ item.travel_time }} from home</span>
                                </div>
                                <p class="mt-2 text-[14px] font-light leading-relaxed text-[#444]">
                                    {{ item.description }}
                                </p>
                            </div>
                        </template>

                        <template v-else>
                            <div class="flex gap-4 p-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#f7f5f2] text-xl">
                                    {{ typeMeta[item.type]?.emoji || '·' }}
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide" :class="typeMeta[item.type]?.chip">
                                            {{ item.type_label || typeMeta[item.type]?.label }}
                                        </span>
                                        <h3 class="text-lg font-semibold">{{ item.title }}</h3>
                                        <span
                                            v-if="item.planned_time"
                                            class="rounded-full bg-black/5 px-2 py-0.5 text-[11px] font-medium"
                                        >
                                            ~{{ item.planned_time }}
                                        </span>
                                    </div>
                                    <p v-if="item.notes" class="mt-2 text-[14px] font-light leading-relaxed text-[#444]">
                                        {{ item.notes }}
                                    </p>
                                    <p v-if="item.source === 'guest'" class="mt-1 text-[12px] text-[var(--muted)]">
                                        Suggested by {{ item.added_by_name || 'a guest' }}
                                    </p>
                                    <a
                                        v-if="item.link_url"
                                        :href="item.link_url"
                                        target="_blank"
                                        rel="noopener"
                                        class="mt-2 inline-block text-[13px] font-medium underline underline-offset-2"
                                    >
                                        Open link
                                    </a>
                                </div>
                            </div>
                        </template>
                    </article>
                </div>
            </section>

            <section v-if="looseIdeas.length || looseBlocks.length" class="rounded-3xl bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-xl font-semibold">Other ideas if the weather plays along</h2>
                <p class="mt-1 text-[14px] text-[var(--muted)]">Not locked to a day — pick and choose.</p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div
                        v-for="place in looseIdeas"
                        :key="'loc-' + place.id"
                        class="overflow-hidden rounded-2xl border border-[var(--line)]"
                    >
                        <div
                            class="h-28 bg-cover bg-center"
                            :style="{ backgroundImage: `url('${place.image_url}')` }"
                        />
                        <div class="p-3">
                            <p class="font-semibold">{{ place.name }}</p>
                            <p class="mt-1 line-clamp-2 text-[13px] text-[var(--muted)]">{{ place.description }}</p>
                        </div>
                    </div>

                    <div
                        v-for="block in looseBlocks"
                        :key="'blk-' + block.id"
                        class="rounded-2xl border border-[var(--line)] p-4"
                    >
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide" :class="typeMeta[block.type]?.chip">
                            {{ block.type_label || typeMeta[block.type]?.label }}
                        </span>
                        <p class="mt-2 font-semibold">{{ block.title }}</p>
                        <p v-if="block.notes" class="mt-1 line-clamp-3 text-[13px] text-[var(--muted)]">{{ block.notes }}</p>
                        <p v-if="block.source === 'guest'" class="mt-2 text-[11px] text-[var(--muted)]">
                            from {{ block.added_by_name || 'a guest' }}
                        </p>
                    </div>
                </div>
            </section>

            <p class="pb-8 text-center text-[13px] text-[var(--muted)]">
                Made for people coming to stay —
                <Link :href="route('explore')" class="underline underline-offset-2">explore more nearby</Link>
            </p>
        </div>
    </div>
</template>
