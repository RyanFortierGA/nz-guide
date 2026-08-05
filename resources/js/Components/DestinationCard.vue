<script setup>
import { ICONS } from '@/icons';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    location: { type: Object, required: true },
    categoryColors: { type: Object, required: true },
    dimmed: { type: Boolean, default: false },
    inTrip: { type: Boolean, default: false },
    compact: { type: Boolean, default: false },
    active: { type: Boolean, default: false },
});

const emit = defineEmits(['learn-more', 'focus']);

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

const previewSubs = computed(() => (props.location.sub_locations || []).slice(0, 4));
const modeIcon = computed(() => ICONS[props.location.mode] || '');

function toggleTrip(e) {
    e.stopPropagation();
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

function openSubMaps(event, url) {
    event.preventDefault();
    event.stopPropagation();
    if (url) {
        window.open(url, '_blank', 'noopener,noreferrer');
    }
}
</script>

<template>
    <!-- Compact sidebar card: horizontal like Figma -->
    <article
        v-if="compact"
        class="ticket ticket-compact"
        :class="{ 'active-pin': active }"
        @click="emit('focus', location)"
    >
        <div class="ticket-compact-inner">
            <div
                class="ticket-compact-photo"
                :style="{ backgroundImage: `url('${location.image_url}')` }"
            >
                <button type="button" class="photo-btn photo-btn-sm" @click.stop="emit('learn-more', location)">
                    Learn more <span>›</span>
                </button>
            </div>
            <div class="ticket-compact-body">
                <button
                    v-if="user || inTrip"
                    type="button"
                    class="ticket-x"
                    :title="inTrip ? 'Remove from trip' : 'Add to trip'"
                    @click="toggleTrip"
                >
                    {{ inTrip ? '×' : '+' }}
                </button>
                <div class="ticket-title-row">
                    <h3>{{ location.name.split(',')[0] }}</h3>
                    <span class="time-badge-inline" :style="{ background: softBadge.bg, color: softBadge.fg }">
                        <span v-if="modeIcon" v-html="modeIcon" />
                        {{ location.travel_time }}
                    </span>
                </div>
                <p class="desc line-clamp-2">{{ location.description }}</p>
            </div>
        </div>
    </article>

    <!-- Main feed card -->
    <article
        v-else
        class="ticket"
        :class="{ dimmed, 'active-pin': active }"
        @click="emit('focus', location)"
    >
        <div class="ticket-photo" :style="{ backgroundImage: `url('${location.image_url}')` }">
            <button type="button" class="photo-btn photo-btn-br" @click.stop="emit('learn-more', location)">
                Learn more <span>›</span>
            </button>
        </div>

        <div class="ticket-body">
            <button
                v-if="user || inTrip"
                type="button"
                class="ticket-x"
                :title="inTrip ? 'Remove from trip' : 'Add to trip'"
                @click="toggleTrip"
            >
                {{ inTrip ? '×' : '+' }}
            </button>

            <div class="ticket-title-row">
                <h3>{{ location.name }}</h3>
                <span class="time-badge-inline" :style="{ background: softBadge.bg, color: softBadge.fg }">
                    <span v-if="modeIcon" v-html="modeIcon" />
                    {{ location.travel_time }}
                </span>
            </div>

            <p class="desc line-clamp-3">{{ location.description }}</p>
            <p v-if="location.cost_estimate" class="mt-2 text-[12.5px] font-medium text-[var(--ink)]">
                ~${{ location.cost_estimate.per_person.toLocaleString() }}/person
                <span class="font-normal text-[var(--muted)]">
                    · {{ location.cost_estimate.nights
                        ? `${location.cost_estimate.nights} night${location.cost_estimate.nights === 1 ? '' : 's'}`
                        : 'day out' }}
                </span>
            </p>

            <div v-if="previewSubs.length" class="subspot-row" @click.stop>
                <a
                    v-for="sub in previewSubs"
                    :key="sub.id"
                    class="subspot-chip"
                    :href="sub.maps_url || '#'"
                    target="_blank"
                    rel="noopener noreferrer"
                    @click="openSubMaps($event, sub.maps_url)"
                >
                    <span
                        v-if="sub.image_url"
                        class="subspot-chip-img"
                        :style="{ backgroundImage: `url('${sub.image_url}')` }"
                    />
                    <span>{{ sub.name }}</span>
                </a>
                <span v-if="(location.sub_locations || []).length > 4" class="subspot-more">
                    +{{ location.sub_locations.length - 4 }}
                </span>
            </div>

            <div class="ticket-actions">
                <a
                    v-if="location.maps_url"
                    :href="location.maps_url"
                    target="_blank"
                    rel="noopener"
                    class="text-link"
                    @click.stop
                >
                    Open in Maps
                </a>
                <button
                    v-if="user && !inTrip"
                    type="button"
                    class="add-trip-btn"
                    @click="toggleTrip"
                >
                    Add to my trip
                </button>
                <button
                    v-else-if="!user"
                    type="button"
                    class="text-link"
                    @click="toggleTrip"
                >
                    Log in to add
                </button>
            </div>
        </div>
    </article>
</template>
