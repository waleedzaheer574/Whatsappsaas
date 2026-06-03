<template>
  <AuthLayout>
    <Head title="Login" />
    <AuthCard
      eyebrow="Welcome back"
      title="Log in to ChatFlow AI"
      subtitle="Manage conversations, campaigns and AI automations from your workspace."
      action="Log in"
      :processing="form.processing"
      @submit="submit"
    >
      <AuthInput v-model="form.email" label="Email" placeholder="you@company.com" type="email" :error="form.errors.email" />
      <AuthInput v-model="form.password" label="Password" placeholder="********" type="password" :error="form.errors.password" />
      <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2">
          <input v-model="form.remember" type="checkbox" class="rounded border-slate-300 text-violet-600" />
          Remember me
        </label>
        <a href="/auth/forgot-password" class="font-bold text-violet-600">Forgot password?</a>
      </div>
      <p class="text-center text-sm text-slate-500">New here? <a href="/auth/register" class="font-black text-violet-600">Create account</a></p>
    </AuthCard>
  </AuthLayout>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthCard from '@/Components/Auth/AuthCard.vue';
import AuthInput from '@/Components/Auth/AuthInput.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/auth/login', {
    preserveScroll: true,
    onFinish: () => form.reset('password'),
  });
};
</script>
