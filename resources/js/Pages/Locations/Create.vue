<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ExploreLayout from '@/Layouts/ExploreLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: Object,
    modes: Object,
});

const form = useForm({
    name: '',
    category: 'local',
    mode: 'car',
    travel_time: '',
    lat: '',
    lng: '',
    description: '',
    best_time: '',
    activities: '',
    image_url: '',
    image_url_2: '',
    airport_code: '',
    airbnb_query: '',
});

function submit() {
    form.post(route('locations.store'));
}
</script>

<template>
    <Head title="Add location" />

    <ExploreLayout>
        <div class="mx-auto max-w-2xl px-5 py-10 sm:px-8">
            <Link :href="route('explore')" class="text-sm text-[var(--muted)] hover:text-[var(--ink)]">
                ← Back to explore
            </Link>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight">Add a location</h1>
            <p class="mt-2 text-[15px] font-light text-[#444]">
                Drop in a new spot with coords, travel time, and a photo URL. It shows up on the map and in filters right away.
            </p>

            <form class="mt-8 space-y-5" @submit.prevent="submit">
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel for="category" value="Category" />
                        <select
                            id="category"
                            v-model="form.category"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.category" />
                    </div>
                    <div>
                        <InputLabel for="mode" value="Travel mode" />
                        <select
                            id="mode"
                            v-model="form.mode"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option v-for="(label, key) in modes" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.mode" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <InputLabel for="travel_time" value="Travel time" />
                        <TextInput id="travel_time" v-model="form.travel_time" class="mt-1 block w-full" placeholder="45 mins" required />
                        <InputError class="mt-2" :message="form.errors.travel_time" />
                    </div>
                    <div>
                        <InputLabel for="lat" value="Latitude" />
                        <TextInput id="lat" v-model="form.lat" type="number" step="any" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="form.errors.lat" />
                    </div>
                    <div>
                        <InputLabel for="lng" value="Longitude" />
                        <TextInput id="lng" v-model="form.lng" type="number" step="any" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="form.errors.lng" />
                    </div>
                </div>

                <div>
                    <InputLabel for="description" value="Description" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div>
                    <InputLabel for="best_time" value="Best time to visit" />
                    <TextInput id="best_time" v-model="form.best_time" class="mt-1 block w-full" placeholder="Dec–Mar for the beach" />
                    <InputError class="mt-2" :message="form.errors.best_time" />
                </div>

                <div>
                    <InputLabel for="activities" value="Activities (comma or newline separated)" />
                    <textarea
                        id="activities"
                        v-model="form.activities"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Coastal walk, Surf lessons"
                    />
                    <InputError class="mt-2" :message="form.errors.activities" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel for="image_url" value="Photo URL" />
                        <TextInput id="image_url" v-model="form.image_url" class="mt-1 block w-full" />
                        <InputError class="mt-2" :message="form.errors.image_url" />
                    </div>
                    <div>
                        <InputLabel for="image_url_2" value="Detail photo URL" />
                        <TextInput id="image_url_2" v-model="form.image_url_2" class="mt-1 block w-full" />
                        <InputError class="mt-2" :message="form.errors.image_url_2" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel for="airport_code" value="Airport code (flying only)" />
                        <TextInput id="airport_code" v-model="form.airport_code" class="mt-1 block w-full" placeholder="ZQN" />
                        <InputError class="mt-2" :message="form.errors.airport_code" />
                    </div>
                    <div>
                        <InputLabel for="airbnb_query" value="Airbnb search query" />
                        <TextInput id="airbnb_query" v-model="form.airbnb_query" class="mt-1 block w-full" placeholder="Piha" />
                        <InputError class="mt-2" :message="form.errors.airbnb_query" />
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <PrimaryButton :disabled="form.processing">Save location</PrimaryButton>
                    <Link :href="route('explore')" class="text-sm text-[var(--muted)]">Cancel</Link>
                </div>
            </form>
        </div>
    </ExploreLayout>
</template>
