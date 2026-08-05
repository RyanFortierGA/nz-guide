<script setup>
import { ICONS } from '@/icons';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    location: { type: Object, required: true },
    categoryColors: { type: Object, required: true },
    inTrip: { type: Boolean, default: false },
    show: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const softBadge = computed(() => {
    const map = {
        flying: { bg: '#ffe4e4', fg: '#c43b40' },
        weekend: { bg: '#fdefd6', fg: '#9a6400' },
        local: { bg: '#e3f0fd', fg: '#1d5fa8' },
    };
    return map[props.location.category] || { bg: '#f3f3f3', fg: '#15181c' };
});

function toggleTrip() {
    if (!user.value) {
        router.visit(route('login'));
        return;
    }
    if (props.inTrip) {
        router.delete(route('trip.remove', props.location.id), { preserveScroll: true });
    } else {
        router.post(route('trip.add', props.location.id), {}, { preserveScroll: true });
    }
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[2000] flex items-end justify-center bg-black/45 p-4 sm:items-center"
            @click.self="emit('close')"
        >
            <div class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-[28px] bg-white shadow-2xl">
                <div
                    class="relative h-56 bg-cover bg-center sm:h-72"
                    :style="{ backgroundImage: `url('${location.image_url}')` }"
                >
                    <button
                        type="button"
                        class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-xl leading-none shadow"
                        @click="emit('close')"
                    >
                        ×
                    </button>
                    <div class="absolute bottom-4 left-4 right-4 flex flex-wrap items-end justify-between gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[12px] font-semibold"
                            :style="{ background: softBadge.bg, color: softBadge.fg }"
                            v-html="ICONS[location.mode] + ' ' + location.travel_time"
                        />
                        <a
                            v-if="location.maps_url"
                            :href="location.maps_url"
                            target="_blank"
                            rel="noopener"
                            class="rounded-full bg-white/95 px-3 py-1.5 text-[11px] font-semibold shadow"
                        >
                            Photos & Maps ›
                        </a>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <h2 class="text-2xl font-semibold tracking-tight sm:text-[28px]">{{ location.name }}</h2>
                    <p class="mt-3 text-[15px] font-light leading-relaxed text-[#444]">
                        {{ location.description }}
                    </p>

                    <p v-if="location.best_time" class="mono mt-4 text-[11px] uppercase tracking-wide text-[var(--muted)]">
                        Best time · {{ location.best_time }}
                    </p>

                    <div
                        v-if="location.cost_estimate"
                        class="mt-6 rounded-2xl border border-[var(--line)] bg-[#f7f5f2] p-4"
                    >
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">
                                    Estimated add-on
                                </p>
                                <p class="mt-1 text-[15px] font-medium leading-snug">
                                    {{ location.cost_estimate.blurb }}
                                </p>
                            </div>
                            <p class="text-2xl font-semibold tracking-tight tabular-nums">
                                ~${{ location.cost_estimate.total.toLocaleString() }}
                            </p>
                        </div>
                        <ul class="mt-3 space-y-1.5 border-t border-[var(--line)] pt-3">
                            <li
                                v-for="(line, i) in location.cost_estimate.lines"
                                :key="i"
                                class="flex items-start justify-between gap-3 text-[13px]"
                            >
                                <div class="min-w-0">
                                    <p class="font-medium">{{ line.label }}</p>
                                    <p v-if="line.detail" class="text-[11.5px] text-[var(--muted)]">{{ line.detail }}</p>
                                </div>
                                <p class="shrink-0 tabular-nums">${{ line.amount.toLocaleString() }}</p>
                            </li>
                        </ul>
                        <p class="mt-3 text-[11.5px] leading-relaxed text-[var(--muted)]">
                            Rough NZD for {{ location.cost_estimate.party_size }}
                            {{ location.cost_estimate.party_size === 1 ? 'person' : 'people' }}
                            · ~${{ location.cost_estimate.per_person.toLocaleString() }}/person.
                            Side trips also free up Auckland Airbnb nights.
                        </p>
                    </div>

                    <div v-if="location.sub_locations?.length" class="mt-6">
                        <h3 class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">
                            Worth stopping at
                        </h3>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <a
                                v-for="sub in location.sub_locations"
                                :key="sub.id"
                                :href="sub.maps_url"
                                target="_blank"
                                rel="noopener"
                                class="group flex gap-3 overflow-hidden rounded-2xl border border-[var(--line)] bg-[#fafafa] p-2 pr-3 transition hover:border-[var(--ink)] hover:bg-white"
                            >
                                <div
                                    class="h-16 w-20 shrink-0 rounded-xl bg-cover bg-center bg-[#e8e8e8]"
                                    :style="sub.image_url ? { backgroundImage: `url('${sub.image_url}')` } : {}"
                                />
                                <div class="min-w-0 self-center">
                                    <p class="truncate font-semibold tracking-tight">{{ sub.name }}</p>
                                    <p class="mt-0.5 text-[12px] text-[var(--muted)] group-hover:text-[var(--ink)]">
                                        Open in Maps ›
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <ul v-else-if="location.activities?.length" class="mt-4 flex flex-wrap gap-2">
                        <li
                            v-for="activity in location.activities"
                            :key="activity"
                            class="rounded-full border border-[var(--line)] px-3 py-1.5 text-[12px]"
                        >
                            {{ activity }}
                        </li>
                    </ul>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <a
                            :href="location.airbnb_url"
                            target="_blank"
                            rel="noopener"
                            class="rounded-2xl border border-[var(--line)] p-4 transition hover:border-[var(--ink)]"
                        >
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">Stays</p>
                            <p class="mt-1 font-semibold">Airbnb nearby</p>
                            <p class="mt-1 text-[13px] text-[var(--muted)]">Homes around {{ location.name.split(',')[0] }}</p>
                        </a>

                        <a
                            v-if="location.flights_url"
                            :href="location.flights_url"
                            target="_blank"
                            rel="noopener"
                            class="rounded-2xl border border-[var(--line)] p-4 transition hover:border-[var(--ink)]"
                        >
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">Flights</p>
                            <p class="mt-1 font-semibold">{{ page.props.auth?.user?.home_airport || 'AKL' }} → {{ location.airport_code }}</p>
                            <p class="mt-1 text-[13px] text-[var(--muted)]">Google Flights from home</p>
                        </a>
                        <a
                            v-else-if="location.maps_url"
                            :href="location.maps_url"
                            target="_blank"
                            rel="noopener"
                            class="rounded-2xl border border-[var(--line)] p-4 transition hover:border-[var(--ink)]"
                        >
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">Getting there</p>
                            <p class="mt-1 font-semibold">Open in Google Maps</p>
                            <p class="mt-1 text-[13px] text-[var(--muted)]">Directions & photos</p>
                        </a>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="rounded-full bg-[var(--ink)] px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-white"
                            @click="toggleTrip"
                        >
                            {{ inTrip ? 'Remove from trip' : 'Add to my trip' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-[var(--line)] px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide"
                            @click="emit('close')"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
