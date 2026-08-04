<script setup>
import DestinationCard from '@/Components/DestinationCard.vue';
import ExploreMap from '@/Components/ExploreMap.vue';
import LocationDetailModal from '@/Components/LocationDetailModal.vue';
import SeoHead from '@/Components/SeoHead.vue';
import TripSidebar from '@/Components/TripSidebar.vue';
import ExploreLayout from '@/Layouts/ExploreLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    locations: Array,
    trip: Object,
    tripLocationIds: Array,
    home: Object,
    categories: Object,
    categoryColors: Object,
    seo: Object,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const activeFilter = ref('local');
const focusId = ref(null);
const detail = ref(null);

const filteredLocations = computed(() => {
    if (activeFilter.value === 'all') return props.locations;
    return props.locations.filter((l) => l.category === activeFilter.value);
});

const tripIds = computed(() => new Set(props.tripLocationIds || []));

function categoryCount(key) {
    if (key === 'all') return props.locations.length;
    return props.locations.filter((l) => l.category === key).length;
}

function openDetail(location) {
    detail.value = location;
}

function focusLocation(location) {
    focusId.value = location.id;
}
</script>

<template>
    <SeoHead
        :title="seo?.title || 'Where to next'"
        :description="seo?.description"
        :image="seo?.image"
        :url="seo?.url"
    />

    <ExploreLayout>
        <div class="border-b border-[var(--line)] px-5 py-6 sm:px-8 sm:py-8">
            <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-[var(--muted)]">
                For friends & family visiting — home base: {{ home.name }}
            </p>
            <h1 class="mt-3 text-[clamp(28px,3.2vw,42px)] font-light leading-tight tracking-tight">
                Where to next, <b class="font-semibold">Aotearoa?</b>
            </h1>
            <p class="mt-3 max-w-xl text-[14.5px] font-light leading-relaxed text-[#444]">
                A personal shortlist from our place in Auckland — walks and ferries nearby, weekend drives,
                and a few flights further afield if you fancy stretching the trip.
            </p>

            <div class="mt-5 flex flex-wrap gap-2">
                <button
                    v-for="(label, key) in categories"
                    :key="key"
                    type="button"
                    class="pill-filter"
                    :class="{ active: activeFilter === key }"
                    @click="activeFilter = key"
                >
                    {{ label }}
                    <span class="count">{{ categoryCount(key) }}</span>
                </button>
            </div>
        </div>

        <div class="grid min-h-[calc(100vh-220px)] lg:grid-cols-[280px_minmax(340px,480px)_1fr]">
            <TripSidebar
                class="hidden max-h-[calc(100vh-220px)] lg:flex"
                :trip="trip"
                :category-colors="categoryColors"
                :authenticated="!!user"
                @learn-more="openDetail"
                @focus="focusLocation"
            />

            <section class="max-h-[calc(100vh-220px)] overflow-y-auto border-r border-[var(--line)] px-5 pb-16 sm:px-7">
                <p class="mono sticky top-0 z-[2] bg-white py-4 text-[11px] text-[var(--muted)]">
                    {{ filteredLocations.length }}
                    {{ activeFilter === 'all' ? `of ${locations.length}` : '' }}
                    places
                </p>
                <div class="flex flex-col gap-4">
                    <DestinationCard
                        v-for="location in filteredLocations"
                        :key="location.id"
                        :location="location"
                        :category-colors="categoryColors"
                        :in-trip="tripIds.has(location.id)"
                        :active="focusId === location.id"
                        @learn-more="openDetail"
                        @focus="focusLocation"
                    />
                    <p
                        v-if="!filteredLocations.length"
                        class="rounded-2xl border border-dashed border-[var(--line)] p-6 text-sm text-[var(--muted)]"
                    >
                        Nothing in this filter yet — try another distance, or add a location.
                    </p>
                </div>
            </section>

            <section class="relative hidden min-h-[480px] lg:block">
                <ExploreMap
                    :locations="filteredLocations"
                    :all-locations="locations"
                    :home="home"
                    :category-colors="categoryColors"
                    :focus-id="focusId"
                    @select="focusLocation"
                />
            </section>
        </div>

        <div class="border-t border-[var(--line)] lg:hidden">
            <div class="h-[55vh]">
                <ExploreMap
                    :locations="filteredLocations"
                    :all-locations="locations"
                    :home="home"
                    :category-colors="categoryColors"
                    :focus-id="focusId"
                    @select="focusLocation"
                />
            </div>
        </div>

        <LocationDetailModal
            v-if="detail"
            :show="!!detail"
            :location="detail"
            :category-colors="categoryColors"
            :in-trip="tripIds.has(detail.id)"
            @close="detail = null"
        />
    </ExploreLayout>
</template>
