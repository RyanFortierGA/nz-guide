<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    trip: { type: Object, required: true },
    costs: { type: Object, required: true },
});

const form = useForm({
    party_size: props.trip.party_size || 2,
    include_auckland_stay: props.trip.include_auckland_stay !== false,
    auckland_airbnb_night: props.trip.auckland_airbnb_night || 180,
});

watch(
    () => [props.trip.party_size, props.trip.include_auckland_stay, props.trip.auckland_airbnb_night],
    () => {
        form.party_size = props.trip.party_size || 2;
        form.include_auckland_stay = props.trip.include_auckland_stay !== false;
        form.auckland_airbnb_night = props.trip.auckland_airbnb_night || 180;
    },
);

const grouped = computed(() => {
    const order = ['stay', 'flight', 'transport', 'daily', 'meal', 'hangout', 'find', 'note'];
    const map = {};
    for (const line of props.costs.lines || []) {
        (map[line.category] ||= []).push(line);
    }
    return order.filter((k) => map[k]?.length).map((k) => ({
        key: k,
        label: {
            stay: 'Stays',
            flight: 'Flights',
            transport: 'Getting around',
            daily: 'Day-to-day',
            meal: 'Meals',
            hangout: 'Hang outs',
            find: 'Locations',
            note: 'Notes',
        }[k] || k,
        lines: map[k],
        subtotal: map[k].reduce((s, l) => s + l.amount, 0),
    }));
});

function money(n) {
    return new Intl.NumberFormat('en-NZ', {
        style: 'currency',
        currency: 'NZD',
        maximumFractionDigits: 0,
    }).format(n || 0);
}

function saveCosts() {
    form.patch(route('trip.costs'), { preserveScroll: true });
}

function setNights(locationId, event) {
    const nights = event.target.value === '' ? null : Number(event.target.value);
    router.patch(
        route('trip.assign', locationId),
        { nights },
        { preserveScroll: true },
    );
}
</script>

<template>
    <aside class="rounded-3xl border border-[var(--line)] bg-white p-5 shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--muted)]">Price calculator</p>
        <h2 class="mt-1 text-xl font-semibold tracking-tight">Cost per person</h2>
        <p class="mt-2 text-[13px] leading-relaxed text-[var(--muted)]">
            Ballpark for each of {{ costs.party_size }} over
            {{ costs.trip_nights }} night{{ costs.trip_nights === 1 ? '' : 's' }}.
            Shared stays and fuel are split. Side trips pull nights out of the Auckland base.
        </p>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-2xl bg-[#f7f5f2] p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-[var(--muted)]">Per person</p>
                <p class="mt-1 text-2xl font-semibold tracking-tight">{{ money(costs.per_person) }}</p>
            </div>
            <div class="rounded-2xl bg-[#f7f5f2] p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                    Party of {{ costs.party_size }}
                </p>
                <p class="mt-1 text-2xl font-semibold tracking-tight">{{ money(costs.party_total || costs.per_person * costs.party_size) }}</p>
            </div>
        </div>

        <form class="mt-4 space-y-3 border-t border-[var(--line)] pt-4" @submit.prevent="saveCosts">
            <label class="block text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                Party size
                <input
                    v-model.number="form.party_size"
                    type="number"
                    min="1"
                    max="12"
                    class="mt-1 block w-full rounded-lg border-gray-200 text-sm"
                    @change="saveCosts"
                />
            </label>
            <label class="flex items-center gap-2 text-[13px]">
                <input v-model="form.include_auckland_stay" type="checkbox" class="rounded border-gray-300" @change="saveCosts" />
                Include Auckland Airbnb nights
            </label>
            <label v-if="form.include_auckland_stay" class="block text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">
                Auckland nightly (NZD, place total)
                <input
                    v-model.number="form.auckland_airbnb_night"
                    type="number"
                    min="50"
                    max="800"
                    step="10"
                    class="mt-1 block w-full rounded-lg border-gray-200 text-sm"
                    @change="saveCosts"
                />
            </label>
        </form>

        <div v-if="trip.locations?.length" class="mt-4 space-y-2 border-t border-[var(--line)] pt-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">Nights away</p>
            <div
                v-for="place in trip.locations.filter((l) => l.category !== 'local')"
                :key="place.id"
                class="flex items-center justify-between gap-2 text-[13px]"
            >
                <span class="truncate font-medium">{{ place.name.split(',')[0] }}</span>
                <label class="flex items-center gap-1 text-[11px] text-[var(--muted)]">
                    <input
                        type="number"
                        min="0"
                        max="14"
                        class="w-14 rounded-lg border-gray-200 text-xs"
                        :value="place.nights ?? place.cost_preview?.nights ?? place.suggested_nights ?? (place.category === 'flying' ? 3 : 1)"
                        @change="setNights(place.id, $event)"
                    />
                    nights
                </label>
            </div>
        </div>

        <div class="mt-5 space-y-4">
            <div v-for="group in grouped" :key="group.key">
                <div class="mb-1.5 flex items-baseline justify-between">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)]">{{ group.label }}</p>
                    <p class="text-[12px] font-semibold">{{ money(group.subtotal) }}/pp</p>
                </div>
                <ul class="space-y-2">
                    <li v-for="line in group.lines" :key="line.key" class="flex items-start justify-between gap-3 text-[13px]">
                        <div class="min-w-0">
                            <p class="font-medium leading-snug">{{ line.label }}</p>
                            <p v-if="line.detail" class="text-[11.5px] text-[var(--muted)]">{{ line.detail }}</p>
                        </div>
                        <p class="shrink-0 font-semibold tabular-nums">{{ money(line.amount) }}</p>
                    </li>
                </ul>
            </div>
            <p v-if="!costs.lines?.length" class="text-[13px] text-[var(--muted)]">
                Add places or meals to see the estimate fill in.
            </p>
        </div>

        <p class="mt-5 border-t border-[var(--line)] pt-3 text-[11.5px] leading-relaxed text-[var(--muted)]">
            {{ costs.disclaimer }}
        </p>
    </aside>
</template>
