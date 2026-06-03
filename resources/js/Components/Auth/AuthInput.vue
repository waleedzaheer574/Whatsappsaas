<template>
  <label class="grid gap-2 text-sm font-bold">
    <span>{{ label }}</span>
    <input v-model="model" :type="type ?? 'text'" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 font-medium outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 dark:border-white/10 dark:bg-white/10" :placeholder="placeholder" />
    <span v-if="error" class="text-xs font-bold text-red-500">{{ error }}</span>
  </label>
</template>

<script setup lang="ts">
defineProps<{ label: string; placeholder: string; type?: string; error?: string }>();
const model = defineModel<string>({ default: '' });
</script>
