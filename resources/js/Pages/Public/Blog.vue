<template>
  <PublicLayout>
    <Head title="Blog" />
    <section class="section-wrap py-20">
      <h1 class="text-4xl font-black sm:text-6xl">Growth playbooks for WhatsApp-first teams.</h1>
      <div class="mt-10 grid gap-5 md:grid-cols-3">
        <article v-for="post in posts" :key="post" class="premium-card">
          <p class="text-sm font-black text-violet-600">Guide</p>
          <h2 class="mt-3 text-2xl font-black">{{ post }}</h2>
          <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">Practical tactics for support, sales automation and AI-assisted customer journeys.</p>
        </article>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const posts = ['How to qualify WhatsApp leads with AI', 'Broadcast campaigns without spammy copy', 'Building a shared inbox your team loves'];
</script>
