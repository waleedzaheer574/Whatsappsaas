<template>
  <PublicLayout>
    <Head title="Pricing" />
    <section class="section-wrap py-10 sm:py-16 lg:py-20">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-xs font-black uppercase text-violet-600 sm:text-sm">Pricing</p>
        <h1 class="mt-3 text-[2rem] font-black leading-[1.08] sm:mt-4 sm:text-6xl">Plans that scale with your WhatsApp growth.</h1>
        <p class="mx-auto mt-4 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base sm:leading-7">Start small, then unlock more accounts, automations and team capacity as your customer volume grows.</p>
      </div>
      <div class="mt-7 grid gap-4 sm:mt-12 lg:grid-cols-3 lg:gap-5">
        <PricingCard v-for="plan in plans" :key="plan.name" v-bind="plan" />
      </div>
      <div class="mt-8 grid gap-3 sm:mt-12 md:grid-cols-3 md:gap-5">
        <article v-for="item in assurances" :key="item.title" class="premium-card">
          <p class="text-base font-black">{{ item.title }}</p>
          <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ item.text }}</p>
        </article>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import PricingCard from '@/Components/Marketing/PricingCard.vue';

const plans = [
  { name: 'Starter', price: '19', caption: 'For small businesses', features: ['1 WhatsApp account', '1,000 messages / month', 'AI auto replies', 'Basic analytics', 'Email support'] },
  { name: 'Pro', price: '49', caption: 'For growing businesses', features: ['3 WhatsApp accounts', '10,000 messages / month', 'Advanced AI', 'Team inbox', 'Broadcast messages', 'Priority support'], featured: true },
  { name: 'Agency', price: '99', caption: 'For agencies and teams', features: ['10 WhatsApp accounts', 'Unlimited messages', 'Client workspaces', 'Advanced analytics', '24/7 priority support'] },
];
const assurances = [
  { title: 'Demo checkout ready', text: 'Use test cards locally to validate the full subscription purchase flow.' },
  { title: 'Workspace locked by plan', text: 'Users without an active subscription stay on billing until a plan is active.' },
  { title: 'Upgrade anytime', text: 'Plan limits are stored per workspace for accounts, messages and team seats.' },
];
</script>
