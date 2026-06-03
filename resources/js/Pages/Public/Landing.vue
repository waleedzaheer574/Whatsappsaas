<template>
  <PublicLayout>
    <Head title="AI WhatsApp Automation" />
    <section class="section-wrap grid min-h-[calc(100vh-5rem)] items-center gap-10 py-16 lg:grid-cols-[.95fr_1.05fr]">
      <div>
        <ThemeToggle class="mb-8" />
        <p class="mb-5 inline-flex rounded-full bg-violet-100 px-4 py-2 text-sm font-black text-violet-700 dark:bg-violet-500/15 dark:text-violet-200">AI WhatsApp Automation</p>
        <h1 class="max-w-3xl text-4xl font-black tracking-tight sm:text-6xl lg:text-7xl">
          <span class="gradient-text">AI WhatsApp</span> Automation for Modern Businesses
        </h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300">Automate replies, capture leads, and grow your business with an AI-powered WhatsApp assistant built for sales, support, and campaigns.</p>
        <div class="mt-8 grid max-w-xl grid-cols-1 gap-3 text-sm font-semibold text-slate-700 dark:text-slate-200 sm:grid-cols-2">
          <span v-for="benefit in benefits" :key="benefit" class="flex items-center gap-3"><CheckCircle2 class="size-5 text-violet-600" />{{ benefit }}</span>
        </div>
        <div class="mt-10 flex flex-col gap-3 sm:flex-row">
          <PrimaryButton href="/auth/register">Start Free Trial</PrimaryButton>
          <SecondaryButton href="/contact">Book a Demo</SecondaryButton>
        </div>
        <p class="mt-4 text-sm text-slate-500">No credit card required - 14-day free trial</p>
      </div>
      <SaasMockup />
    </section>

    <section class="section-wrap pb-16">
      <div class="grid gap-5 text-center text-sm font-black text-slate-400 sm:grid-cols-5">
        <span v-for="brand in brands" :key="brand" class="rounded-2xl py-4">{{ brand }}</span>
      </div>
    </section>

    <section class="section-wrap py-16">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-sm font-black uppercase text-violet-600">Features</p>
        <h2 class="mt-3 text-3xl font-black sm:text-5xl">Everything you need to automate WhatsApp</h2>
        <p class="mt-4 text-slate-600 dark:text-slate-300">A complete workspace for conversations, CRM, broadcasts, training and realtime analytics.</p>
      </div>
      <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-5">
        <FeatureCard v-for="feature in features" :key="feature.title" v-bind="feature" />
      </div>
    </section>

    <section class="section-wrap grid gap-6 py-16 lg:grid-cols-[.9fr_1.1fr]">
      <div class="premium-card bg-white text-slate-950 dark:bg-white/[.06] dark:text-white">
        <p class="text-sm font-black uppercase text-violet-400">Automation Showcase</p>
        <h2 class="mt-3 text-3xl font-black leading-tight sm:text-4xl">Smart flows that reply, qualify and route leads in seconds.</h2>
        <p class="mt-4 text-sm leading-6 text-slate-500 dark:text-slate-300">Build business logic for every WhatsApp message with AI, CRM updates, routing and follow-ups in one clean workflow.</p>
        <div class="mt-8 space-y-4">
          <div v-for="flow in flows" :key="flow" class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-white/10 dark:bg-white/10">
            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-sky-100 text-sky-500 dark:bg-sky-400/15">
              <Sparkles class="size-5" />
            </span>
            <span class="font-bold text-slate-700 dark:text-slate-100">{{ flow }}</span>
          </div>
        </div>
      </div>
      <div class="premium-card bg-white dark:bg-white/[.06]">
        <div class="grid gap-4 sm:grid-cols-3">
          <div v-for="stat in stats" :key="stat.label" class="rounded-3xl bg-slate-100 p-5 dark:bg-white/10">
            <p class="text-sm text-slate-500">{{ stat.label }}</p>
            <p class="mt-2 text-3xl font-black">{{ stat.value }}</p>
          </div>
        </div>
        <div class="mt-6 rounded-[26px] bg-gradient-to-br from-violet-600 to-indigo-600 p-5 text-white">
          <div class="flex items-center justify-between">
            <p class="font-black">Realtime Analytics</p>
            <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-bold">Live</span>
          </div>
          <svg viewBox="0 0 700 220" class="mt-6 h-52 w-full">
            <defs><linearGradient id="chart" x1="0" x2="0" y1="0" y2="1"><stop stop-color="#fff" stop-opacity=".45" /><stop offset="1" stop-color="#fff" stop-opacity="0" /></linearGradient></defs>
            <path d="M0 170 C80 80 120 190 210 105 S330 160 410 60 S560 130 700 35 L700 220 L0 220 Z" fill="url(#chart)" />
            <path d="M0 170 C80 80 120 190 210 105 S330 160 410 60 S560 130 700 35" fill="none" stroke="white" stroke-width="10" stroke-linecap="round" />
          </svg>
        </div>
      </div>
    </section>

    <section id="how-it-works" class="section-wrap py-16">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-sm font-black uppercase text-violet-600">How it works</p>
        <h2 class="mt-3 text-3xl font-black sm:text-5xl">Get started in 3 simple steps</h2>
      </div>
      <div class="mt-10 grid gap-5 md:grid-cols-3">
        <div v-for="(step, index) in steps" :key="step.title" class="premium-card text-center">
          <span class="mx-auto grid size-11 place-items-center rounded-full bg-violet-600 font-black text-white">{{ index + 1 }}</span>
          <component :is="step.icon" class="mx-auto mt-6 size-12 text-violet-600" />
          <h3 class="mt-5 text-xl font-black">{{ step.title }}</h3>
          <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ step.text }}</p>
        </div>
      </div>
    </section>

    <section class="section-wrap py-16">
      <div class="mx-auto max-w-3xl text-center">
        <p class="text-sm font-black uppercase text-violet-600">Pricing</p>
        <h2 class="mt-3 text-3xl font-black sm:text-5xl">Simple, transparent pricing</h2>
      </div>
      <div class="mt-10 grid gap-5 lg:grid-cols-3">
        <PricingCard v-for="plan in plans" :key="plan.name" v-bind="plan" />
      </div>
    </section>

    <section class="section-wrap py-16">
      <div class="grid gap-5 lg:grid-cols-3">
        <article v-for="quote in testimonials" :key="quote.name" class="premium-card">
          <p class="text-amber-400">★★★★★</p>
          <p class="mt-4 leading-7 text-slate-700 dark:text-slate-200">"{{ quote.text }}"</p>
          <p class="mt-5 font-black">{{ quote.name }}</p>
          <p class="text-sm text-slate-500">{{ quote.role }}</p>
        </article>
      </div>
    </section>

    <section class="section-wrap py-16">
      <div class="rounded-[32px] bg-gradient-to-r from-violet-600 to-indigo-600 p-8 text-center text-white shadow-glow sm:p-14">
        <h2 class="text-3xl font-black sm:text-5xl">Ready to automate your WhatsApp growth?</h2>
        <p class="mx-auto mt-4 max-w-2xl text-white/80">Launch your AI assistant, CRM, campaigns and analytics from one premium workspace.</p>
        <div class="mt-8 flex justify-center">
          <SecondaryButton href="/auth/register">Create Free Account</SecondaryButton>
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
