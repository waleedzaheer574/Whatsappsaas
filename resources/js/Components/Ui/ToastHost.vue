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
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="confirmState.open" class="fixed inset-0 z-[10000] grid place-items-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm" @click.self="resolveConfirm(false)">
        <div class="w-full max-w-md overflow-hidden rounded-[28px] border border-white/20 bg-white shadow-2xl dark:border-white/10 dark:bg-[#111a2f]">
          <div class="border-b border-slate-200 p-5 dark:border-white/10">
            <div class="flex items-start gap-3">
              <div class="grid size-11 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-200">
                <AlertTriangle class="size-5" />
              </div>
              <div class="min-w-0">
                <p class="text-base font-black text-slate-950 dark:text-white">{{ confirmState.title }}</p>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ confirmState.message }}</p>
              </div>
            </div>
          </div>
          <div class="flex flex-col-reverse gap-3 bg-slate-50 p-4 dark:bg-white/5 sm:flex-row sm:justify-end">
            <button class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-white dark:border-white/10 dark:text-slate-200 dark:hover:bg-white/10" type="button" @click="resolveConfirm(false)">
              Cancel
            </button>
            <button class="rounded-2xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-violet-700/25 transition hover:brightness-110" type="button" @click="resolveConfirm(true)">
              Confirm
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, X } from 'lucide-vue-next';

type ToastType = 'success' | 'error';
type Toast = { id: number; type: ToastType; title: string; message: string };
type ConfirmResolver = (value: boolean) => void;

const page = usePage();
const toasts = ref<Toast[]>([]);
const confirmState = ref({
  open: false,
  title: 'Confirm action',
  message: '',
  resolver: null as ConfirmResolver | null,
});
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

function handleToastEvent(event: Event) {
  const detail = (event as CustomEvent<{ type?: ToastType; message?: string }>).detail ?? {};
  pushToast(detail.type === 'error' ? 'error' : 'success', detail.message ?? '');
}

function resolveConfirm(value: boolean) {
  confirmState.value.resolver?.(value);
  confirmState.value.open = false;
  confirmState.value.resolver = null;
}

function confirmAction(message: string, title = 'Confirm action') {
  return new Promise<boolean>((resolve) => {
    confirmState.value = {
      open: true,
      title,
      message,
      resolver: resolve,
    };
  });
}

function handleKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape' && confirmState.value.open) resolveConfirm(false);
}

onMounted(() => {
  window.addEventListener('chatflow:toast', handleToastEvent);
  window.addEventListener('keydown', handleKeydown);
  (window as any).chatflowConfirm = confirmAction;
  (window as any).chatflowToast = (type: ToastType, message: string) => pushToast(type, message);
});
onUnmounted(() => {
  window.removeEventListener('chatflow:toast', handleToastEvent);
  window.removeEventListener('keydown', handleKeydown);
  delete (window as any).chatflowConfirm;
  delete (window as any).chatflowToast;
});

watch(flashSuccess, (message) => pushToast('success', message), { immediate: true });
watch(flashError, (message) => pushToast('error', message), { immediate: true });
watch(validationMessage, (message) => pushToast('error', message), { immediate: true });
watch(subscriptionNotice, (message) => pushToast('error', message), { immediate: true });
</script>
