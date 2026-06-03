<template>
  <AuthLayout>
    <Head title="Register" />
    <AuthCard
      eyebrow="Start free"
      title="Create your workspace"
      subtitle="Launch your WhatsApp AI assistant with a 14-day trial."
      action="Create Account"
      :processing="form.processing"
      @submit="submit"
    >
      <AuthInput v-model="form.name" label="Name" placeholder="Your name" :error="form.errors.name" />
      <AuthInput v-model="form.email" label="Work email" placeholder="you@company.com" type="email" :error="form.errors.email" />
      <AuthInput v-model="form.company" label="Company" placeholder="Company name" :error="form.errors.company" />
      <AuthInput v-model="form.password" label="Password" placeholder="Create a strong password" type="password" :error="form.errors.password" />
      <p class="text-center text-sm text-slate-500">Already have an account? <a href="/auth/login" class="font-black text-violet-600">Log in</a></p>
    </AuthCard>
  </AuthLayout>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthCard from '@/Components/Auth/AuthCard.vue';
import AuthInput from '@/Components/Auth/AuthInput.vue';

const form = useForm({
  name: '',
  email: '',
  company: '',
  password: '',
});

const submit = () => {
  form.post('/auth/register', {
    preserveScroll: true,
    onFinish: () => form.reset('password'),
  });
};
</script>
