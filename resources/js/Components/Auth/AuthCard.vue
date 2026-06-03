<template>
  <div class="premium-card">
    <p class="text-sm font-black uppercase text-violet-600">{{ eyebrow }}</p>
    <h1 class="mt-3 text-3xl font-black">{{ title }}</h1>
    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ subtitle }}</p>
    <form class="mt-8 grid gap-4" @submit.prevent="$emit('submit')">
      <slot />
      <button class="mt-2 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-4 font-black text-white shadow-glow disabled:cursor-not-allowed disabled:opacity-60" :disabled="processing">{{ action }}</button>
    </form>
  </div>
</template>

<script setup lang="ts">
defineProps<{ eyebrow: string; title: string; subtitle: string; action: string; processing?: boolean }>();
defineEmits<{ submit: [] }>();
</script>
