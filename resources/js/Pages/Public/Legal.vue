<template>
  <PublicLayout>
    <Head :title="kind" />
    <section class="section-wrap py-20">
      <article class="premium-card mx-auto max-w-4xl">
        <p class="text-sm font-black uppercase text-violet-600">Legal</p>
        <h1 class="mt-4 text-4xl font-black">{{ kind }}</h1>
        <p class="mt-6 leading-8 text-slate-600 dark:text-slate-300">This page provides a production-ready placeholder for {{ kind.toLowerCase() }} content. Replace this copy with your jurisdiction-specific policy before launch.</p>
        <div class="mt-8 space-y-5">
          <p v-for="section in sections" :key="section" class="leading-8 text-slate-600 dark:text-slate-300">{{ section }}</p>
        </div>
      </article>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps<{ kind: string }>();
const sections = [
  'ChatFlow AI is designed for responsible automation, team collaboration and transparent data handling.',
  'Customers are responsible for consent, messaging compliance and accuracy of business information used to train AI replies.',
  'Security controls include role-based access, activity logs, API key management and workspace-level settings.',
];
</script>
