<template>
  <a :href="href" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white/90 px-5 py-3 text-sm font-bold text-slate-950 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/10 dark:text-white">
    <slot />
  </a>
</template>

<script setup lang="ts">
defineProps<{ href: string }>();
</script>
