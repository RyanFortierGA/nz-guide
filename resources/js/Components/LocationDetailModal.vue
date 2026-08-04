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
const color = computed(() => props.categoryColors[props.location.category] || '#15181c');

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
            class="fixed inset-0 z-[2000] flex items-end justify-center bg-black/40 p-4 sm:items-center"
            @click.self="emit('close')"
        >
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white shadow-2xl">
                <div
                    class="relative h-56 bg-cover bg-center sm:h-64"
                    :style="{ backgroundImage: `url('${location.image_url}')` }"
                >
                    <button
                        type="button"
                        class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-xl leading-none"
                        @click="emit('close')"
                    >
                        ×
                    </button>
                    <span
                        class="absolute bottom-4 left-4 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11.5px] font-bold text-white mono"
                        :style="{ background: color }"
                        v-html="ICONS[location.mode] + location.travel_time"
                    />
                </div>

                <div class="p-6 sm:p-8">
                    <h2 class="text-2xl font-semibold tracking-tight">{{ location.name }}</h2>
                    <p class="mt-3 text-[15px] font-light leading-relaxed text-[#444]">
                        {{ location.description }}
                    </p>

                    <p v-if="location.best_time" class="mono mt-4 text-[11px] uppercase tracking-wide text-[var(--muted)]">
                        Best time · {{ location.best_time }}
                    </p>

                    <ul v-if="location.activities?.length" class="mt-3 flex flex-wrap gap-2">
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
                            <p class="mt-1 text-[13px] text-[var(--muted)]">Browse homes around {{ location.name.split(',')[0] }}</p>
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
                            <p class="mt-1 text-[13px] text-[var(--muted)]">Open Google Flights from your home base</p>
                        </a>
                        <div
                            v-else
                            class="rounded-2xl border border-dashed border-[var(--line)] p-4 text-[13px] text-[var(--muted)]"
                        >
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em]">Flights</p>
                            <p class="mt-1 font-medium text-[var(--ink)]">Drive / ferry / walk from Auckland</p>
                            <p class="mt-1">No flight search needed for this spot.</p>
                        </div>
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
