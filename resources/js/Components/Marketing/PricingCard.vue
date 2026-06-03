<template>
  <article :class="['premium-card relative', featured && 'border-violet-400 shadow-glow']">
    <span v-if="featured" class="absolute right-5 top-5 rounded-full bg-violet-600 px-3 py-1 text-xs font-bold text-white">Popular</span>
    <p class="font-bold text-slate-500 dark:text-slate-400">{{ name }}</p>
    <div class="mt-3 flex items-end gap-1 sm:mt-4"><span class="text-4xl font-black sm:text-5xl">${{ price }}</span><span class="pb-1.5 text-sm text-slate-500 sm:pb-2">/month</span></div>
    <p class="mt-2 text-sm text-slate-500">{{ caption }}</p>
    <ul class="mt-5 space-y-2.5 text-sm sm:mt-6 sm:space-y-3">
      <li v-for="feature in features" :key="feature" class="flex items-center gap-3"><Check class="size-4 text-emerald-500" /><span>{{ feature }}</span></li>
    </ul>
    <PrimaryButton href="/auth/register" class="mt-6 w-full sm:mt-7">Start Free Trial</PrimaryButton>
  </article>
</template>

<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import PrimaryButton from '@/Components/Ui/PrimaryButton.vue';

defineProps<{ name: string; price: string; caption: string; features: string[]; featured?: boolean }>();
</script>
