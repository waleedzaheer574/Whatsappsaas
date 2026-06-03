<template>
  <div class="relative mx-auto aspect-[1.18] w-full max-w-xl">
    <div class="absolute inset-8 rounded-[36px] bg-violet-500/20 blur-3xl" />
    <div class="glass-panel absolute inset-x-8 top-8 rotate-[-4deg] rounded-[32px] p-4">
      <div class="flex items-center justify-between">
        <span class="text-sm font-bold">Dashboard</span>
        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-600">Live</span>
      </div>
      <div class="mt-5 grid grid-cols-2 gap-3">
        <div class="rounded-2xl bg-slate-100 p-4 dark:bg-white/10"><p class="text-xs text-slate-500">Automated</p><p class="text-2xl font-black">12,458</p></div>
        <div class="rounded-2xl bg-slate-100 p-4 dark:bg-white/10"><p class="text-xs text-slate-500">Accuracy</p><p class="text-2xl font-black">92.5%</p></div>
      </div>
      <div class="mt-5 h-28 rounded-3xl bg-gradient-to-br from-violet-100 to-indigo-100 p-4 dark:from-violet-500/20 dark:to-sky-500/10">
        <svg viewBox="0 0 360 100" class="h-full w-full"><path d="M0 80 C40 40 70 90 110 48 S185 70 220 28 S300 76 360 22" fill="none" stroke="#7C3AED" stroke-width="8" stroke-linecap="round" /></svg>
      </div>
    </div>
    <div class="absolute left-0 top-28 hidden w-16 rounded-3xl bg-slate-950 p-3 shadow-2xl sm:block">
      <div class="mb-4 grid size-9 place-items-center rounded-2xl bg-violet-600 text-white"><Bot class="size-5" /></div>
      <div v-for="i in 5" :key="i" class="mx-auto mb-4 size-3 rounded-full bg-white/25" />
    </div>
    <div class="glass-panel absolute bottom-16 right-0 max-w-xs rounded-3xl p-4 text-sm">
      <p class="font-bold">Hello! 👋</p>
      <p class="mt-1 text-slate-600 dark:text-slate-300">How can I help you today?</p>
    </div>
    <div class="absolute bottom-2 right-6 grid size-16 place-items-center rounded-full bg-whatsapp text-white shadow-glow"><MessageCircle class="size-8" /></div>
    <div class="absolute bottom-0 left-4 animate-floaty"><div class="grid size-28 place-items-center rounded-[32px] bg-white shadow-glow dark:bg-slate-900"><Bot class="size-16 text-violet-600" /></div></div>
  </div>
</template>

<script setup lang="ts">
import { Bot, MessageCircle } from 'lucide-vue-next';
</script>
