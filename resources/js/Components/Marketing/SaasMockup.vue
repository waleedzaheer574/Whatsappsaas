<template>
  <div class="relative mx-auto aspect-[1.05] w-full max-w-[22rem] sm:aspect-[1.18] sm:max-w-xl">
    <div class="absolute inset-6 rounded-[30px] bg-violet-500/20 blur-3xl sm:inset-8 sm:rounded-[36px]" />
    <div class="glass-panel absolute inset-x-3 top-5 rotate-[-2deg] rounded-[24px] p-3 sm:inset-x-8 sm:top-8 sm:rotate-[-4deg] sm:rounded-[32px] sm:p-4">
      <div class="flex items-center justify-between">
        <span class="text-sm font-bold">Dashboard</span>
        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-600">Live</span>
      </div>
      <div class="mt-4 grid grid-cols-2 gap-2 sm:mt-5 sm:gap-3">
        <div class="rounded-2xl bg-slate-100 p-3 dark:bg-white/10 sm:p-4"><p class="text-[11px] text-slate-500 sm:text-xs">Automated</p><p class="text-xl font-black sm:text-2xl">12,458</p></div>
        <div class="rounded-2xl bg-slate-100 p-3 dark:bg-white/10 sm:p-4"><p class="text-[11px] text-slate-500 sm:text-xs">Accuracy</p><p class="text-xl font-black sm:text-2xl">92.5%</p></div>
      </div>
      <div class="mt-4 h-24 rounded-2xl bg-gradient-to-br from-violet-100 to-indigo-100 p-3 dark:from-violet-500/20 dark:to-sky-500/10 sm:mt-5 sm:h-28 sm:rounded-3xl sm:p-4">
        <svg viewBox="0 0 360 100" class="h-full w-full"><path d="M0 80 C40 40 70 90 110 48 S185 70 220 28 S300 76 360 22" fill="none" stroke="#7C3AED" stroke-width="8" stroke-linecap="round" /></svg>
      </div>
    </div>
    <div class="absolute left-0 top-28 hidden w-16 rounded-3xl bg-slate-950 p-3 shadow-2xl sm:block">
      <div class="mb-4 grid size-9 place-items-center rounded-2xl bg-violet-600 text-white"><Bot class="size-5" /></div>
      <div v-for="i in 5" :key="i" class="mx-auto mb-4 size-3 rounded-full bg-white/25" />
    </div>
    <div class="glass-panel absolute bottom-14 right-1 max-w-[13rem] rounded-2xl p-3 text-xs sm:bottom-16 sm:right-0 sm:max-w-xs sm:rounded-3xl sm:p-4 sm:text-sm">
      <p class="font-bold">Hello! 👋</p>
      <p class="mt-1 text-slate-600 dark:text-slate-300">How can I help you today?</p>
    </div>
    <div class="absolute bottom-3 right-5 grid size-12 place-items-center rounded-full bg-whatsapp text-white shadow-glow sm:bottom-2 sm:right-6 sm:size-16"><MessageCircle class="size-6 sm:size-8" /></div>
    <div class="absolute bottom-0 left-2 animate-floaty sm:left-4"><div class="grid size-20 place-items-center rounded-[24px] bg-white shadow-glow dark:bg-slate-900 sm:size-28 sm:rounded-[32px]"><Bot class="size-11 text-violet-600 sm:size-16" /></div></div>
  </div>
</template>

<script setup lang="ts">
import { Bot, MessageCircle } from 'lucide-vue-next';
</script>
