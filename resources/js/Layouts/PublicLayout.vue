<template>
  <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(124,58,237,.16),transparent_35%),linear-gradient(180deg,#fff,#f8fafc)] dark:bg-[radial-gradient(circle_at_top_left,rgba(124,58,237,.22),transparent_35%),linear-gradient(180deg,#070B1A,#0F172A)]">
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/70 bg-white/82 shadow-sm backdrop-blur-2xl dark:border-white/10 dark:bg-ink/82">
      <nav class="section-wrap flex h-16 items-center justify-between sm:h-20">
        <AppLogo />
        <div class="hidden items-center gap-8 text-sm font-semibold text-slate-700 dark:text-slate-200 lg:flex">
          <a v-for="item in nav" :key="item.href" :href="item.href" class="transition hover:text-violet-600">{{ item.label }}</a>
        </div>
        <div class="hidden items-center gap-3 md:flex">
          <ThemeToggle />
          <SecondaryButton href="/auth/login">Log in</SecondaryButton>
          <PrimaryButton href="/auth/register">Get Started</PrimaryButton>
        </div>
        <button class="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-white/10 sm:size-11 lg:hidden" @click="open = !open">
          <Menu class="size-5" />
        </button>
      </nav>
      <div v-if="open" class="section-wrap pb-5 lg:hidden">
        <div class="glass-panel grid gap-2 rounded-3xl p-3">
          <a v-for="item in nav" :key="item.href" :href="item.href" class="rounded-2xl px-4 py-3 text-sm font-bold hover:bg-violet-50 dark:hover:bg-white/10">{{ item.label }}</a>
          <div class="grid grid-cols-2 gap-2 pt-2">
            <SecondaryButton href="/auth/login">Log in</SecondaryButton>
            <PrimaryButton href="/auth/register">Start</PrimaryButton>
          </div>
        </div>
      </div>
    </header>
    <main class="pt-16 sm:pt-20"><slot /></main>
    <footer class="border-t border-slate-200 bg-white/70 py-10 dark:border-white/10 dark:bg-white/[.03] sm:py-12">
      <div class="section-wrap grid gap-8 sm:grid-cols-2 md:grid-cols-[1.2fr_repeat(4,1fr)] md:gap-10">
        <div class="sm:col-span-2 md:col-span-1">
          <AppLogo />
          <p class="mt-4 max-w-sm text-sm leading-6 text-slate-600 dark:text-slate-300">AI-powered WhatsApp automation for modern teams that want faster replies, cleaner CRM, and smarter growth.</p>
        </div>
        <div v-for="group in footer" :key="group.title">
          <h4 class="font-black">{{ group.title }}</h4>
          <a v-for="link in group.links" :key="link" href="#" class="mt-3 block text-sm text-slate-500 hover:text-violet-600">{{ link }}</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Menu } from 'lucide-vue-next';
import AppLogo from '@/Components/Ui/AppLogo.vue';
import PrimaryButton from '@/Components/Ui/PrimaryButton.vue';
import SecondaryButton from '@/Components/Ui/SecondaryButton.vue';
import ThemeToggle from '@/Components/Ui/ThemeToggle.vue';

const open = ref(false);
const nav = [
  { label: 'Features', href: '/features' },
  { label: 'How It Works', href: '/#how-it-works' },
  { label: 'Pricing', href: '/pricing' },
  { label: 'Resources', href: '/docs' },
  { label: 'Blog', href: '/blog' },
];
const footer = [
  { title: 'Product', links: ['Features', 'Inbox', 'Pricing', 'Changelog'] },
  { title: 'Resources', links: ['Docs', 'Help Center', 'Tutorials', 'Blog'] },
  { title: 'Company', links: ['About', 'Contact', 'Privacy', 'Terms'] },
  { title: 'Platform', links: ['API Keys', 'Integrations', 'Reverb', 'Status'] },
];
</script>
