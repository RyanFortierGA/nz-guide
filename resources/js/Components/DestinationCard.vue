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
const color = computed(() => props.categoryColors[props.location.category] || '#15181c');

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
</script>

<template>
    <article
        class="ticket"
        :class="{ dimmed, 'active-pin': active, '!p-2': compact }"
        @click="emit('focus', location)"
    >
        <div
            class="ticket-photo"
            :class="compact ? '!h-[110px] !rounded-xl' : ''"
            :style="{ backgroundImage: `url('${location.image_url}')` }"
        >
            <button
                type="button"
                class="photo-btn"
                @click.stop="emit('learn-more', location)"
            >
                Learn more <span>›</span>
            </button>
            <span class="time-badge" :style="{ background: color }" v-html="ICONS[location.mode] + location.travel_time" />
        </div>

        <div class="relative px-2 pb-1 pt-3">
            <button
                v-if="user || inTrip"
                type="button"
                class="absolute right-1 top-2 flex h-7 w-7 items-center justify-center rounded-full text-[var(--muted)] hover:bg-gray-100 hover:text-[var(--ink)]"
                :title="inTrip ? 'Remove from trip' : 'Add to trip'"
                @click="toggleTrip"
            >
                <span v-if="inTrip" class="text-lg leading-none">×</span>
                <span v-else class="text-lg leading-none">+</span>
            </button>

            <h3 class="pr-8 text-[17px] font-semibold tracking-tight" :class="compact ? '!text-[15px]' : ''">
                {{ location.name }}
            </h3>
            <p class="mt-1 text-[13.5px] font-light leading-relaxed text-[#444]" :class="compact ? 'line-clamp-2 !text-[12.5px]' : 'line-clamp-3'">
                {{ location.description }}
            </p>

            <div v-if="!compact && !user" class="mt-3">
                <button
                    type="button"
                    class="text-[11px] font-semibold uppercase tracking-wide text-[var(--muted)] hover:text-[var(--ink)]"
                    @click="toggleTrip"
                >
                    Log in to add to trip
                </button>
            </div>
            <div v-else-if="!compact && user && !inTrip" class="mt-3">
                <button
                    type="button"
                    class="rounded-full border border-[var(--ink)] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide hover:bg-black hover:text-white"
                    @click="toggleTrip"
                >
                    Add to my trip
                </button>
            </div>
        </div>
    </article>
</template>
