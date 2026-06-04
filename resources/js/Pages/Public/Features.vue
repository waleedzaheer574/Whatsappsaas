<template>
  <PublicLayout>
    <Head title="Features" />
    <section class="section-wrap py-10 sm:py-16 lg:py-20">
      <div class="max-w-3xl">
        <p class="text-xs font-black uppercase text-violet-600 sm:text-sm">Platform</p>
        <h1 class="mt-3 text-[2rem] font-black leading-[1.08] sm:mt-4 sm:text-6xl">A complete WhatsApp operating system for revenue teams.</h1>
        <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base sm:leading-7">Bring conversations, automation, analytics and team controls into one focused workspace.</p>
      </div>
      <div class="mt-7 grid gap-3 sm:mt-12 md:grid-cols-2 md:gap-5 lg:grid-cols-3">
        <FeatureCard v-for="feature in features" :key="feature.title" v-bind="feature" />
      </div>
      <div class="mt-8 grid gap-3 sm:mt-12 md:grid-cols-3 md:gap-5">
        <article v-for="metric in metrics" :key="metric.label" class="premium-card">
          <p class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">{{ metric.label }}</p>
          <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white sm:text-4xl">{{ metric.value }}</p>
          <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ metric.text }}</p>
        </article>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Bot, ChartSpline, KeyRound, MessageSquare, Send, ShieldCheck } from 'lucide-vue-next';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import FeatureCard from '@/Components/Marketing/FeatureCard.vue';

const tones = ['bg-violet-100 text-violet-600', 'bg-sky-100 text-sky-600', 'bg-emerald-100 text-emerald-600'];
const features = [
  { title: 'Smart AI Replies', text: 'Context-aware answers trained on your products, policies and tone.', icon: Bot, tone: tones[0] },
  { title: 'Shared Inbox', text: 'Assign, tag, snooze and collaborate on WhatsApp conversations.', icon: MessageSquare, tone: tones[1] },
  { title: 'Broadcast Studio', text: 'Create segmented campaigns with previews and delivery insights.', icon: Send, tone: tones[2] },
  { title: 'Realtime Analytics', text: 'Track response speed, lead quality, conversions and campaign ROI.', icon: ChartSpline, tone: tones[0] },
  { title: 'API Keys', text: 'Connect ChatFlow AI to your internal workflows and SaaS stack.', icon: KeyRound, tone: tones[1] },
  { title: 'Governance', text: 'Roles, activity logs and security controls for growing teams.', icon: ShieldCheck, tone: tones[2] },
];
const metrics = [
  { label: 'Automation', value: '84%', text: 'Average replies handled automatically after training.' },
  { label: 'Speed', value: '8s', text: 'Typical response time for connected inbox workflows.' },
  { label: 'Coverage', value: '24/7', text: 'Always-on capture for sales, support and follow-ups.' },
];
</script>
