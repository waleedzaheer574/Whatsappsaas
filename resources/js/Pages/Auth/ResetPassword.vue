<template>
  <AuthLayout>
    <Head title="Reset Password" />
    <AuthCard
      eyebrow="Security"
      title="Set a new password"
      subtitle="Create a strong, secure password for your account."
      action="Reset Password"
      :processing="form.processing"
      @submit="submit"
    >
      <div v-if="page.props.flash?.error" class="rounded-2xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
        {{ page.props.flash.error }}
      </div>

      <AuthInput v-model="form.email" label="Email" placeholder="you@company.com" type="email" :error="form.errors.email" />
      <AuthInput v-model="form.password" label="New Password" placeholder="Create a strong password" type="password" :error="form.errors.password" />
      <AuthInput v-model="form.password_confirmation" label="Confirm Password" placeholder="Repeat the password" type="password" :error="form.errors.password_confirmation" />
    </AuthCard>
  </AuthLayout>
</template>

<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthCard from '@/Components/Auth/AuthCard.vue';
import AuthInput from '@/Components/Auth/AuthInput.vue';

const props = defineProps<{
  token: string;
  email?: string;
}>();

const page = usePage();

const form = useForm({
  token: props.token,
  email: props.email ?? '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post('/auth/reset-password', {
    preserveScroll: true,
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>
