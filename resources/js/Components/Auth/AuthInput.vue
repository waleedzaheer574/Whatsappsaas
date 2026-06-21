<template>
  <label class="grid gap-2 text-sm font-bold text-slate-700 dark:text-slate-200">
    <span>{{ label }}</span>
    <div class="relative flex items-center">
      <input
        v-model="model"
        :type="inputType"
        class="w-full rounded-2xl border border-slate-200 bg-white pl-4 pr-12 py-4 font-medium outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 dark:border-white/10 dark:bg-white/10 dark:text-white"
        :placeholder="placeholder"
      />
      <button
        v-if="type === 'password'"
        type="button"
        class="absolute right-4 text-slate-400 hover:text-slate-600 dark:hover:text-white focus:outline-none"
        @click="toggleVisibility"
      >
        <Eye v-if="showPassword" class="size-5" />
        <EyeOff v-else class="size-5" />
      </button>
    </div>
    <span v-if="error" class="text-xs font-bold text-red-500">{{ error }}</span>
  </label>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

const props = defineProps<{
  label: string;
  placeholder: string;
  type?: string;
  error?: string;
}>();

const model = defineModel<string>({ default: '' });

const showPassword = ref(false);

const inputType = computed(() => {
  if (props.type === 'password') {
    return showPassword.value ? 'text' : 'password';
  }
  return props.type ?? 'text';
});

function toggleVisibility() {
  showPassword.value = !showPassword.value;
}
</script>
