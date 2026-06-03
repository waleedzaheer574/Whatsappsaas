<template>
  <article :class="['premium-card relative', featured && 'border-violet-400 shadow-glow']">
    <span v-if="featured" class="absolute right-5 top-5 rounded-full bg-violet-600 px-3 py-1 text-xs font-bold text-white">Popular</span>
    <p class="font-bold text-slate-500 dark:text-slate-400">{{ name }}</p>
    <div class="mt-4 flex items-end gap-1"><span class="text-5xl font-black">${{ price }}</span><span class="pb-2 text-sm text-slate-500">/month</span></div>
    <p class="mt-2 text-sm text-slate-500">{{ caption }}</p>
    <ul class="mt-6 space-y-3 text-sm">
      <li v-for="feature in features" :key="feature" class="flex items-center gap-3"><Check class="size-4 text-emerald-500" /><span>{{ feature }}</span></li>
    </ul>
    <PrimaryButton href="/auth/register" class="mt-7 w-full">Start Free Trial</PrimaryButton>
  </article>
</template>

<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import PrimaryButton from '@/Components/Ui/PrimaryButton.vue';

defineProps<{ name: string; price: string; caption: string; features: string[]; featured?: boolean }>();
</script>
