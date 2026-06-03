<template>
  <PublicLayout>
    <Head title="Features" />
    <section class="section-wrap py-20">
      <div class="max-w-3xl">
        <p class="text-sm font-black uppercase text-violet-600">Platform</p>
        <h1 class="mt-4 text-4xl font-black sm:text-6xl">A complete WhatsApp operating system for revenue teams.</h1>
      </div>
      <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        <FeatureCard v-for="feature in features" :key="feature.title" v-bind="feature" />
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
</script>
