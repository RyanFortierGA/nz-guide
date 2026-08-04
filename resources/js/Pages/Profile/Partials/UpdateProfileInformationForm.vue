<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    home_name: user.home_name || '',
    home_lat: user.home_lat ?? '',
    home_lng: user.home_lng ?? '',
    home_airport: user.home_airport || 'AKL',
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account, home base, and departure airport for flight searches.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="home_name" value="Home base label" />
                <TextInput id="home_name" class="mt-1 block w-full" v-model="form.home_name" />
                <InputError class="mt-2" :message="form.errors.home_name" />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <InputLabel for="home_lat" value="Home latitude" />
                    <TextInput id="home_lat" type="number" step="any" class="mt-1 block w-full" v-model="form.home_lat" />
                    <InputError class="mt-2" :message="form.errors.home_lat" />
                </div>
                <div>
                    <InputLabel for="home_lng" value="Home longitude" />
                    <TextInput id="home_lng" type="number" step="any" class="mt-1 block w-full" v-model="form.home_lng" />
                    <InputError class="mt-2" :message="form.errors.home_lng" />
                </div>
                <div>
                    <InputLabel for="home_airport" value="Home airport" />
                    <TextInput id="home_airport" class="mt-1 block w-full" v-model="form.home_airport" placeholder="AKL" />
                    <InputError class="mt-2" :message="form.errors.home_airport" />
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
