<template>
  <div class="pointer-events-none fixed inset-x-3 top-3 z-[9999] grid gap-3 sm:left-auto sm:right-5 sm:top-5 sm:w-full sm:max-w-sm">
    <TransitionGroup
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="translate-y-2 opacity-0 sm:translate-x-4 sm:translate-y-0"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="translate-y-2 opacity-0 sm:translate-x-4 sm:translate-y-0"
    >
      <article
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'pointer-events-auto overflow-hidden rounded-2xl border bg-white/95 p-4 shadow-glass backdrop-blur-2xl dark:bg-[#111a2f]/95',
          toast.type === 'success' ? 'border-emerald-200 dark:border-emerald-400/25' : 'border-red-200 dark:border-red-400/25',
        ]"
      >
        <div class="flex gap-3">
          <div
            :class="[
              'grid size-10 shrink-0 place-items-center rounded-2xl',
              toast.type === 'success' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-300',
            ]"
          >
            <CheckCircle2 v-if="toast.type === 'success'" class="size-5" />
            <AlertTriangle v-else class="size-5" />
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-black text-slate-950 dark:text-white">{{ toast.title }}</p>
            <p class="mt-1 text-sm leading-5 text-slate-600 dark:text-slate-300">{{ toast.message }}</p>
          </div>
          <button class="grid size-8 shrink-0 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-white" type="button" @click="dismiss(toast.id)">
            <X class="size-4" />
          </button>
        </div>
        <div :class="['mt-3 h-1 rounded-full', toast.type === 'success' ? 'bg-emerald-500' : 'bg-red-500']" />
      </article>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, X } from 'lucide-vue-next';

type ToastType = 'success' | 'error';
type Toast = { id: number; type: ToastType; title: string; message: string };

const page = usePage();
const toasts = ref<Toast[]>([]);
let nextId = 1;
let lastSignature = '';

const flashSuccess = computed(() => (page.props.flash as Record<string, string | null> | undefined)?.success ?? '');
const flashError = computed(() => (page.props.flash as Record<string, string | null> | undefined)?.error ?? '');
const validationMessage = computed(() => {
  const errors = page.props.errors as Record<string, string> | undefined;
  if (!errors) return '';
  return Object.values(errors).find(Boolean) ?? '';
});
const subscriptionNotice = computed(() => {
  const dashboard = page.props.dashboard as Record<string, any> | undefined;
  return dashboard?.subscriptionNotice?.text ?? '';
});

function pushToast(type: ToastType, message: string) {
  if (!message) return;
  const signature = `${type}:${message}`;
  if (signature === lastSignature) return;
  lastSignature = signature;

  const id = nextId++;
  toasts.value.push({
    id,
    type,
    title: type === 'success' ? 'Success' : 'Action needed',
    message,
  });

  window.setTimeout(() => dismiss(id), type === 'success' ? 4200 : 6200);
}

function dismiss(id: number) {
  toasts.value = toasts.value.filter((toast) => toast.id !== id);
}

watch(flashSuccess, (message) => pushToast('success', message), { immediate: true });
watch(flashError, (message) => pushToast('error', message), { immediate: true });
watch(validationMessage, (message) => pushToast('error', message), { immediate: true });
watch(subscriptionNotice, (message) => pushToast('error', message), { immediate: true });
</script>
