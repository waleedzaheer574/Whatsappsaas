<template>
  <PublicLayout>
    <Head title="Blog" />
    <section class="section-wrap py-10 sm:py-16 lg:py-20">
      <div class="max-w-3xl">
        <p class="text-xs font-black uppercase text-violet-600 sm:text-sm">Blog</p>
        <h1 class="mt-3 text-[2rem] font-black leading-[1.08] sm:mt-4 sm:text-6xl">Growth playbooks for WhatsApp-first teams.</h1>
        <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base sm:leading-7">Practical tactics for support, sales automation and AI-assisted customer journeys.</p>
      </div>
      <article class="premium-card mt-7 grid gap-5 overflow-hidden sm:mt-10 lg:grid-cols-[1fr_1.3fr]">
        <div class="rounded-[22px] bg-gradient-to-br from-violet-600 to-indigo-600 p-5 text-white sm:p-7">
          <p class="text-xs font-black uppercase text-white/70">Featured</p>
          <h2 class="mt-3 text-2xl font-black leading-tight sm:text-4xl">How to qualify WhatsApp leads with AI</h2>
          <p class="mt-4 text-sm leading-6 text-white/80">A simple operating model for capturing intent, assigning status and sending the next best reply.</p>
        </div>
        <div class="grid content-center gap-3">
          <div v-for="point in featuredPoints" :key="point" class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-700 dark:bg-white/[.06] dark:text-slate-200">{{ point }}</div>
        </div>
      </article>
      <div class="mt-5 grid gap-4 md:grid-cols-3 md:gap-5">
        <article v-for="post in posts" :key="post" class="premium-card">
          <p class="text-xs font-black uppercase text-violet-600">Guide</p>
          <h2 class="mt-3 text-xl font-black leading-tight sm:text-2xl">{{ post }}</h2>
          <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300 sm:mt-4">Practical tactics for support, sales automation and AI-assisted customer journeys.</p>
        </article>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const posts = ['How to qualify WhatsApp leads with AI', 'Broadcast campaigns without spammy copy', 'Building a shared inbox your team loves'];
const featuredPoints = ['Capture every new number as a CRM contact', 'Route high-intent chats to the right team', 'Use message status and notes for follow-up discipline'];
</script>
