<script setup>
import DestinationCard from '@/Components/DestinationCard.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    trip: { type: Object, default: null },
    categoryColors: { type: Object, required: true },
    authenticated: { type: Boolean, default: false },
});

const emit = defineEmits(['learn-more', 'focus']);
</script>

<template>
    <aside class="flex h-full flex-col border-r border-[var(--line)] bg-[var(--sidebar)]">
        <div class="border-b border-[var(--line)]/60 px-4 py-4">
            <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--muted)]">Personal trip plan</p>
            <h2 class="mt-1 text-base font-semibold tracking-tight">{{ trip?.name || 'My Trip' }}</h2>
            <p v-if="trip?.arrives_label && trip?.departs_label" class="mt-1 text-[12px] text-[var(--muted)]">
                {{ trip.arrives_label }} → {{ trip.departs_label }}
            </p>

            <div v-if="authenticated" class="mt-3 flex flex-col gap-2">
                <Link
                    :href="trip?.setup_complete ? route('trip.show') : route('trip.setup')"
                    class="inline-flex items-center justify-center rounded-full bg-[var(--ink)] px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-white"
                >
                    {{ trip?.setup_complete ? 'Plan by day' : 'Set arrival dates' }}
                </Link>
                <Link
                    v-if="trip?.share_url"
                    :href="trip.share_url"
                    target="_blank"
                    class="text-center text-[11px] font-medium text-[var(--muted)] underline-offset-2 hover:text-[var(--ink)] hover:underline"
                >
                    Open share link
                </Link>
            </div>
        </div>

        <div class="flex-1 space-y-3 overflow-y-auto p-3">
            <template v-if="!authenticated">
                <div class="rounded-2xl border border-dashed border-[var(--line)] bg-white/70 p-4 text-sm text-[var(--muted)]">
                    <p class="font-medium text-[var(--ink)]">Planning a visit?</p>
                    <p class="mt-1 text-[13px] leading-relaxed">
                        Log in to save places and sketch a day-by-day plan for whoever’s coming to stay.
                    </p>
                    <Link
                        :href="route('login')"
                        class="mt-3 inline-flex rounded-full bg-[var(--ink)] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white"
                    >
                        Log in
                    </Link>
                </div>
            </template>

            <template v-else-if="!trip?.locations?.length">
                <div class="rounded-2xl border border-dashed border-[var(--line)] bg-white/70 p-4 text-sm text-[var(--muted)]">
                    <p class="font-medium text-[var(--ink)]">Nothing planned yet</p>
                    <p class="mt-1 text-[13px] leading-relaxed">
                        Tap <strong>Add to my trip</strong> on any destination, then open day planning when you’re ready.
                    </p>
                </div>
            </template>

            <DestinationCard
                v-for="location in trip?.locations || []"
                :key="location.id"
                :location="location"
                :category-colors="categoryColors"
                :in-trip="true"
                compact
                @learn-more="emit('learn-more', $event)"
                @focus="emit('focus', $event)"
            />
        </div>
    </aside>
</template>
