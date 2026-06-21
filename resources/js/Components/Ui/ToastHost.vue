<template>
  <!-- Centered Modal for Toasts / Messages -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="activeToast" class="fixed inset-0 z-[9999] grid place-items-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm" @click.self="dismiss(activeToast.id)">
        <div class="relative w-full max-w-sm overflow-hidden rounded-[28px] border border-white/20 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-[#111a2f] text-center">
          <!-- Top Close Button -->
          <button class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 dark:hover:text-white" type="button" @click="dismiss(activeToast.id)">
            <X class="size-5" />
          </button>

          <!-- Header Icon -->
          <div :class="[
            'mx-auto flex size-14 items-center justify-center rounded-2xl transition-transform duration-300 hover:scale-110',
            activeToast.type === 'success' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-300'
          ]">
            <CheckCircle2 v-if="activeToast.type === 'success'" class="size-7" />
            <AlertTriangle v-else class="size-7" />
          </div>

          <!-- Title & Message -->
          <h3 class="mt-4 text-lg font-black text-slate-950 dark:text-white">
            {{ activeToast.title }}
          </h3>
          <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
            {{ activeToast.message }}
          </p>

          <!-- Action Button -->
          <button :class="[
            'mt-6 w-full rounded-2xl py-3.5 text-sm font-black text-white shadow-lg transition hover:brightness-110 active:scale-95',
            activeToast.type === 'success' ? 'bg-gradient-to-r from-emerald-500 to-teal-500 shadow-emerald-700/20' : 'bg-gradient-to-r from-red-500 to-rose-600 shadow-red-700/20'
          ]" type="button" @click="dismiss(activeToast.id)">
            OK
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- Centered Confirm Dialog -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="confirmState.open" class="fixed inset-0 z-[10000] grid place-items-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm" @click.self="resolveConfirm(false)">
        <div class="w-full max-w-md overflow-hidden rounded-[28px] border border-white/20 bg-white shadow-2xl dark:border-white/10 dark:bg-[#111a2f]">
          <div class="border-b border-slate-200 p-6 dark:border-white/10">
            <div class="flex items-start gap-4">
              <div class="grid size-11 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-200">
                <AlertTriangle class="size-5" />
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-base font-black text-slate-950 dark:text-white">{{ confirmState.title }}</p>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ confirmState.message }}</p>
              </div>
            </div>
          </div>
          <div class="flex flex-col-reverse gap-3 bg-slate-50 p-4 dark:bg-white/5 sm:flex-row sm:justify-end">
            <button class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-white dark:border-white/10 dark:text-slate-200 dark:hover:bg-white/10" type="button" @click="resolveConfirm(false)">
              Cancel
            </button>
            <button class="rounded-2xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-violet-700/25 transition hover:brightness-110 active:scale-95" type="button" @click="resolveConfirm(true)">
              Confirm
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- Centered Loading Spinner -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="isProcessing" class="fixed inset-0 z-[10001] flex flex-col items-center justify-center bg-slate-950/60 backdrop-blur-sm">
        <div class="flex flex-col items-center justify-center rounded-[28px] border border-white/20 bg-white/90 p-8 shadow-2xl dark:border-white/10 dark:bg-[#111a2f]/90 max-w-xs text-center">
          <!-- Premium Rotating Spinner -->
          <div class="relative flex items-center justify-center">
            <div class="size-12 rounded-full border-4 border-violet-100 dark:border-violet-500/10"></div>
            <div class="absolute size-12 rounded-full border-4 border-t-violet-600 border-r-fuchsia-500 border-b-transparent border-l-transparent animate-spin"></div>
          </div>
          
          <h4 class="mt-5 text-sm font-black text-slate-950 dark:text-white">Processing...</h4>
          <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Please wait while we process your request.</p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
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
const isProcessing = ref(false);

let nextId = 1;
let lastSignature = '';

const activeToast = computed(() => toasts.value[0] || null);

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

  window.setTimeout(() => dismiss(id), type === 'success' ? 5000 : 7000);
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
  if (event.key === 'Escape') {
    if (confirmState.value.open) {
      resolveConfirm(false);
    } else if (activeToast.value) {
      dismiss(activeToast.value.id);
    }
  }
}

let removeStartListener: (() => void) | null = null;
let removeFinishListener: (() => void) | null = null;

onMounted(() => {
  window.addEventListener('chatflow:toast', handleToastEvent);
  window.addEventListener('keydown', handleKeydown);
  (window as any).chatflowConfirm = confirmAction;
  (window as any).chatflowToast = (type: ToastType, message: string) => pushToast(type, message);

  removeStartListener = router.on('start', () => {
    isProcessing.value = true;
  });
  removeFinishListener = router.on('finish', () => {
    isProcessing.value = false;
  });
});

onUnmounted(() => {
  window.removeEventListener('chatflow:toast', handleToastEvent);
  window.removeEventListener('keydown', handleKeydown);
  delete (window as any).chatflowConfirm;
  delete (window as any).chatflowToast;

  if (removeStartListener) removeStartListener();
  if (removeFinishListener) removeFinishListener();
});

watch(flashSuccess, (message) => pushToast('success', message), { immediate: true });
watch(flashError, (message) => pushToast('error', message), { immediate: true });
watch(validationMessage, (message) => pushToast('error', message), { immediate: true });
watch(subscriptionNotice, (message) => pushToast('error', message), { immediate: true });
</script>
