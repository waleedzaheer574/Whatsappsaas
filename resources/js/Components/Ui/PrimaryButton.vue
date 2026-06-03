<template>
  <a :href="href" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-glow transition hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-violet-400">
    <slot />
  </a>
</template>

<script setup lang="ts">
defineProps<{ href: string }>();
</script>
