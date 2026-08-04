<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    image: { type: String, default: '/og-share.jpg' },
    url: { type: String, default: '' },
});

const absoluteImage = computed(() => {
    if (!props.image) return '';
    if (/^https?:\/\//i.test(props.image)) return props.image;
    if (typeof window !== 'undefined') {
        return `${window.location.origin}${props.image.startsWith('/') ? '' : '/'}${props.image}`;
    }
    return props.image;
});
</script>

<template>
    <Head :title="title">
        <meta v-if="description" head-key="description" name="description" :content="description" />
        <meta head-key="og:title" property="og:title" :content="title" />
        <meta v-if="description" head-key="og:description" property="og:description" :content="description" />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta v-if="url" head-key="og:url" property="og:url" :content="url" />
        <meta head-key="og:image" property="og:image" :content="absoluteImage" />
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:title" name="twitter:title" :content="title" />
        <meta v-if="description" head-key="twitter:description" name="twitter:description" :content="description" />
        <meta head-key="twitter:image" name="twitter:image" :content="absoluteImage" />
    </Head>
</template>
