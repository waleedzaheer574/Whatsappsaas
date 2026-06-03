<template>
  <AuthLayout>
    <Head title="Forgot Password" />
    <AuthCard eyebrow="Recovery" title="Reset your password" subtitle="Enter your email and we will send a secure reset link." action="Send Reset Link">
      <AuthInput label="Email" placeholder="you@company.com" type="email" />
      <p class="text-center text-sm text-slate-500"><a href="/auth/login" class="font-black text-violet-600">Back to login</a></p>
    </AuthCard>
  </AuthLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthCard from '@/Components/Auth/AuthCard.vue';
import AuthInput from '@/Components/Auth/AuthInput.vue';
</script>
