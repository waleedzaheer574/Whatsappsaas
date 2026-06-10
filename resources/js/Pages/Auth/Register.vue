<template>
  <AuthLayout>
    <Head title="Register" />
    <AuthCard
      :eyebrow="isVerifying ? 'Verify email' : 'Start free'"
      :title="isVerifying ? 'Enter verification code' : 'Create your workspace'"
      :subtitle="isVerifying ? `We sent a 6-digit code to ${pendingEmail}.` : 'Launch your WhatsApp AI assistant with a 14-day trial.'"
      :action="isVerifying ? 'Verify & Create Account' : 'Send Verification Code'"
      :processing="isVerifying ? verifyForm.processing : form.processing"
      @submit="submit"
    >
      <div v-if="page.props.flash?.success" class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
        {{ page.props.flash.success }}
      </div>
      <div v-if="page.props.flash?.error" class="rounded-2xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
        {{ page.props.flash.error }}
      </div>

      <template v-if="!isVerifying">
        <AuthInput v-model="form.name" label="Name" placeholder="Your name" :error="form.errors.name" />
        <AuthInput v-model="form.email" label="Work email" placeholder="you@company.com" type="email" :error="form.errors.email" />
        <AuthInput v-model="form.company" label="Company" placeholder="Company name" :error="form.errors.company" />
        <AuthInput v-model="form.password" label="Password" placeholder="Create a strong password" type="password" :error="form.errors.password" />
      </template>

      <template v-else>
        <AuthInput v-model="verifyForm.code" label="Verification code" placeholder="6-digit code" type="text" :error="verifyForm.errors.code" />
        <button class="text-sm font-black text-violet-600" type="button" @click="resendCode">
          Send a new code
        </button>
      </template>

      <p class="text-center text-sm text-slate-500">Already have an account? <a href="/auth/login" class="font-black text-violet-600">Log in</a></p>
    </AuthCard>
  </AuthLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthCard from '@/Components/Auth/AuthCard.vue';
import AuthInput from '@/Components/Auth/AuthInput.vue';

const props = defineProps<{
  pendingRegistration?: {
    name: string;
    email: string;
    company: string;
    expires_at: string;
  } | null;
}>();

const page = usePage();

const form = useForm({
  name: props.pendingRegistration?.name ?? '',
  email: props.pendingRegistration?.email ?? '',
  company: props.pendingRegistration?.company ?? '',
  password: '',
});

const verifyForm = useForm({
  code: '',
});

const pendingEmail = computed(() => props.pendingRegistration?.email ?? form.email);
const isVerifying = computed(() => Boolean(props.pendingRegistration));

const submit = () => {
  if (isVerifying.value) {
    verifyForm.post('/auth/register/verify', {
      preserveScroll: true,
      onFinish: () => verifyForm.reset('code'),
    });

    return;
  }

  form.post('/auth/register', {
    preserveScroll: true,
    onFinish: () => form.reset('password'),
  });
};

const resendCode = () => {
  verifyForm.post('/auth/register/resend', {
    preserveScroll: true,
    onFinish: () => verifyForm.reset('code'),
  });
};
</script>
