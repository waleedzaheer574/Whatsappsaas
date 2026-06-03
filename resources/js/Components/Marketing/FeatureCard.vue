<template>
  <article class="premium-card group transition duration-300 hover:-translate-y-1 hover:border-violet-300">
    <div :class="['mb-4 grid size-10 place-items-center rounded-2xl sm:mb-5 sm:size-12', tone]">
      <component :is="icon" class="size-5 sm:size-6" />
    </div>
    <h3 class="text-base font-black sm:text-lg">{{ title }}</h3>
    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300 sm:mt-3">{{ text }}</p>
  </article>
</template>

<script setup lang="ts">
defineProps<{ title: string; text: string; icon: object; tone: string }>();
</script>
