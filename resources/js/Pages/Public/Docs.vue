<template>
  <PublicLayout>
    <Head title="Documentation" />
    <section class="section-wrap py-10 sm:py-16 lg:py-20">
      <div class="max-w-3xl">
        <p class="text-xs font-black uppercase text-violet-600 sm:text-sm">Resources</p>
        <h1 class="mt-3 text-[2rem] font-black leading-[1.08] sm:mt-4 sm:text-6xl">Documentation for building your WhatsApp CRM.</h1>
        <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base sm:leading-7">Setup guides, API notes and workflow references for running ChatFlow AI locally or in production.</p>
      </div>
      <div class="mt-7 grid gap-3 sm:mt-10 md:grid-cols-3 md:gap-5">
        <article v-for="item in docs" :key="item.title" class="premium-card">
          <p class="text-xs font-black uppercase text-violet-600">{{ item.kicker }}</p>
          <h2 class="mt-3 text-lg font-black sm:text-xl">{{ item.title }}</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300 sm:mt-3">{{ item.text }}</p>
        </article>
      </div>
      <div class="mt-8 rounded-[24px] border border-slate-200 bg-white/80 p-4 shadow-glass dark:border-white/10 dark:bg-white/[.055] sm:mt-12 sm:rounded-[28px] sm:p-6">
        <h2 class="text-xl font-black">Popular setup path</h2>
        <div class="mt-5 grid gap-3 md:grid-cols-4">
          <div v-for="(step, index) in setup" :key="step" class="rounded-2xl bg-slate-50 p-4 dark:bg-white/[.06]">
            <span class="grid size-8 place-items-center rounded-full bg-violet-600 text-xs font-black text-white">{{ index + 1 }}</span>
            <p class="mt-3 text-sm font-black leading-5">{{ step }}</p>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const docs = [
  { kicker: 'Start', title: 'Quickstart', text: 'Connect WhatsApp Business, create your workspace and invite your team.' },
  { kicker: 'Knowledge', title: 'AI Training', text: 'Upload documents, FAQs and product data to improve response quality.' },
  { kicker: 'Developers', title: 'API & Reverb', text: 'Use REST APIs and realtime events for integrations and custom dashboards.' },
];
const setup = ['Create workspace and subscription', 'Connect WhatsApp Cloud API', 'Add contacts or receive API messages', 'Reply from live inbox and track statuses'];
</script>
