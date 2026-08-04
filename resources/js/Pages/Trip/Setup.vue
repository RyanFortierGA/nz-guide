<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SeoHead from '@/Components/SeoHead.vue';
import TextInput from '@/Components/TextInput.vue';
import ExploreLayout from '@/Layouts/ExploreLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    trip: Object,
});

const form = useForm({
    visitor_name: props.trip.visitor_name || '',
    arrives_at: props.trip.arrives_at || '',
    departs_at: props.trip.departs_at || '',
    share_blurb: props.trip.share_blurb || '',
    name: props.trip.name || '',
});

function submit() {
    form.post(route('trip.setup.save'));
}
</script>

<template>
    <SeoHead
        title="When are you arriving?"
        description="Tell us when guests land and leave so we can sketch a gentle day-by-day visit plan."
    />

    <ExploreLayout>
        <div class="mx-auto max-w-xl px-5 py-10 sm:px-8">
            <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-[var(--muted)]">Trip setup</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight">When do they get in?</h1>
            <p class="mt-3 text-[15px] font-light leading-relaxed text-[#444]">
                A few dates so we can turn “my trip” into a calm day-by-day sketch — arrival, leave day,
                and whatever you want to squeeze in between. Not a booking tool; just for you and your visitors.
            </p>

            <form class="mt-8 space-y-5" @submit.prevent="submit">
                <div>
                    <InputLabel for="visitor_name" value="Who’s visiting? (optional)" />
                    <TextInput
                        id="visitor_name"
                        v-model="form.visitor_name"
                        class="mt-1 block w-full"
                        placeholder="Mum & Dad, the cousins…"
                    />
                    <InputError class="mt-2" :message="form.errors.visitor_name" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel for="arrives_at" value="Arrival" />
                        <TextInput
                            id="arrives_at"
                            v-model="form.arrives_at"
                            type="datetime-local"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.arrives_at" />
                    </div>
                    <div>
                        <InputLabel for="departs_at" value="Leaving" />
                        <TextInput
                            id="departs_at"
                            v-model="form.departs_at"
                            type="datetime-local"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.departs_at" />
                    </div>
                </div>

                <div>
                    <InputLabel for="share_blurb" value="Note for the share link (optional)" />
                    <textarea
                        id="share_blurb"
                        v-model="form.share_blurb"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-800 focus:ring-gray-800"
                        placeholder="Can’t wait to show you Auckland — here’s a rough sketch of the week."
                    />
                    <InputError class="mt-2" :message="form.errors.share_blurb" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing">Save & plan days</PrimaryButton>
                    <Link :href="route('explore')" class="text-sm text-[var(--muted)]">Back to explore</Link>
                </div>
            </form>
        </div>
    </ExploreLayout>
</template>
