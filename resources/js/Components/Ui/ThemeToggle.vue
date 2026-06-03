<template>
  <button class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/10 dark:text-white" type="button" aria-label="Toggle theme" @click="theme.toggle()">
    <Sun v-if="!theme.dark" class="size-4" />
    <Moon v-else class="size-4" />
    <span>{{ theme.dark ? 'Dark' : 'Light' }}</span>
  </button>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { Moon, Sun } from 'lucide-vue-next';
import { useThemeStore } from '@/Stores/theme';

const theme = useThemeStore();
onMounted(() => theme.boot());
</script>
