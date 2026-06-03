<template>
  <article class="premium-card group transition duration-300 hover:-translate-y-1 hover:border-violet-300">
    <div :class="['mb-5 grid size-12 place-items-center rounded-2xl', tone]">
      <component :is="icon" class="size-6" />
    </div>
    <h3 class="text-lg font-black">{{ title }}</h3>
    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ text }}</p>
  </article>
</template>

<script setup lang="ts">
defineProps<{ title: string; text: string; icon: object; tone: string }>();
</script>
