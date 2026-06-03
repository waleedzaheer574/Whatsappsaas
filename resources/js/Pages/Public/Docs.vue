<template>
  <PublicLayout>
    <Head title="Documentation" />
    <section class="section-wrap py-20">
      <h1 class="text-4xl font-black sm:text-6xl">Documentation</h1>
      <div class="mt-10 grid gap-5 md:grid-cols-3">
        <article v-for="item in docs" :key="item.title" class="premium-card">
          <h2 class="text-xl font-black">{{ item.title }}</h2>
          <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ item.text }}</p>
        </article>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const docs = [
  { title: 'Quickstart', text: 'Connect WhatsApp Business, create your workspace and invite your team.' },
  { title: 'AI Training', text: 'Upload documents, FAQs and product data to improve response quality.' },
  { title: 'API & Reverb', text: 'Use REST APIs and realtime events for integrations and custom dashboards.' },
];
</script>
