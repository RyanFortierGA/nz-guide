<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <div class="min-h-screen bg-white text-[var(--ink)]">
        <header class="flex items-center justify-between gap-4 border-b border-[var(--line)] px-5 py-3 sm:px-8">
            <Link :href="route('explore')" class="flex items-baseline gap-2 no-underline">
                <span class="text-[11px] font-medium uppercase tracking-[0.2em] text-[var(--muted)]">Explore</span>
                <span class="text-lg font-semibold tracking-tight">Aotearoa</span>
            </Link>

            <nav class="flex items-center gap-2 sm:gap-3">
                <Link
                    v-if="user"
                    :href="route('trip.show')"
                    class="rounded-full border border-[var(--ink)] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide hover:bg-black hover:text-white"
                >
                    My trip
                </Link>
                <Link
                    v-if="user"
                    :href="route('locations.create')"
                    class="hidden rounded-full border border-[var(--line)] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide hover:border-[var(--ink)] sm:inline-flex"
                >
                    Add location
                </Link>
                <template v-if="user">
                    <Link
                        :href="route('profile.edit')"
                        class="hidden text-sm text-[var(--muted)] hover:text-[var(--ink)] sm:inline"
                    >
                        {{ user.name }}
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="rounded-full bg-[var(--ink)] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white"
                    >
                        Log out
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="text-sm font-medium text-[var(--muted)] hover:text-[var(--ink)]"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="route('register')"
                        class="rounded-full bg-[var(--ink)] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white"
                    >
                        Sign up
                    </Link>
                </template>
            </nav>
        </header>

        <slot />
    </div>
</template>
