<template>
  <div class="grid min-h-screen bg-slate-50 dark:bg-ink lg:grid-cols-[1.05fr_.95fr]">
    <section class="relative hidden overflow-hidden bg-ink p-10 text-white lg:block">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(124,58,237,.42),transparent_28%),radial-gradient(circle_at_80%_70%,rgba(56,189,248,.22),transparent_28%)]" />
      <div class="relative z-10 flex h-full flex-col justify-between">
        <AppLogo />
        <div>
          <p class="text-sm font-bold uppercase tracking-[.24em] text-violet-300">AI WhatsApp Automation</p>
          <h1 class="mt-5 max-w-xl text-5xl font-black leading-tight">Turn every conversation into a managed growth workflow.</h1>
        </div>
        <SaasMockup />
      </div>
    </section>
    <section class="flex min-h-screen items-center justify-center p-4">
      <div class="w-full max-w-md">
        <div class="mb-8 flex items-center justify-between lg:hidden">
          <AppLogo />
          <ThemeToggle />
        </div>
        <slot />
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import AppLogo from '@/Components/Ui/AppLogo.vue';
import ThemeToggle from '@/Components/Ui/ThemeToggle.vue';
import SaasMockup from '@/Components/Marketing/SaasMockup.vue';
</script>
