<template>
  <PublicLayout>
    <Head title="Pricing" />
    <section class="section-wrap py-20">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-sm font-black uppercase text-violet-600">Pricing</p>
        <h1 class="mt-4 text-4xl font-black sm:text-6xl">Plans that scale with your WhatsApp growth.</h1>
      </div>
      <div class="mt-12 grid gap-5 lg:grid-cols-3">
        <PricingCard v-for="plan in plans" :key="plan.name" v-bind="plan" />
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
</script>
