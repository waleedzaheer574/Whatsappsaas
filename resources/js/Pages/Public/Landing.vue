<template>
  <PublicLayout>
    <Head title="AI WhatsApp Automation" />
    <section class="section-wrap grid items-center gap-8 py-8 sm:py-12 lg:min-h-[calc(100vh-5rem)] lg:grid-cols-[.95fr_1.05fr] lg:gap-10 lg:py-16">
      <div>
        <ThemeToggle class="mb-8 hidden lg:inline-flex" />
        <p class="mb-4 inline-flex rounded-full bg-violet-100 px-3 py-1.5 text-xs font-black text-violet-700 dark:bg-violet-500/15 dark:text-violet-200 sm:px-4 sm:py-2 sm:text-sm">AI WhatsApp Automation</p>
        <h1 class="max-w-3xl text-[2rem] font-black leading-[1.08] tracking-tight sm:text-6xl lg:text-7xl">
          <span class="gradient-text">AI WhatsApp</span> Automation for Modern Businesses
        </h1>
        <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300 sm:mt-6 sm:text-lg sm:leading-8">Automate replies, capture leads, and grow your business with an AI-powered WhatsApp assistant built for sales, support, and campaigns.</p>
        <div class="mt-6 grid max-w-xl grid-cols-1 gap-3 text-sm font-semibold text-slate-700 dark:text-slate-200 sm:mt-8 sm:grid-cols-2">
          <span v-for="benefit in benefits" :key="benefit" class="flex items-center gap-2.5 rounded-2xl bg-white/70 px-3 py-2 shadow-sm dark:bg-white/[.06] sm:bg-transparent sm:px-0 sm:py-0 sm:shadow-none"><CheckCircle2 class="size-4 shrink-0 text-violet-600 sm:size-5" />{{ benefit }}</span>
        </div>
        <div class="mt-7 grid gap-3 sm:mt-10 sm:flex sm:flex-row">
          <PrimaryButton href="/auth/register" class="w-full sm:w-auto">Start Free Trial</PrimaryButton>
          <SecondaryButton href="/contact" class="w-full sm:w-auto">Book a Demo</SecondaryButton>
        </div>
        <p class="mt-3 text-center text-xs font-semibold text-slate-500 sm:text-left sm:text-sm">No credit card required - 14-day free trial</p>
      </div>
      <SaasMockup />
    </section>

    <section class="section-wrap pb-10 sm:pb-16">
      <div class="grid grid-cols-2 gap-2 text-center text-xs font-black text-slate-400 sm:grid-cols-5 sm:gap-5 sm:text-sm">
        <span v-for="brand in brands" :key="brand" class="rounded-2xl bg-white/60 py-3 dark:bg-white/[.04] sm:bg-transparent sm:py-4">{{ brand }}</span>
      </div>
    </section>

    <section class="section-wrap py-10 sm:py-16">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-xs font-black uppercase text-violet-600 sm:text-sm">Features</p>
        <h2 class="mt-3 text-2xl font-black leading-tight sm:text-5xl">Everything you need to automate WhatsApp</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300 sm:mt-4 sm:text-base">A complete workspace for conversations, CRM, broadcasts, training and realtime analytics.</p>
      </div>
      <div class="mt-7 grid gap-3 sm:mt-10 md:grid-cols-2 md:gap-5 lg:grid-cols-5">
        <FeatureCard v-for="feature in features" :key="feature.title" v-bind="feature" />
      </div>
    </section>

    <section class="section-wrap grid gap-4 py-10 sm:gap-6 sm:py-16 lg:grid-cols-[.9fr_1.1fr]">
      <div class="premium-card bg-white text-slate-950 dark:bg-white/[.06] dark:text-white">
        <p class="text-xs font-black uppercase text-violet-400 sm:text-sm">Automation Showcase</p>
        <h2 class="mt-3 text-2xl font-black leading-tight sm:text-4xl">Smart flows that reply, qualify and route leads in seconds.</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-300 sm:mt-4">Build business logic for every WhatsApp message with AI, CRM updates, routing and follow-ups in one clean workflow.</p>
        <div class="mt-6 space-y-3 sm:mt-8 sm:space-y-4">
          <div v-for="flow in flows" :key="flow" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 shadow-sm dark:border-white/10 dark:bg-white/10 sm:gap-4 sm:p-4">
            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-sky-100 text-sky-500 dark:bg-sky-400/15 sm:size-10">
              <Sparkles class="size-4 sm:size-5" />
            </span>
            <span class="text-sm font-bold leading-5 text-slate-700 dark:text-slate-100 sm:text-base">{{ flow }}</span>
          </div>
        </div>
      </div>
      <div class="premium-card bg-white dark:bg-white/[.06]">
        <div class="grid gap-3 sm:grid-cols-3 sm:gap-4">
          <div v-for="stat in stats" :key="stat.label" class="rounded-2xl bg-slate-100 p-4 dark:bg-white/10 sm:rounded-3xl sm:p-5">
            <p class="text-xs font-semibold text-slate-500 sm:text-sm">{{ stat.label }}</p>
            <p class="mt-1 text-2xl font-black sm:mt-2 sm:text-3xl">{{ stat.value }}</p>
          </div>
        </div>
        <div class="mt-4 rounded-[22px] bg-gradient-to-br from-violet-600 to-indigo-600 p-4 text-white sm:mt-6 sm:rounded-[26px] sm:p-5">
          <div class="flex items-center justify-between">
            <p class="font-black">Realtime Analytics</p>
            <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-bold">Live</span>
          </div>
          <svg viewBox="0 0 700 220" class="mt-4 h-36 w-full sm:mt-6 sm:h-52">
            <defs><linearGradient id="chart" x1="0" x2="0" y1="0" y2="1"><stop stop-color="#fff" stop-opacity=".45" /><stop offset="1" stop-color="#fff" stop-opacity="0" /></linearGradient></defs>
            <path d="M0 170 C80 80 120 190 210 105 S330 160 410 60 S560 130 700 35 L700 220 L0 220 Z" fill="url(#chart)" />
            <path d="M0 170 C80 80 120 190 210 105 S330 160 410 60 S560 130 700 35" fill="none" stroke="white" stroke-width="10" stroke-linecap="round" />
          </svg>
        </div>
      </div>
    </section>

    <section id="how-it-works" class="section-wrap py-10 sm:py-16">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-xs font-black uppercase text-violet-600 sm:text-sm">How it works</p>
        <h2 class="mt-3 text-2xl font-black leading-tight sm:text-5xl">Get started in 3 simple steps</h2>
      </div>
      <div class="mt-7 grid gap-3 sm:mt-10 md:grid-cols-3 md:gap-5">
        <div v-for="(step, index) in steps" :key="step.title" class="premium-card text-center">
          <span class="mx-auto grid size-10 place-items-center rounded-full bg-violet-600 text-sm font-black text-white sm:size-11">{{ index + 1 }}</span>
          <component :is="step.icon" class="mx-auto mt-5 size-10 text-violet-600 sm:mt-6 sm:size-12" />
          <h3 class="mt-4 text-lg font-black sm:mt-5 sm:text-xl">{{ step.title }}</h3>
          <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300 sm:mt-3">{{ step.text }}</p>
        </div>
      </div>
    </section>

    <section class="section-wrap py-10 sm:py-16">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-xs font-black uppercase text-violet-600 sm:text-sm">Pricing</p>
        <h2 class="mt-3 text-2xl font-black leading-tight sm:text-5xl">Simple, transparent pricing</h2>
      </div>
      <div class="mt-7 grid gap-4 sm:mt-10 lg:grid-cols-3 lg:gap-5">
        <PricingCard v-for="plan in plans" :key="plan.name" v-bind="plan" />
      </div>
    </section>

    <section class="section-wrap py-10 sm:py-16">
      <div class="grid gap-4 lg:grid-cols-3 lg:gap-5">
        <article v-for="quote in testimonials" :key="quote.name" class="premium-card">
          <p class="text-amber-400">★★★★★</p>
          <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-200 sm:mt-4 sm:text-base sm:leading-7">"{{ quote.text }}"</p>
          <p class="mt-5 font-black">{{ quote.name }}</p>
          <p class="text-sm text-slate-500">{{ quote.role }}</p>
        </article>
      </div>
    </section>

    <section class="section-wrap py-10 sm:py-16">
      <div class="rounded-[24px] bg-gradient-to-r from-violet-600 to-indigo-600 p-6 text-center text-white shadow-glow sm:rounded-[32px] sm:p-14">
        <h2 class="text-2xl font-black leading-tight sm:text-5xl">Ready to automate your WhatsApp growth?</h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-white/80 sm:mt-4 sm:text-base">Launch your AI assistant, CRM, campaigns and analytics from one premium workspace.</p>
        <div class="mt-6 flex justify-center sm:mt-8">
          <SecondaryButton href="/auth/register" class="w-full sm:w-auto">Create Free Account</SecondaryButton>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Bot, CheckCircle2, LineChart, MessageSquareReply, Send, Sparkles, Users, Workflow } from 'lucide-vue-next';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import FeatureCard from '@/Components/Marketing/FeatureCard.vue';
import PricingCard from '@/Components/Marketing/PricingCard.vue';
import SaasMockup from '@/Components/Marketing/SaasMockup.vue';
import PrimaryButton from '@/Components/Ui/PrimaryButton.vue';
import SecondaryButton from '@/Components/Ui/SecondaryButton.vue';
import ThemeToggle from '@/Components/Ui/ThemeToggle.vue';

const benefits = ['Smart AI Replies', 'Lead Generation', 'Team Inbox', 'Real-time Analytics'];
const brands = ['TechNova', 'Digicore', 'PixelLab', 'InnoSuite', 'Brandox'];
const features = [
  { title: 'AI Auto Replies', text: 'Smart responses that understand context and engage naturally.', icon: MessageSquareReply, tone: 'bg-violet-100 text-violet-600 dark:bg-violet-500/15' },
  { title: 'Lead Generation', text: 'Capture and qualify leads automatically from conversations.', icon: Users, tone: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15' },
  { title: 'Team Inbox', text: 'Collaborate with your team in a shared WhatsApp inbox.', icon: Bot, tone: 'bg-pink-100 text-pink-600 dark:bg-pink-500/15' },
  { title: 'Broadcast Campaigns', text: 'Send targeted broadcast messages to your audience.', icon: Send, tone: 'bg-sky-100 text-sky-600 dark:bg-sky-500/15' },
  { title: 'Analytics & Reports', text: 'Track performance with detailed analytics and insights.', icon: LineChart, tone: 'bg-amber-100 text-amber-600 dark:bg-amber-500/15' },
];
const flows = ['AI qualifies hot leads and creates CRM deals', 'VIP conversations route to senior agents', 'Broadcasts personalize offers by customer stage', 'Training improves replies from business documents'];
const stats = [{ label: 'Replies automated', value: '84%' }, { label: 'Leads captured', value: '12.4k' }, { label: 'Response time', value: '8s' }];
const steps = [
  { title: 'Connect WhatsApp', text: 'Connect your WhatsApp Business account in a few clicks.', icon: MessageSquareReply },
  { title: 'Train Your AI', text: 'Add business info and let AI learn how to respond.', icon: Bot },
  { title: 'Automate & Grow', text: 'AI handles conversations while your team focuses on growth.', icon: Workflow },
];
const plans = [
  { name: 'Starter', price: '19', caption: 'For small businesses', features: ['1 WhatsApp account', '1,000 messages / month', 'AI auto replies', 'Basic analytics'] },
  { name: 'Pro', price: '49', caption: 'For growing businesses', features: ['3 WhatsApp accounts', '10,000 messages / month', 'Advanced AI', 'Team inbox', 'Broadcast messages'], featured: true },
  { name: 'Agency', price: '99', caption: 'For agencies and teams', features: ['10 WhatsApp accounts', 'Unlimited messages', 'Team collaboration', 'Advanced analytics', 'Priority support'] },
];
const testimonials = [
  { name: 'Sarah Johnson', role: 'E-commerce Store Owner', text: 'ChatFlow AI transformed our customer support. Response time improved by 80%.' },
  { name: 'Michael Chen', role: 'Digital Marketing Agency', text: 'The AI replies are amazing. It feels like we have a 24/7 team working for our business.' },
  { name: 'Priya Sharma', role: 'Real Estate Consultant', text: 'Lead generation features helped us increase qualified leads by 35% in one month.' },
];
</script>
